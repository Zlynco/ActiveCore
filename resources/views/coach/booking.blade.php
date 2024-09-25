@extends('layouts.appcoach')

@section('content')
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow mb-4">
                <!-- Card Header -->
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Your Bookings</h6>
                    
                </div>
                <!-- Card Body -->
                <div class="card-body">
                    <!-- Search Form -->
                    <form method="GET" action="{{ route('coach.booking') }}">
                        <div class="mb-4">
                            <x-text-input id="search" name="search" type="text" placeholder="Search Booking..."
                                :value="request('search')" class="form-control" />
                            <x-input-error :messages="$errors->get('search')" class="mt-2" />
                        </div>
                    </form>

                    <!-- Booking List -->
                    <div style="max-height:380px; overflow-y: scroll;">
                        @if ($bookings->count() > 0)
                            <ul class="list-group">
                                @foreach ($bookings as $booking)
                                    <li class="list-group-item">
                                        <strong>Booking Code: {{ $booking->booking_code }}</strong><br>
                                        Member: {{ $booking->user->name }}<br> <!-- Pastikan ada relasi ke user (member) -->
                                        Booking Date: {{ $booking->booking_date->format('d M Y') }}<br>
                                        Booking Time: {{ $booking->booking_time->format('H:i') }}<br>
                                        Sessions: {{ $booking->session_count }}<br>
                                        Payment Required: {{ $booking->payment_required ? 'Yes' : 'No' }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p>No bookings found.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
