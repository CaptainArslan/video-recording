<header class="app-header">
    <nav class="navbar navbar-expand-lg navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item d-block d-xl-none">
                <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
                    <i class="ti ti-menu-2"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link nav-icon-hover" href="javascript:void(0)">
                    <i class="ti ti-bell-ringing"></i>
                    <div class="notification bg-primary rounded-circle"></div>
                </a>
            </li>
        </ul>
        <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
            <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
                <button class="btn btn-outline-warning mx-3 mt-2 d-block">{{ is_role() }}</button>
                @if (session('super_admin') && !empty(session('super_admin')))
                <li class="nav-item">
                    <a href="{{ route('backtoadmin') }}?admin=1" class="btn btn-outline-danger mx-3 mt-2 d-block">Back to super admin </a>
                </li>
                @endif
                @if (session('company_admin') && !empty(session('company_admin')))
                <li class="nav-item">
                    <a href="{{ route('backtoadmin') }}?company=1" class="btn btn-outline-primary mx-3 mt-2 d-block">Login as agency </a>
                </li>
                @endif
                <li class="nav-item dropdown">
                    <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src=" {{ asset( auth()->user()->image ?? '/assets/images/profile/user-1.jpg') }}" alt="" width="35" height="35" class="rounded-circle">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                        <div class="message-body">
                            <a href="{{ route('profile') }}" class="d-flex align-items-center gap-2 dropdown-item">
                                <i class="ti ti-user fs-6"></i>
                                <p class="mb-0 fs-3"> @if (Auth::check())
                                    {{ Auth::user()->name ? ucfirst(Auth::user()->name) : 'Profile' }}
                                    @endif
                                </p>
                            </a>
                            <a href="{{ route('password') }}" class="d-flex align-items-center gap-2 dropdown-item">
                                <i class="ti ti-mail fs-6"></i>
                                <p class="mb-0 fs-3">Update Password</p>
                            </a>
                            <a href="#" class="btn btn-outline-primary mx-3 mt-2 d-block" onclick="document.getElementById('logout').submit()">Logout</a>
                            <form method="POST" action="{{ route('logout') }}" id="logout">
                                @csrf
                            </form>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
</header>