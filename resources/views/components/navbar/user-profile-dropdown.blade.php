{{-- resources/views/components/navbar/user-profile-dropdown.blade.php --}}
<li class="nav-item dropdown user-profile-dropdown order-lg-0 order-1">
    <a href="javascript:void(0);"
       class="nav-link dropdown-toggle user"
       id="userProfileDropdown"
       data-bs-toggle="dropdown"
       aria-haspopup="true"
       aria-expanded="false">
        <div class="avatar-container">
            <div class="avatar avatar-sm avatar-indicators avatar-online">
                <img alt="avatar"
                     src="{{ auth()->user()->avatar ?? asset('src/assets/img/profile-30.png') }}"
                     class="rounded-circle">
            </div>
        </div>
    </a>
    <div class="dropdown-menu position-absolute" aria-labelledby="userProfileDropdown">
        <div class="user-profile-section">
            <div class="media mx-auto">
                <div class="emoji me-2">
                    &#x1F44B;
                </div>
                <div class="media-body">
                    <h5>{{ auth()->user()->name ?? 'User' }}</h5>
                    {{-- Display user roles --}}
                    <p>{{ auth()->user()->roles->pluck('name')->join(', ') ?: 'Member' }}</p>
                </div>
            </div>
        </div>

        {{-- Link Profile - Accessible by all authenticated users --}}
        @can('update profile')
        <div class="dropdown-item">
            <a href="{{ route('profile.index') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Profile</span>
            </a>
        </div>
        @endcan

        {{-- Company Profile - Only for users with permission --}}
        @can('update profile company')
        <div class="dropdown-item">
            <a href="{{ route('companies.edit-profile') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-briefcase">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
                <span>Company Profile</span>
            </a>
        </div>
        @endcan

{{-- Logout - Always visible --}}
        <div class="dropdown-item">
            {{-- Perhatikan perubahan ID di bawah ini --}}
            <form method="POST" action="{{ route('logout') }}" id="logout-form-header">
                @csrf
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form-header').submit();">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>Log Out</span>
                </a>
            </form>
        </div>
    </div>
</li>
