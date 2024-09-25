@extends('layouts.appcoach')
@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Member Attendance</h1>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('coach.attendances.update', $attendance->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="booking_id" class="form-label">Booking</label>
                            <select id="booking_id" name="booking_id" class="form-select form-control" required>
                                @foreach ($bookings as $booking)
                                    <option value="{{ $booking->id }}" {{ $booking->id == $attendance->booking_id ? 'selected' : '' }}>
                                        {{ $booking->id }} - {{ $booking->member->name }} - {{ $booking->coach->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('booking_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="attendance_date" class="form-label">Attendance Date</label>
                            <input type="date" id="attendance_date" name="attendance_date" class="form-control" value="{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('Y-m-d') }}" required>
                            @error('attendance_date')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-select form-control" required>
                                <option value="Present" {{ $attendance->status == 'Present' ? 'selected' : '' }}>Present</option>
                                <option value="Absent" {{ $attendance->status == 'Absent' ? 'selected' : '' }}>Absent</option>
                                <option value="Not Yet" {{ $attendance->status == 'Not Yet' ? 'selected' : '' }}>Not Yet</option>
                            </select>
                            @error('status')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Update Attendance</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
