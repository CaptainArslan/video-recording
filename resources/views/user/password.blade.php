@extends('layouts.app')
@section('title', 'Profile Edit')
@section('section')
<div class="card">
    <div class="card-body">
        <h5 class="card-title fw-semibold mb-4">Update Password</h5>
        <x-auth-session-status class="mb-4" :status="session('status')" />
        <x-auth-validation-errors class="mb-4 text-danger" :errors="$errors" />
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