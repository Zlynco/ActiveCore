@extends('layouts.appcoach')

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard Coach</h1>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Card: Upcoming Classes -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Upcoming Classes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @if(count($classes) > 0)
                                    <ul>
                                        @foreach($classes as $class)
                                            <li>{{ $class->name }} - {{ $class->day_of_week }} at {{ $class->start_time }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p>No upcoming classes</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Coach Availability Status -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Availability Status
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @if($coach->availability_status == 1)
                                    <span class="text-success">Available</span>
                                @else
                                    <span class="text-danger">Unavailable</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Info Section -->
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow mb-4">
                <!-- Card Header -->
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Important Notes</h6>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                    <p>Ensure to mark attendance for each class on time. Keep track of your schedules and notify admin for any changes or cancellations.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
