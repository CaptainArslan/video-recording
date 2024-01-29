@extends('layouts.app')
@section('title', 'Dashbaord')

@section('section')
@if (env('APP_ENV') == 'local')
<script>
    // Embed values from the environment
    window.env = {
        CRM_LOCATION_ID: "{{ env('CRM_LOCATION_ID') }}",
        CRM_TOKEN: "{{ env('CRM_TOKEN') }}",
    };
</script>
@endif
@endsection
@section('js')
<!-- <script src="{{ asset('js/connection.js') }}"></script> -->
<script>
    var parentWindow = window.parent;
    window.addEventListener("message", (e) => {
        var data = e.data;
        console.log();
        console.log(data);
        if (data.type == 'location') {
            checkForauth(data);
        }
    });

    $(document).ready(function() {
        // for local testing
        // let dt = {
        //     location: window.env.CRM_LOCATION_ID,
        //     token: window.env.CRM_TOKEN,
        // };
        let params = new URLSearchParams(location.search);
        let dt = {
            location: params.get('location_id') || "",
            token: params.get('sessionkey') || "",
        }

        if (dt?.token && dt?.location && dt?.token != "" && dt?.location != "") {
            checkForauth(dt);
        } else {
            parentWindow.postMessage('authconnecting', '*');
        }
    });

    function checkForauth(dt) {
        loadingStart();
        console.log("Checking for URL");
        var url = "{{ route('auth.checking') }}";
        $.ajax({
            url: url,
            type: 'GET',
            data: {
                location: dt.location,
                token: dt.token
            },
            success: function(data) {
                loadingStop();
                console.log(data);
                toastr.success("Location connected successfully!");
                location.href = "{{ route('user') }}?v=" + new Date().getTime();
            },
            error: function(data) {
                console.log("Error in ajax call : " + data);
                loadingStop();
            },
            complete: function() {
                console.log("completion : " + data);
                loadingStop();
            }
        });
    }
</script>
@endsection