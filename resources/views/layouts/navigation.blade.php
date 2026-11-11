<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
            <x-application-logo style="height:40px; width:auto;" />
            <span class="ms-2 fw-bold text-primary">Alumni Portal</span>
        </a>

        <!-- Hamburger / Toggler -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @auth
                    @if(auth()->user()->is_approved)
                        @if(auth()->user()->hasRole('admin'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active fw-bold' : '' }}" href="{{ route('admin.dashboard') }}">
                                    Admin Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.pending-approvals') ? 'active fw-bold' : '' }}" href="{{ route('admin.pending-approvals') }}">
                                    Pending Approvals
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.user-management') ? 'active fw-bold' : '' }}" href="{{ route('admin.user-management') }}">
                                    User Management
                                </a>
                            </li>
                        @elseif(auth()->user()->hasRole('alumni'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('alumni.dashboard') ? 'active fw-bold' : '' }}" href="{{ route('alumni.dashboard') }}">
                                    Alumni Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('alumni.profile') ? 'active fw-bold' : '' }}" href="{{ route('alumni.profile') }}">
                                    My Profile
                                </a>
                            </li>
                        @elseif(auth()->user()->hasRole('student'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active fw-bold' : '' }}" href="{{ route('student.dashboard') }}">
                                    Student Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('student.profile') ? 'active fw-bold' : '' }}" href="{{ route('student.profile') }}">
                                    My Profile
                                </a>
                            </li>
                        @endif
                    @endif
                @endauth
            </ul>

            <!-- Right Side: Notifications & User Dropdown -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                @auth
                    <!-- Notifications Dropdown -->
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link position-relative" href="#" id="navbarNotifications" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                            <i class="fas fa-bell fa-lg text-secondary"></i>
                            @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp
                            @if($unreadCount > 0)
                                <span id="notificationBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ $unreadCount }}
                                    <span class="visually-hidden">unread notifications</span>
                                </span>
                            @endif
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="navbarNotifications" style="min-width:300px;">
                            <li class="dropdown-header fw-bold text-primary">Notifications</li>

                            @forelse(auth()->user()->notifications()->latest()->take(5)->get() as $notification)
                                <li>
                                    <a class="dropdown-item d-flex justify-content-between align-items-start
                                        {{ is_null($notification->read_at) ? 'fw-bold bg-light mark-as-read' : '' }}"
                                        href="{{ $notification->data['url'] ?? '#' }}"
                                        data-id="{{ $notification->id }}">
                                        <div class="me-2">
                                            {{ $notification->data['message'] ?? 'Notification' }}
                                            <br>
                                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                        </div>
                                    </a>
                                </li>
                            @empty
                                <li class="dropdown-item text-center text-muted small">No notifications</li>
                            @endforelse

                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-center fw-semibold text-primary" href="{{ route('notifications.all') }}">
                                    View all notifications
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- User Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle fw-semibold text-dark" href="#" id="navbarUserDropdown"
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="navbarUserDropdown">
                            @if(auth()->user()->hasRole('admin'))
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Settings</a></li>
                            @elseif(auth()->user()->hasRole('alumni'))
                                <li><a class="dropdown-item" href="{{ route('alumni.profile') }}">My Profile</a></li>
                            @elseif(auth()->user()->hasRole('student'))
                                <li><a class="dropdown-item" href="{{ route('student.profile') }}">My Profile</a></li>
                            @endif

                            <li><hr class="dropdown-divider"></li>

                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-1"></i> Log Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>

                @else
                    <!-- Guest Links -->
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="{{ route('register') }}">Register</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
