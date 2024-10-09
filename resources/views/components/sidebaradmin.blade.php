<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('admin.dashboard') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-dumbbell"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Active Core</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Management
    </div>

    <!-- Nav Item - Manage Users -->
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.user') }}">
            <i class="fas fa-fw fa-user"></i>
            <span>Manage Users</span>
        </a>
    </li>
    <!-- Nav Item - Manage Category -->
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.category') }}">
            <i class="fas fa-fw fa-list"></i>
            <span>Manage Category</span>
        </a>
    </li>
        <!-- Nav Item - Manage Room -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.rooms') }}">
                <i class="fas fa-fw fa-door-open"></i>
                <span>Manage Room</span>
            </a>
        </li>
    <!-- Nav Item - Manage Classes -->
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.kelas') }}">
            <i class="fas fa-fw fa-bolt"></i>
            <span>Manage Classes</span>
        </a>
    </li>

    <!-- Nav Item - Manage Bookings -->
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.booking') }}">
            <i class="fas fa-fw fa-calendar"></i>
            <span>Manage Bookings</span>
        </a>
    </li>

    <!-- Nav Item - Coach Management -->
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.attendance') }}">
            <i class="fas fa-fw fa-chalkboard-teacher"></i>
            <span>Manage Attendances</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Sidebar Toggler -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->
