<x-appadmin-layout>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Attendance</h1>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('admin.attendances.update', $attendance->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="class_id">Class</label>
                            <select id="class_id" name="class_id" class="form-control">
                                <option value="">No Class</option> <!-- Opsi untuk tidak ada kelas -->
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ $attendance->class_id == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="user_id">Coach</label>
                            <select name="user_id" id="user_id" class="form-control">
                                @foreach ($coaches as $coach)
                                    <option value="{{ $coach->id }}" {{ $attendance->user_id == $coach->id ? 'selected' : '' }}>
                                        {{ $coach->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="attendance_date">Attendance Date</label>
                            <input type="date" name="attendance_date" id="attendance_date" class="form-control" value="{{ $attendance->attendance_date->format('Y-m-d') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="Present" {{ $attendance->status == 'Present' ? 'selected' : '' }}>Present</option>
                                <option value="Sick" {{ $attendance->status == 'Sick' ? 'selected' : '' }}>Sick</option>
                                <option value="Excused" {{ $attendance->status == 'Excused' ? 'selected' : '' }}>Excused</option>
                                <option value="Absent" {{ $attendance->status == 'Absent' ? 'selected' : '' }}>Absent</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="check_in">Check In</label>
                            <input type="time" name="check_in" id="check_in" class="form-control" value="{{ $attendance->check_in }}">
                        </div>

                        <div class="form-group">
                            <label for="check_out">Check Out</label>
                            <input type="time" name="check_out" id="check_out" class="form-control" value="{{ $attendance->check_out }}">
                        </div>

                        <div class="form-group">
                            <label for="absence_reason">Absence Reason</label>
                            <textarea name="absence_reason" id="absence_reason" class="form-control">{{ $attendance->absence_reason }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="unique_code">Unique Code</label>
                            <input type="text" name="unique_code" id="unique_code" class="form-control" value="{{ $attendance->unique_code }}" readonly>
                        </div>                        
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-appadmin-layout>
