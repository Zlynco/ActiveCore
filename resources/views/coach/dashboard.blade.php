@extends('layouts.appcoach')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet" />

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard Coach</h1>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Kartu Upcoming Classes Today -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-bottom-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Upcoming Classes Today
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @if ($classes->isEmpty())
                                    No classes scheduled for today.
                                @else
                                    <ul class="list-unstyled">
                                        @foreach ($classes as $class)
                                            <li class="mb-1">
                                                <strong>{{ $class->name }}</strong> -
                                                {{ \Carbon\Carbon::parse($class->start_time)->format('H:i') }} -
                                                {{ \Carbon\Carbon::parse($class->end_time)->format('H:i') }}
                                            </li>
                                        @endforeach
                                    </ul>
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
    <div class="card 
        @if($isAvailable) border-bottom-success @else border-bottom-danger @endif shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">
                        Availability Status
                    </div>
                    <div class="h5 mb-0 font-weight-bold 
                        @if($isAvailable) text-success @else text-danger @endif">
                        @if($isAvailable)
                            Available
                        @else
                            Not Available
                            <br>
                            @if($currentClass)
                                <small>Currently teaching class.: <strong>{{ $currentClass->name }}</strong></small>
                                <br>
                                <small>Time: {{ \Carbon\Carbon::parse($currentClass->start_time)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($currentClass->end_time)->format('H:i') }}</small>
                            @elseif($currentBooking)
                                <small>Currently booked by: <strong>{{ $currentBooking->user->name ?? 'N/A' }}</strong></small>
                                <br>
                                <small>Time: {{ \Carbon\Carbon::parse($currentBooking->start_booking_time)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($currentBooking->end_booking_time)->format('H:i') }}</small>
                            @endif
                        @endif
                    </div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-user-check fa-2x 
                        @if($isAvailable) text-success @else text-danger @endif"></i>
                </div>
            </div>
        </div>
    </div>
</div>



    </div>

    <!-- Calendar Section -->
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow mb-4">
                <!-- Card Header -->
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Class Schedule</h6>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                    <div id="calendar"></div> <!-- Tempat untuk menampilkan kalender -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk Menampilkan Detail Kelas -->
    <div class="modal fade" id="classDetailModal" tabindex="-1" role="dialog" aria-labelledby="classDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="classDetailModalLabel">Class Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="classTitle"></p>
                    <div id="classTime"></div>
                    <p id="classQuota"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@endsection
