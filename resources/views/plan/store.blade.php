@extends('layouts.app')
@section('title', 'Create Plan')

@section('section')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Create {{ $title }}</h5>
            <div class="card shadow-none">
                <x-form :fields="$formFields" :action="route('plans.store')" :method="'POST'" :enctype="true" />
                <span class ="text-danger">Set 0 limit for unlimited videos</span>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script src="{{ asset('assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/dist/simplebar.js') }}"></script>
@endsection
