<!-- ========== Topbar Start ========== -->
<div class="navbar-custom">
    <div class="topbar container-fluid">
        <div class="d-flex align-items-center gap-lg-2 gap-1">

            <!-- Topbar Brand Logo -->
            <div class="logo-topbar">
                <!-- Logo light -->
                <a href="{{ url('/') }}" class="logo-light">
                    <span class="logo-lg">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="logo">
                    </span>
                    <span class="logo-sm">
                        <img src="{{ asset('assets/images/logo-sm.png') }}" alt="small logo">
                    </span>
                </a>

                <!-- Logo Dark -->
                <a href="{{ url('/') }}" class="logo-dark">
                    <span class="logo-lg">
                        <img src="{{ asset('assets/images/logo-dark.png') }}" alt="dark logo">
                    </span>
                    <span class="logo-sm">
                        <img src="{{ asset('assets/images/logo-dark-sm.png') }}" alt="small logo">
                    </span>
                </a>
            </div>

            <!-- Sidebar Toggle -->
            <button class="button-toggle-menu">
                <i class="mdi mdi-menu"></i>
            </button>

            <!-- Topnav Menu Toggle -->
            <button class="navbar-toggle" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <div class="lines">
                    <span></span><span></span><span></span>
                </div>
            </button>
        </div>

        <ul class="topbar-menu d-flex align-items-center gap-3">

			<!-- Theme Mode -->
			<li class="d-none d-sm-inline-block">
				<div class="nav-link cursor-pointer" wire:click="toggle">
					<i class="ri-moon-line font-22"></i>
				</div>
			</li>

            <!-- Fullscreen -->
            <li class="d-none d-md-inline-block">
                <a class="nav-link" href="#" data-toggle="fullscreen">
                    <i class="ri-fullscreen-line font-22"></i>
                </a>
            </li>

            <!-- User -->
            <li class="dropdown">
                <a class="nav-link dropdown-toggle arrow-none nav-user px-2" data-bs-toggle="dropdown" href="#" role="button">
                    <span class="account-user-avatar">
                        <img src="{{ asset('assets/images/users/avatar-1.jpg') }}" alt="user-image" width="32" class="rounded-circle">
                    </span>
                    <span class="d-lg-flex flex-column gap-1 d-none">
                        <h5 class="my-0">{{ Auth::user()->firstname ?: Auth::user()->username }}</h5>

						<h6 class="my-0 fw-normal">
							{{ Auth::user()->username }}
						</h6>

                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated profile-dropdown">
                    
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <i class="mdi mdi-account-circle me-1"></i>
                        <span>Profil</span>
                    </a>

                    <a href="{{ route('profile.settings') }}" class="dropdown-item">
                        <i class="mdi mdi-cog me-1"></i>
                        <span>Einstellungen</span>
                    </a>

                    <a href="#" class="dropdown-item" wire:click.prevent="logout">
                        <i class="mdi mdi-logout me-1"></i>
                        <span>Abmelden</span>
                    </a>
                </div>
            </li>
        </ul>
    </div>
</div>
<!-- ========== Topbar End ========== -->
