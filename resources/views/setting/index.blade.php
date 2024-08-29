@extends('layouts.app')
@section('title', 'Dashbaord')

@section('section')
@if(is_role() == 'admin' )
<div class="card">
    <div class="card-body">
        <h5 class="card-title fw-semibold mb-4">Settings</h5>
        <div class="card shadow-none">
            <x-form :fields="$form_fields" :action="route('settings.store')" :method="'POST'" :enctype="true" />
        </div>
    </div>
</div>
@else
<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card card-xxl-stretch-50 mb-5 mb-xl-10">
            <div class="card-body pt-5">
                <img src="{{ asset('crm.jpg') }}" alt="Logo" class="img-fluid">
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card card-xxl-stretch-50 mb-5 mb-xl-10">
            <div class="card-body pt-5">
                @php
                $href = '';
                $disabled = 'disabled';
                $description = 'Already Connected to CRM!';
                if(!is_connected()){
                $disabled = '';
                $href='https://marketplace.gohighlevel.com/oauth/chooselocation?response_type=code&redirect_uri=' . route('authorization.gohighlevel.callback') . '&client_id=' . supersetting('crm_client_id') . '&scope=calendars.readonly calendars/events.write calendars/groups.readonly calendars/groups.write campaigns.readonly conversations.readonly conversations.write conversations/message.readonly conversations/message.write contacts.readonly contacts.write forms.readonly forms.write links.write links.readonly locations.readonly locations/customValues.readonly locations/customValues.write locations/customFields.readonly locations/customFields.write locations/tasks.readonly locations/tasks.write locations/tags.readonly locations/tags.write locations/templates.readonly medias.readonly medias.write opportunities.readonly opportunities.write surveys.readonly users.readonly users.write workflows.readonly calendars/events.readonly calendars.write businesses.write businesses.readonly';
                $description = 'Please Connect CRM';
                }
                @endphp
                <a href="{{ $href }}" class="form-control btn btn-primary {{ $disabled }}"> {{ $description }} </a>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
@section('js')
<script src="{{ asset('assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/libs/simplebar/dist/simplebar.js') }}"></script>
@endsection