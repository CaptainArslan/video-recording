@extends('layouts.app')
@section('title', 'Profile Edit')
@section('section')
<div class="card">
    <div class="card-body">
        <h5 class="card-title fw-semibold mb-4">Profile</h5>
        <div class="card shadow-none">
            <x-form :fields="$formFields" :action="route('profile.update')" :method="'POST'" :enctype="true" />
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <h5 class="card-title fw-semibold mb-4">Update Password</h5>
        <div class="card shadow-none">
            <x-form :fields="$passwordFields" :action="route('password.update')" :method="'POST'" :enctype="true" />
        </div>
    </div>
</div>
@endsection
@section('js')
<script src="{{ asset('assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/libs/simplebar/dist/simplebar.js') }}"></script>
@endsection