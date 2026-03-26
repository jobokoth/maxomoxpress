@extends('layouts.dashui')

@section('content')
    <div class="row mt-4">
        <div class="col-12">
            <h3 class="mb-3">New Student Admission</h3>
            <form method="POST" action="{{ route('tenant.students.store', ['school_slug' => $school->slug]) }}" enctype="multipart/form-data">
                @csrf
                @include('students._form')
            </form>
        </div>
    </div>
@endsection
