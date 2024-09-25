@extends('layouts.appcoach')

@section('content')
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card shadow mb-4">
            <!-- Card Header -->
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Attendance Form</h6>
            </div>
            <!-- Card Body -->
            <div class="card-body" style="max-height: 500px; overflow-y: scroll;">
                <form id="attendanceForm" method="POST" action="{{ route('coach.attendance.store') }}">
                    @csrf
                    <div class="form-group">
                        <label for="class_id">Class</label>
                        <select id="class_id" name="class_id" class="form-control">
                            <option value="No Class">No Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('class_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="attendance_date">Attendance Date</label>
                        <input type="date" id="attendance_date" name="attendance_date" class="form-control" required>
                        @error('attendance_date')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="Present">Present</option>
                            <option value="Sick">Sick</option>
                            <option value="Excused">Excused</option>
                            <option value="Absent">Absent</option>
                        </select>
                        @error('status')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="check_in">Check In Time</label>
                        <input type="time" id="check_in" name="check_in" class="form-control">
                        @error('check_in')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="check_out">Check Out Time</label>
                        <input type="time" id="check_out" name="check_out" class="form-control">
                        @error('check_out')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="absence_reason">Absence Reason</label>
                        <textarea id="absence_reason" name="absence_reason" class="form-control" rows="3"></textarea>
                        @error('absence_reason')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Submit Attendance</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script>
    document.getElementById('attendanceForm').addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission
    
        const form = this;
        const formData = new FormData(form);
    
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = data.redirect; // Redirect to a specified URL
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error!',
                text: 'An unexpected error occurred.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    });
    </script>
@endsection
