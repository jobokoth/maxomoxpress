@extends('layouts.dashui')

@section('content')
<div class="row mt-4 g-3">
    <div class="col-12">
        @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    </div>

    <div class="col-12"><h4 class="mb-0">Communication Center</h4><p class="text-muted mb-0">Parent portal access, notifications, and event reminders.</p></div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Grant Parent Portal Access</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tenant.communication.parents.access', ['school_slug' => $school->slug]) }}">@csrf
                    <div class="mb-2"><label class="form-label">Guardian</label><select name="guardian_id" class="form-select form-select-sm" required>@foreach($guardians as $guardian)<option value="{{ $guardian->id }}">{{ $guardian->full_name }} ({{ $guardian->students->count() }} student(s))</option>@endforeach</select></div>
                    <div class="mb-2"><input type="email" name="email" class="form-control form-control-sm" placeholder="Portal login email" required></div>
                    <div class="mb-2"><input name="phone" class="form-control form-control-sm" placeholder="Phone"></div>
                    <div class="mb-2"><input type="password" name="password" class="form-control form-control-sm" placeholder="Password (optional)"></div>
                    <button class="btn btn-primary btn-sm" type="submit">Grant Access</button>
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Parent Portal Accounts</h6></div>
            <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Guardian</th><th>Portal</th></tr></thead><tbody>
                @forelse($guardians as $guardian)
                    <tr>
                        <td>{{ $guardian->full_name }}</td>
                        <td>
                            @if($guardian->user_id)
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.communication.parents.portal', ['school_slug' => $school->slug, 'guardian' => $guardian->id]) }}">Preview</a>
                            @else
                                <span class="text-muted small">Not enabled</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="text-muted text-center py-3">No guardians available.</td></tr>
                @endforelse
            </tbody></table></div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Create Event</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tenant.communication.events.store', ['school_slug' => $school->slug]) }}">@csrf
                    <div class="mb-2"><input name="title" class="form-control form-control-sm" placeholder="Event title" required></div>
                    <div class="mb-2"><select name="event_type" class="form-select form-select-sm" required>@foreach(['academic','sports','cultural','holiday','meeting','exam','other'] as $type)<option value="{{ $type }}">{{ strtoupper($type) }}</option>@endforeach</select></div>
                    <div class="mb-2"><label class="form-label">Start</label><input type="datetime-local" name="start_at" class="form-control form-control-sm" required></div>
                    <div class="mb-2"><label class="form-label">End</label><input type="datetime-local" name="end_at" class="form-control form-control-sm"></div>
                    <div class="mb-2"><input name="location" class="form-control form-control-sm" placeholder="Location"></div>
                    <div class="mb-2"><select name="audience" class="form-select form-select-sm" required><option value="all">All</option><option value="parents">Parents</option><option value="students">Students</option><option value="staff">Staff</option></select></div>
                    <div class="mb-2"><textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Description"></textarea></div>
                    <button class="btn btn-primary btn-sm" type="submit">Save Event</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Send Notification</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tenant.communication.notifications.send', ['school_slug' => $school->slug]) }}">@csrf
                    <div class="row g-2 mb-2"><div class="col"><input name="title" class="form-control form-control-sm" placeholder="Title" required></div><div class="col"><select name="audience" class="form-select form-select-sm" required><option value="parents">Parents</option><option value="students">Students</option><option value="staff">Staff</option><option value="custom">Custom</option></select></div></div>
                    <div class="row g-2 mb-2">
                        <div class="col">
                            <label class="form-label mb-1">Channels</label>
                            <div class="d-flex gap-3">
                                <label><input type="checkbox" name="channels[]" value="email" checked> Email</label>
                                <label><input type="checkbox" name="channels[]" value="sms"> SMS</label>
                                <label><input type="checkbox" name="channels[]" value="whatsapp"> WhatsApp</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2"><label class="form-label">Custom Guardians (for audience=custom)</label><select name="guardian_ids[]" class="form-select form-select-sm" multiple>@foreach($guardians as $guardian)<option value="{{ $guardian->id }}">{{ $guardian->full_name }}</option>@endforeach</select></div>
                    <div class="mb-2"><label class="form-label">Custom contacts (one email/phone per line)</label><textarea name="custom_contacts" class="form-control form-control-sm" rows="2" placeholder="parent@example.com\n+2547xxxxxxx"></textarea></div>
                    <div class="mb-2"><textarea name="message" class="form-control form-control-sm" rows="3" placeholder="Message" required></textarea></div>
                    <button class="btn btn-success btn-sm" type="submit">Send Notification</button>
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Events & Reminders</h6></div>
            <div class="card-body py-2"><small class="text-muted">If the reminder time is in the future, it is queued and auto-dispatched by scheduler.</small></div>
            <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Event</th><th>When</th><th>Audience</th><th>Reminder</th></tr></thead><tbody>
                @forelse($events as $event)
                    <tr>
                        <td>{{ $event->title }}<br><small class="text-muted">{{ strtoupper($event->event_type) }}</small></td>
                        <td>{{ $event->start_at?->format('d M Y H:i') }}</td>
                        <td>{{ strtoupper($event->audience) }}</td>
                        <td>
                            <form method="POST" action="{{ route('tenant.communication.events.reminders.send', ['school_slug' => $school->slug, 'event' => $event->id]) }}" class="d-flex gap-1">@csrf
                                <select name="channel" class="form-select form-select-sm"><option value="email">Email</option><option value="sms">SMS</option><option value="whatsapp">WhatsApp</option></select>
                                <input type="number" name="offset_minutes" class="form-control form-control-sm" value="60" min="0" max="10080" title="Minutes before start">
                                <button class="btn btn-sm btn-outline-primary" type="submit">Schedule/Send</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted text-center py-3">No events yet.</td></tr>
                @endforelse
            </tbody></table></div>
            <div class="card-footer">{{ $events->appends(request()->query())->links() }}</div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Notification Logs</h6></div>
            <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Time</th><th>Channel</th><th>Audience</th><th>Recipient</th><th>Status</th></tr></thead><tbody>
                @forelse($notifications as $note)
                    <tr>
                        <td>{{ $note->created_at?->format('d M Y H:i') }}</td>
                        <td>{{ strtoupper($note->channel) }}</td>
                        <td>{{ strtoupper($note->audience) }}</td>
                        <td>{{ $note->recipient_name }}<br><small class="text-muted">{{ $note->recipient_contact }}</small></td>
                        <td>{{ strtoupper($note->status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center py-3">No notifications yet.</td></tr>
                @endforelse
            </tbody></table></div>
            <div class="card-footer">{{ $notifications->appends(request()->query())->links() }}</div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">Reminder Logs</h6></div>
            <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Event</th><th>Channel</th><th>Schedule</th><th>Status</th></tr></thead><tbody>
                @forelse($reminders as $reminder)
                    <tr>
                        <td>{{ $reminder->event?->title }}</td>
                        <td>{{ strtoupper($reminder->channel) }}</td>
                        <td>{{ $reminder->scheduled_at?->format('d M Y H:i') }}</td>
                        <td>{{ strtoupper($reminder->status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted text-center py-3">No reminders yet.</td></tr>
                @endforelse
            </tbody></table></div>
            <div class="card-footer">{{ $reminders->appends(request()->query())->links() }}</div>
        </div>
    </div>
</div>
@endsection
