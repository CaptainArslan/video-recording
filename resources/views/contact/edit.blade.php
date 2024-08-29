@extends('layouts.app')
@section('title', 'Create User')

@section('section')
<div class="card">
    <div class="card-body">
        <h5 class="card-title fw-semibold mb-4">Create {{ $title }}</h5>
        <div class="card shadow-none">
            <x-form :fields="$formFields" :action="route('contacts.update', $contact->id)" :method="'POST'" :enctype="false" />
        </div>
    </div>
</div>
@endsection
@section('js')
<script src="{{ asset('assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/libs/simplebar/dist/simplebar.js') }}"></script>
@endsection