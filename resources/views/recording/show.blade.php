@extends('layouts.app')
@section('title', 'Show Recording')
@section('section')
    <div class="card shadow-none">
        <div class="card-body d-flex align-items-center justify-content-between">
            <h3 class="card-title fw-semibold">{{ $recording->title }} </h3>
            @auth
                <a class="btn btn-primary" href="{{ route('recordings.index') }}">Back</a>
            @endauth
        </div>

        <div class="card-body">
            <video class="w-100" controls>
                <source src="{{ $recording->file_url }}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        {{-- <div class="card-footer">
            <p class="text"> {{ $recording->description }} </p>
            <b>Total visit: ({{ $visits }})</b>
        </div> --}}
    </div>

@endsection
@section('js')

@endsection
