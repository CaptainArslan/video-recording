@extends('layouts.app')
@section('title', 'Edit User')

@section('section')
<div class="card">
    <div class="card-body">
        <h5 class="card-title fw-semibold mb-4">Edit User</h5>
        <div class="card shadow-none">
            <x-form :fields="$formFields" :action="route('users.update', $user->id)" :method="'POST'" :enctype="true" />
        </div>
    </div>
</div>
@endsection
@section('js')
<script src="{{ asset('assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/libs/simplebar/dist/simplebar.js') }}"></script>
@endsection