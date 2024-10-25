<nav x-data="{ open: false }" class="navbar navbar-expand navbar-light bg-white topbar mb-3 static-top shadow">
    <!-- Primary Navigation Menu -->

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>


    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">





        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <!-- Tampilkan nama pengguna yang sedang login -->
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ auth()->user()->name }}</span>
                <img class="img-profile rounded-circle"
                    src="{{ auth()->user()->profile_image ? asset('storage/' . auth()->user()->profile_image) : asset('startbootstrap-sb-admin-2-master/img/undraw_profile.svg') }}">
            </a>


            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <div class="mt-3 space-y-1">
                    @php
                        $user = auth()->user();
                    @endphp

                    <x-responsive-nav-link :href="match ($user->role) {
                        'admin' => route('profile.admin.edit'),
                        'coach' => route('profile.coach.edit'),
                        default => route('profile.edit'),
                    }">

                        {{ match ($user->role) {
                            'admin' => __('Profile Admin'),
                            'coach' => __('Profile Coach'),
                            default => __('Profile'),
                        } }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>

        </li>

    </ul>

</nav>
