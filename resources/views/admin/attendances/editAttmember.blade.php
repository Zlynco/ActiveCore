<x-appcoach-layout>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add New Member Attendance</h1>
        <a href="{{ route('coach.memberatt') }}" class="btn btn-secondary">Back to List</a>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('coach.attendances.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="booking_id">Booking</label>
                            <select name="booking_id" id="booking_id" class="form-control" required>
                                <option value="">Select Booking</option>
                                @foreach ($bookings as $booking)
                                    <option value="{{ $booking->id }}">{{ $booking->member->name }} with {{ $booking->coach->name }} on {{ $booking->booking_date }}</option>
                                @endforeach
                            </select>
                            @error('booking_id')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                
                        <div class="form-group">
                            <label for="member_id">Member</label>
                            <select name="member_id" id="member_id" class="form-control" required>
                                <option value="">Select Member</option>
                                @foreach ($bookings as $booking)
                                    <option value="{{ $booking->user_id }}" {{ $booking->user_id == old('member_id') ? 'selected' : '' }}>
                                        {{ $booking->member->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('member_id')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                
                        <div class="form-group">
                            <label for="coach_id">Coach</label>
                            <select name="coach_id" id="coach_id" class="form-control" required>
                                <option value="">Select Coach</option>
                                @foreach ($bookings as $booking)
                                    <option value="{{ $booking->coach_id }}" {{ $booking->coach_id == old('coach_id') ? 'selected' : '' }}>
                                        {{ $booking->coach->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('coach_id')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                
                        <div class="form-group">
                            <label for="attendance_date">Attendance Date</label>
                            <input type="date" name="attendance_date" id="attendance_date" class="form-control" value="{{ old('attendance_date') }}" required>
                            @error('attendance_date')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                
                        <button type="submit" class="btn btn-primary">Create Attendance</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-appcoach-layout>
