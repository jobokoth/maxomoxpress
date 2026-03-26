@extends('layouts.dashui')

@section('content')
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="mb-2">{{ $title }}</h3>
                    <p class="text-muted mb-0">{{ $description }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
