<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('coach.dashboard') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-dumbbell"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Active Core</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="{{ route('coach.dashboard') }}">
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

    <!-- Nav Item - Manage Classes -->
    <li class="nav-item">
        <a class="nav-link" href="{{ route('coach.kelas') }}">
            <i class="fas fa-fw fa-bolt"></i>
            <span>Your Classes</span>
        </a>
    </li>

    <!-- Nav Item - Manage Bookings -->
    <li class="nav-item">
        <a class="nav-link" href="{{ route('coach.booking') }}">
            <i class="fas fa-fw fa-calendar"></i>
            <span>Bookings</span>
        </a>
    </li>

    <!-- Nav Item - Coach Management -->
    <li class="nav-item">
        <a class="nav-link" href="{{ route('coach.attendance') }}">
            <i class="fas fa-fw fa-chalkboard-teacher"></i>
            <span>Attendances</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('coach.memberAttendance') }}">
            <i class="fas fa-fw fa-chalkboard-teacher"></i>
            <span>Member Attendances</span>
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
