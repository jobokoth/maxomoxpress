<?php

namespace App\Http\Controllers;

use App\Models\CommunicationNotification;
use App\Models\EventReminder;
use App\Models\Guardian;
use App\Models\SchoolEvent;
use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CommunicationController extends Controller
{
    public function index(): View
    {
        $school = app('current_school');

        return view('communication.index', [
            'school' => $school,
            'guardians' => Guardian::query()->with('students')->orderBy('full_name')->get(),
            'events' => SchoolEvent::query()->latest('start_at')->paginate(10, ['*'], 'events_page'),
            'notifications' => CommunicationNotification::query()->latest()->paginate(15, ['*'], 'notifications_page'),
            'reminders' => EventReminder::query()->with('event')->latest()->paginate(15, ['*'], 'reminders_page'),
            'students' => Student::query()->orderBy('last_name')->orderBy('first_name')->get(),
        ]);
    }

    public function parentPortal(Guardian $guardian): View
    {
        $guardian->load(['students.course', 'students.batch']);
        $studentIds = $guardian->students->pluck('id');

        return view('communication.parent-portal', [
            'school' => app('current_school'),
            'guardian' => $guardian,
            'children' => $guardian->students,
            'announcements' => app('current_school')
                ? \App\Models\Announcement::query()->latest('published_at')->take(10)->get()
                : collect(),
            'events' => SchoolEvent::query()->where('start_at', '>=', now()->startOfDay())->orderBy('start_at')->take(10)->get(),
            'fees' => \App\Models\FeeAssignment::query()->with(['student', 'structure'])->whereIn('student_id', $studentIds)->latest()->take(20)->get(),
            'marks' => \App\Models\StudentMark::query()->with(['student', 'exam', 'subject'])->whereIn('student_id', $studentIds)->latest()->take(20)->get(),
        ]);
    }

    public function grantParentPortalAccess(Request $request): RedirectResponse
    {
        $school = app('current_school');

        $validated = $request->validate([
            'guardian_id' => ['required', Rule::exists('guardians', 'id')->where('school_id', $school->id)],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $guardian = Guardian::query()->findOrFail($validated['guardian_id']);

        $user = User::query()->firstOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $guardian->full_name,
                'password' => Hash::make($validated['password'] ?? Str::password(12)),
                'phone' => $validated['phone'] ?? $guardian->phone,
            ]
        );

        if (! $user->hasRole('parent')) {
            $user->assignRole('parent');
        }

        $school->users()->syncWithoutDetaching([
            $user->id => [
                'role_in_school' => 'parent',
                'is_primary_school' => false,
                'joined_at' => now(),
            ],
        ]);

        $guardian->update([
            'user_id' => $user->id,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? $guardian->phone,
        ]);

        return back()->with('status', 'Parent portal access granted.');
    }

    public function sendNotification(Request $request): RedirectResponse
    {
        $school = app('current_school');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'audience' => ['required', Rule::in(['parents', 'students', 'staff', 'custom'])],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => [Rule::in(['email', 'sms', 'whatsapp'])],
            'guardian_ids' => ['nullable', 'array'],
            'guardian_ids.*' => [Rule::exists('guardians', 'id')->where('school_id', $school->id)],
            'custom_contacts' => ['nullable', 'string'],
        ]);

        $recipients = $this->buildRecipients(
            $validated['audience'],
            $validated['guardian_ids'] ?? [],
            $validated['custom_contacts'] ?? null,
            $school->id
        );

        if ($recipients->isEmpty()) {
            return back()->withErrors([
                'message' => 'No valid recipients found for the selected audience/channels.',
            ])->withInput();
        }

        $this->sendToRecipients(
            $validated['title'],
            $validated['message'],
            $validated['audience'],
            $validated['channels'],
            $recipients,
            $request->user()?->id,
            $school->id
        );

        return back()->with('status', 'Notifications queued/sent successfully.');
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_type' => ['required', Rule::in(['academic', 'sports', 'cultural', 'holiday', 'meeting', 'exam', 'other'])],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'audience' => ['required', Rule::in(['all', 'parents', 'students', 'staff'])],
        ]);

        SchoolEvent::query()->create($validated + ['created_by_user_id' => $request->user()?->id]);

        return back()->with('status', 'Calendar event created.');
    }

    public function sendEventReminder(Request $request, SchoolEvent $event): RedirectResponse
    {
        $validated = $request->validate([
            'channel' => ['required', Rule::in(['email', 'sms', 'whatsapp'])],
            'offset_minutes' => ['required', 'integer', 'min:0', 'max:10080'],
        ]);

        $scheduledAt = Carbon::parse($event->start_at)->subMinutes((int) $validated['offset_minutes']);

        $reminder = EventReminder::query()->create([
            'school_id' => app('current_school')->id,
            'school_event_id' => $event->id,
            'channel' => $validated['channel'],
            'audience' => $event->audience === 'all' ? 'all' : $event->audience,
            'offset_minutes' => (int) $validated['offset_minutes'],
            'scheduled_at' => $scheduledAt,
            'created_by_user_id' => $request->user()?->id,
            'status' => 'scheduled',
        ]);

        if ($scheduledAt->lte(now())) {
            $this->deliverReminder($reminder, $request->user()?->id);

            return back()->with('status', 'Event reminder sent.');
        }

        return back()->with('status', 'Event reminder scheduled for ' . $scheduledAt->format('d M Y H:i') . '.');
    }

    public function dispatchDueReminders(?int $schoolId = null): int
    {
        $dueReminders = EventReminder::query()
            ->with('event')
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now());

        if ($schoolId) {
            $dueReminders->where('school_id', $schoolId);
        }

        $dueReminders = $dueReminders->get();

        $sent = 0;
        foreach ($dueReminders as $reminder) {
            if ($this->deliverReminder($reminder, $reminder->created_by_user_id)) {
                $sent++;
            }
        }

        return $sent;
    }

    private function deliverReminder(EventReminder $reminder, ?int $createdByUserId): bool
    {
        $event = $reminder->event;
        if (! $event) {
            $reminder->update([
                'status' => 'failed',
                'error_message' => 'Event not found.',
            ]);

            return false;
        }

        $title = 'Event Reminder: ' . $event->title;
        $message = "Reminder: {$event->title} starts at {$event->start_at->format('d M Y H:i')}";
        $audiences = $event->audience === 'all' ? ['parents', 'students', 'staff'] : [$event->audience];

        foreach ($audiences as $audience) {
            $recipients = $this->buildRecipients($audience, [], null, $event->school_id);
            if ($recipients->isEmpty()) {
                continue;
            }

            $this->sendToRecipients(
                $title,
                $message,
                $audience,
                [$reminder->channel],
                $recipients,
                $createdByUserId,
                $event->school_id
            );
        }

        $reminder->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return true;
    }

    private function dispatchNotification(CommunicationNotification $notification): void
    {
        try {
            if ($notification->channel === 'email') {
                Mail::raw($notification->message, function ($mail) use ($notification): void {
                    $mail->to($notification->recipient_contact)
                        ->subject($notification->title);
                });
            }

            $notification->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $notification->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    private function buildRecipients(string $audience, array $guardianIds, ?string $customContacts, int $schoolId): Collection
    {
        $recipients = collect();

        if ($audience === 'parents') {
            $recipients = Guardian::query()->get()->map(function (Guardian $guardian) {
                return [
                    'name' => $guardian->full_name,
                    'email' => $guardian->email,
                    'phone' => $guardian->phone,
                    'user_id' => $guardian->user_id,
                    'student_id' => $guardian->students()->value('students.id'),
                ];
            });
        } elseif ($audience === 'students') {
            $recipients = Student::query()->get()->map(fn (Student $student) => [
                'name' => $student->full_name,
                'email' => $student->email,
                'phone' => $student->phone,
                'user_id' => null,
                'student_id' => $student->id,
            ]);
        } elseif ($audience === 'staff') {
            $recipients = User::query()
                ->whereHas('schools', fn ($query) => $query->where('schools.id', $schoolId))
                ->get()
                ->map(fn (User $user) => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'user_id' => $user->id,
                    'student_id' => null,
                ]);
        } else {
            if (! empty($guardianIds)) {
                $recipients = Guardian::query()->whereIn('id', $guardianIds)->get()->map(function (Guardian $guardian) {
                    return [
                        'name' => $guardian->full_name,
                        'email' => $guardian->email,
                        'phone' => $guardian->phone,
                        'user_id' => $guardian->user_id,
                        'student_id' => $guardian->students()->value('students.id'),
                    ];
                });
            }

            if (! empty($customContacts)) {
                $customRows = collect(explode("\n", $customContacts))
                    ->map(fn (string $line) => trim($line))
                    ->filter();

                $recipients = $recipients->merge($customRows->map(function (string $line): array {
                    return [
                        'name' => 'Custom',
                        'email' => filter_var($line, FILTER_VALIDATE_EMAIL) ? $line : null,
                        'phone' => ! filter_var($line, FILTER_VALIDATE_EMAIL) ? $line : null,
                        'user_id' => null,
                        'student_id' => null,
                    ];
                }));
            }
        }

        return $recipients
            ->filter(function (array $recipient): bool {
                return filled($recipient['email'] ?? null) || filled($recipient['phone'] ?? null);
            })
            ->unique(function (array $recipient): string {
                return ($recipient['email'] ?? '-') . '|' . ($recipient['phone'] ?? '-');
            })
            ->values();
    }

    private function sendToRecipients(string $title, string $message, string $audience, array $channels, $recipients, ?int $createdByUserId, int $schoolId): void
    {
        foreach ($channels as $channel) {
            foreach ($recipients as $recipient) {
                $contact = $channel === 'email' ? ($recipient['email'] ?? null) : ($recipient['phone'] ?? null);
                if (blank($contact)) {
                    continue;
                }

                $notification = CommunicationNotification::query()->create([
                    'school_id' => $schoolId,
                    'title' => $title,
                    'message' => $message,
                    'channel' => $channel,
                    'audience' => $audience,
                    'recipient_name' => $recipient['name'] ?? null,
                    'recipient_contact' => $contact,
                    'recipient_user_id' => $recipient['user_id'] ?? null,
                    'related_student_id' => $recipient['student_id'] ?? null,
                    'created_by_user_id' => $createdByUserId,
                    'status' => 'pending',
                ]);

                $this->dispatchNotification($notification);
            }
        }
    }
}
