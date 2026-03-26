@extends('layouts.dashui')

@section('content')
    <div class="row mt-4">
        <div class="col-12">
            <h3 class="mb-3">Edit Student Profile</h3>
            <form method="POST" action="{{ route('tenant.students.update', ['school_slug' => $school->slug, 'student' => $student->id]) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('students._form')
            </form>
        </div>
    </div>
@endsection
