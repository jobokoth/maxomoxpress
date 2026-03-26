@extends('layouts.dashui')

@section('content')
<div class="row mt-4">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header"><h4 class="mb-0">Report Card Filters</h4></div>
            <div class="card-body">
                <form method="GET" class="row g-2">
                    <div class="col-md-3"><label class="form-label">Exam</label><select name="exam_id" class="form-select"><option value="">All Exams</option>@foreach($exams as $exam)<option value="{{ $exam->id }}" @selected((int)($filters['examId'] ?? 0) === $exam->id)>{{ $exam->name }}</option>@endforeach</select></div>
                    <div class="col-md-3"><label class="form-label">Course</label><select name="course_id" class="form-select"><option value="">All Courses</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected((int)($filters['courseId'] ?? 0) === $course->id)>{{ $course->name }}</option>@endforeach</select></div>
                    <div class="col-md-3"><label class="form-label">Batch</label><select name="batch_id" class="form-select"><option value="">All Batches</option>@foreach($batches as $batch)<option value="{{ $batch->id }}" @selected((int)($filters['batchId'] ?? 0) === $batch->id)>{{ $batch->name }}</option>@endforeach</select></div>
                    <div class="col-md-3"><label class="form-label">Student (Transcript)</label><select name="student_id" class="form-select"><option value="">All Students</option>@foreach($students as $student)<option value="{{ $student->id }}" @selected((int)($filters['studentId'] ?? 0) === $student->id)>{{ $student->full_name }}</option>@endforeach</select></div>
                    <div class="col-6 d-grid"><button class="btn btn-outline-secondary" type="submit">Generate</button></div>
                    <div class="col-6 d-grid"><button class="btn btn-outline-primary" type="button" onclick="window.print()">Print</button></div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h4 class="mb-0">Report Cards / Transcripts</h4></div>
            <div class="card-body">
                @forelse($reportRows as $row)
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h5 class="mb-0">{{ $row['student']->full_name }}</h5>
                                <small class="text-muted">Admission: {{ $row['student']->admission_number }}</small>
                            </div>
                            <div class="text-end">
                                <div><strong>Obtained:</strong> {{ number_format($row['obtained_marks'], 2) }}</div>
                                <div><strong>Possible:</strong> {{ number_format($row['possible_marks'], 2) }}</div>
                                <div><strong>Percentage:</strong> {{ number_format($row['percentage'], 2) }}%</div>
                                <div><strong>Grade:</strong> {{ $row['grade'] }}</div>
                                <div><strong>GPA:</strong> {{ number_format($row['gpa'], 2) }}</div>
                                <div><strong>Pass/Fail:</strong> {{ $row['pass_count'] }}/{{ $row['fail_count'] }}</div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead><tr><th>Exam</th><th>Date</th><th>Subject</th><th>Marks</th><th>Grade</th><th>Remarks</th></tr></thead>
                                <tbody>
                                    @foreach($row['transcript'] as $record)
                                        <tr>
                                            <td>{{ $record->exam?->name }}</td>
                                            <td>{{ $record->schedule?->exam_date?->format('d M Y') ?: '-' }}</td>
                                            <td>{{ $record->subject?->name }}</td>
                                            <td>{{ number_format((float)$record->marks_obtained, 2) }}</td>
                                            <td>{{ $record->grade_letter ?: '-' }}</td>
                                            <td>{{ $record->remarks ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No marks found for selected filters.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
