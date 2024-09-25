<x-appadmin-layout>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add New Attendance</h1>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('admin.attendances.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="class_id">Class</label>
                            <select id="class_id" name="class_id" class="form-control">
                                <option value="">No Class</option>  <!-- Opsi No Class -->
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
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
                                    <option value="{{ $coach->id }}">{{ $coach->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="attendance_date">Attendance Date</label>
                            <input type="date" name="attendance_date" id="attendance_date" class="form-control"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="Present">Present</option>
                                <option value="Sick">Sick</option>
                                <option value="Excused">Excused</option>
                                <option value="Absent">Absent</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="check_in">Check In</label>
                            <input type="time" name="check_in" id="check_in" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="check_out">Check Out</label>
                            <input type="time" name="check_out" id="check_out" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="absence_reason">Absence Reason</label>
                            <textarea name="absence_reason" id="absence_reason" class="form-control"></textarea>
                        </div>

                        @if (isset($attendance))
                        <div class="form-group">
                            <label for="unique_code">Unique Code</label>
                            <input type="text" name="unique_code" id="unique_code" class="form-control"
                                value="{{ $attendance->unique_code }}" readonly>
                        </div>
                        @endif
                        
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-appadmin-layout>
