@auth
    @if (is_role() == 'super_admin' || is_role() == 'admin')
        <aside class="left-sidebar">
            <div>
                <div class="brand-logo d-flex align-items-center justify-content-between">
                    <a href="{{ route('dashboard') }}" class="text-nowrap logo-img">
                        <img src="{{ asset('/' . setting('company_logo')) }}" width="180" alt="" />
                    </a>
                    <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
                        <i class="ti ti-x fs-8"></i>
                    </div>
                </div>
                <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
                    <ul id="sidebarnav">
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('dashboard') }}" aria-expanded="false">
                                <span>
                                    <i class="ti ti-layout-dashboard"></i>
                                </span>
                                <span class="hide-menu">Dashboard</span>
                            </a>
                        </li>
                        @if (is_role() == 'user' || is_role() == 'company')
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('recordings.index') }}" aria-expanded="false">
                                    <span>
                                        <i class="fa fa-play" aria-hidden="true"></i>
                                    </span>
                                    <span class="hide-menu">My Library</span>
                                </a>
                            </li>
                        @endif
                        @if (is_role() == 'user' || is_role() == 'company')
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('contacts.index') }}" aria-expanded="false">
                                    <span>
                                        <i class="ti ti-article"></i>
                                    </span>
                                    <span class="hide-menu">Contacts</span>
                                </a>
                            </li>
                        @endif
                        @if (is_role() == 'admin' || is_role() == 'admin')
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('users.index') }}" aria-expanded="false">
                                    <span>
                                        <i class="fa fa-users" aria-hidden="true"></i>
                                    </span>
                                    <span class="hide-menu">Users</span>
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('plans.index') }}" aria-expanded="false">
                                    <span>
                                        <i class="fa fa-files-o" aria-hidden="true"></i>
                                    </span>
                                    <span class="hide-menu">Plans</span>
                                </a>
                            </li>
                        @endif
                        @if (is_role() == 'company' || is_role() == 'admin')
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('settings.store') }}" aria-expanded="false">
                                    <span>
                                        <i class="fa fa-cog" aria-hidden="true"></i>
                                    </span>
                                    <span class="hide-menu">Setting</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </aside>
    @endif
@endauth
