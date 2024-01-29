<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> @yield('title') </title>
    <link rel="shortcut icon" type="image/png" href="{{ asset('/assets/images/logos/favicon.png') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.min.css') }}" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- datatable -->
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" integrity="sha512-vKMx8UnXk60zUwyUnUPM3HbQo8QfmNx7+ltw8Pm5zLusl1XIfwcxo8DbWCqMGKaWeNxWA8yrx5v3SaVpMvR3CA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://kit.fontawesome.com/c3e204ff59.js" crossorigin="anonymous"></script>


    <!-- Video.js CSS -->
    <link href="https://unpkg.com/video.js@7.20.1/dist/video-js.min.css" rel="stylesheet">
    <!-- videojs-record CSS -->
    <link href="https://unpkg.com/videojs-record/dist/css/videojs.record.min.css" rel="stylesheet">

</head>

<body>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        @include('partials.sidebar')
        <!--  Main wrapper -->
        <div class="body-wrapper">
            @include('partials.header')
            <div class="container-fluid">
                @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
                <!-- <script>
                    toastr.success("{{ session('success') }}", {
                        timeOut: 10000
                    });
                </script> -->
                @endif
                @if (session('error'))
                <div class="alert alert-danger" role="alert">
                    {{ session('error') }}
                </div>
                <!-- <script>
                    toastr.error("{{ session('error') }}", {
                        timeOut: 10000
                    });
                </script> -->
                @endif
                @yield('section')
                @include('partials.footer')
            </div>
        </div>
    </div>
    {{-- <script src="{{ asset('/assets/libs/jquery/dist/jquery.min.js') }}"></script> --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script src="{{ asset('/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('/assets/js/sidebarmenu.js') }}"></script>
    <script src="{{ asset('/assets/js/app.min.js') }}"></script>
    <script src="{{ asset('js/my-custom.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>


    <!-- Video.js and RecordRTC -->
    <script src="https://unpkg.com/video.js@7.20.1/dist/video.min.js"></script>
    <script src="https://unpkg.com/recordrtc/RecordRTC.js"></script>
    <script src="https://unpkg.com/webrtc-adapter/out/adapter.js"></script>
    <!-- videojs-record -->
    <script src="https://unpkg.com/videojs-record/dist/videojs.record.min.js"></script>

    <script src="{{ asset('js/video-js.js') }}"></script>

    @yield('js')
</body>

</html>