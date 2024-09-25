@extends('layouts.appcoach')

@section('content')
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow mb-4">
                <!-- Card Header -->
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Member Attendance</h6>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                    <a href="{{ route('coach.attendances.create') }}" class="btn btn-success mb-3">Add Attendance</a>
                    @if ($memberAttendances->isEmpty())
                        <p>No member attendances found.</p>
                    @else
                        <div class="container my-4" >
                            <div class="table-responsive" style="max-height: 500px; overflow-y: scroll;">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Member</th>
                                            <th>Attendance Date</th>
                                            <th>QR Code</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($memberAttendances as $attendance)
                                            <tr>
                                                <td>{{ $attendance->booking->id }}</td>
                                                <td>{{ $attendance->member->name }}</td>
                                                <td>{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('Y-m-d') }}</td>
                                                <td>
                                                    <button type="button" class="btn btn-info" data-bs-toggle="modal"
                                                        data-bs-target="#qrCodeModal"
                                                        data-qr-code="{{ asset('qrcodes/QR-' . $attendance->unique_code . '.png') }}"
                                                        data-status="{{ $attendance->status }}">
                                                        Show QR Code
                                                    </button>
                                                </td>
                                                <td>
                                                    <a href="{{ route('coach.attendances.edit', $attendance->id) }}"
                                                        class="btn btn-warning">Edit</a>
                                                    <form
                                                        action="{{ route('coach.attendances.destroy', $attendance->id) }}"
                                                        method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div> <!-- End of table-responsive -->
                        </div>

                        <!-- Modal untuk QR Code -->
                        <div class="modal fade" id="qrCodeModal" tabindex="-1"
                            aria-labelledby="qrCodeModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="qrCodeModalLabel">QR Code</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <img id="qrCodeImage" src="" alt="QR Code" style="width: 100%; height: auto;">
                                        <!-- Tombol Status -->
                                        <button id="statusButton" type="button" class="btn" style="width: 100%; margin-top: 10px;" disabled></button>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- End of container -->
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript untuk modal QR Code -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var qrCodeModal = document.getElementById('qrCodeModal');
            qrCodeModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var qrCodeSrc = button.getAttribute('data-qr-code');
                var status = button.getAttribute('data-status');
                var qrCodeImage = qrCodeModal.querySelector('#qrCodeImage');
                var statusButton = qrCodeModal.querySelector('#statusButton');

                qrCodeImage.src = qrCodeSrc;

                statusButton.classList.remove('btn-success', 'btn-danger', 'btn-secondary');
                statusButton.classList.add('btn');
                statusButton.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                switch (status) {
                    case 'Present':
                        statusButton.classList.add('btn-success');
                        break;
                    case 'Absent':
                        statusButton.classList.add('btn-danger');
                        break;
                    case 'Not Yet':
                        statusButton.classList.add('btn-secondary');
                        break;
                    default:
                        statusButton.classList.add('btn-light');
                        break;
                }
            });
        });
    </script>
@endsection
