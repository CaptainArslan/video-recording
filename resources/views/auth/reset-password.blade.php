<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Hexatech Solutions</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset('/assets/images/logos/favicon.png') }}" />
    <link rel="stylesheet" href="{{ asset('/assets/css/styles.min.css') }}" />
</head>

<body>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <div class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
            <div class="d-flex align-items-center justify-content-center w-100">
                <div class="row justify-content-center w-100">
                    <div class="col-md-8 col-lg-6 col-xxl-3">
                        <div class="card mb-0">
                            <div class="card-body">
                                <a href="{{ route('dashboard') }}" class="text-nowrap logo-img text-center d-block py-3 w-100">
                                    <img src="{{ asset('/assets/images/logos/dark-logo.svg') }}" width="180" alt="">
                                </a>
                                <x-auth-session-status class="mb-4" :status="session('status')" />
                                <x-auth-validation-errors class="mb-4 text-danger" :errors="$errors" />
                                <form method="POST" action="{{ route('password.update') }}">
                                    @csrf
                                    <input type="hidden" name="token" value="{{ $request->route('token') }}">
                                    <div class="mb-3">
                                        <x-label for="email" :value="__('Email')" />
                                        <x-input id="email" class="form-control" type="email" name="email" :value="old('email', $request->email)" required autofocus />
                                    </div>
                                    <div class="mb-4">
                                        <x-label for="password" :value="__('Password')" />
                                        <x-input id="password" class="form-control" type="password" name="password" required />
                                    </div>
                                    <div class="mb-4">
                                        <x-label for="password_confirmation" :value="__('Confirm Password')" />
                                        <x-input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required />
                                    </div>
                                    <x-button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2">
                                        {{ __('Reset Password') }}
                                    </x-button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('/assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>