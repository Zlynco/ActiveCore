<x-appadmin-layout>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Attendances</h1>
            <div class="d-flex mt-3 mt-sm-0">
                <a id="addNewAttendanceBtn" href="{{ route('admin.attendances.create') }}" class="btn btn-primary">Add New
                    Attendance</a>
                <a href="{{ route('admin.attendances.logs') }}" class="btn btn-info ml-2">Show Attendance Log</a>
            </div>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Tab navigation -->
                    <ul class="nav nav-tabs" id="attendanceTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="coach-attendance-tab" data-bs-toggle="tab"
                                href="#coach-attendance" role="tab" aria-controls="coach-attendance"
                                aria-selected="true">Coach Attendance</a>
                        </li>
                        <!--<li class="nav-item" role="presentation">-->
                        <!--    <a class="nav-link" id="member-attendance-tab" data-bs-toggle="tab"-->
                        <!--        href="#member-attendance" role="tab" aria-controls="member-attendance"-->
                        <!--        aria-selected="false">Member Attendance</a>
                        </li>-->
                        <form method="GET" action="{{ route('admin.attendance') }}">
                            <div class="mb-4 ml-3">
                                <x-text-input id="search" name="search" type="text" placeholder="Search..."
                                    :value="request('search')" class="form-control" />
                                <x-input-error :messages="$errors->get('search')" class="mt-2" />
                            </div>
                        </form>
                    </ul>

                    <!-- Tab content -->
                    <div class="tab-content" id="attendanceTabContent" style="max-height: 400px; overflow-y: scroll;">
                        <!-- Coach attendance tab -->
                        <div class="tab-pane fade show active" id="coach-attendance" role="tabpanel"
                            aria-labelledby="coach-attendance-tab">
                            @if ($attendances->isEmpty())
                                <p>No coach attendances found.</p>
                            @else
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Class</th>
                                            <th>Coach</th>
                                            <th>Attendance Date</th>
                                            <th>Status</th>
                                            <th>Check In</th>
                                            <th>Check Out</th>
                                            <th>Absence Reason</th>
                                            <th>Unique Code</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($attendances as $attendance)
                                            <tr>
                                                <td>{{ optional($attendance->class)->name ?? 'No Class' }}</td>
                                                <td>{{ $attendance->coach->name }}</td>
                                                <td>{{ $attendance->attendance_date->format('Y-m-d') }}</td>
                                                <td>{{ $attendance->status }}</td>
                                                <td>{{ $attendance->check_in }}</td>
                                                <td>{{ $attendance->check_out }}</td>
                                                <td>{{ $attendance->absence_reason }}</td>
                                                <td>{{ $attendance->unique_code }}</td>
                                                <td>
                                                    <a href="{{ route('admin.attendances.edit', $attendance->id) }}"
                                                        class="btn btn-warning">Edit</a>
                                                    <form
                                                        action="{{ route('admin.attendances.delete', $attendance->id) }}"
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
                            @endif
                        </div>

                        <!-- Member attendance tab -->
                        <div class="tab-pane fade" id="member-attendance" role="tabpanel"
                            aria-labelledby="member-attendance-tab">
                            @if ($memberAttendances->isEmpty())
                                <p>No member attendances found.</p>
                            @else
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Member</th>
                                            <th>Coach</th>
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
                                                <td>{{ $attendance->coach->name }}</td>
                                                <td>{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('Y-m-d') }}
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-info" data-bs-toggle="modal"
                                                        data-bs-target="#qrCodeModal"
                                                        data-qr-code="{{ asset('qrcodes/QR-' . $attendance->unique_code . '.png') }}"
                                                        data-status="{{ $attendance->status }}">
                                                        Show QR Code
                                                    </button>

                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.attendances.editAttmember', $attendance->id) }}"
                                                        class="btn btn-warning">Edit</a>
                                                    <form
                                                        action="{{ route('admin.attendances.deleteAttmember', $attendance->id) }}"
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
                                <!-- Modal -->
                                <div class="modal fade" id="qrCodeModal" tabindex="-1"
                                    aria-labelledby="qrCodeModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="qrCodeModalLabel">QR Code</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <img id="qrCodeImage" src="" alt="QR Code"
                                                    style="width: 100%; height: auto;">
                                                <!-- Status Display -->
                                                <button id="statusButton" type="button" class="btn"
                                                    style="width: 100%; margin-top: 10px;" disabled></button>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var addNewAttendanceBtn = document.getElementById('addNewAttendanceBtn');
            var tabs = document.querySelectorAll('#attendanceTab a[data-bs-toggle="tab"]');

            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    var activeTabId = tab.getAttribute('href').substring(1);
                    if (activeTabId === 'member-attendance') {
                        addNewAttendanceBtn.setAttribute('href',
                            '{{ route('admin.attendances.createAttmember') }}');
                    } else {
                        addNewAttendanceBtn.setAttribute('href',
                            '{{ route('admin.attendances.create') }}');
                    }
                });
            });

            // Trigger click event on the default active tab to set the initial state of the button
            var activeTab = document.querySelector('#attendanceTab .nav-link.active');
            if (activeTab) {
                var activeTabId = activeTab.getAttribute('href').substring(1);
                if (activeTabId === 'member-attendance') {
                    addNewAttendanceBtn.setAttribute('href', '{{ route('admin.attendances.createAttmember') }}');
                }
            }
        });

        // JavaScript to handle QR Code modal
        document.addEventListener('DOMContentLoaded', function() {
            var qrCodeModal = document.getElementById('qrCodeModal');
            qrCodeModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget; // Button that triggered the modal
                var qrCodeSrc = button.getAttribute('data-qr-code'); // Get QR code URL from data attribute
                var status = button.getAttribute('data-status'); // Get status from data attribute
                var qrCodeImage = qrCodeModal.querySelector(
                '#qrCodeImage'); // Find the image element in the modal
                var statusButton = qrCodeModal.querySelector(
                '#statusButton'); // Find the status button in the modal

                qrCodeImage.src = qrCodeSrc; // Set the image source to QR code URL

                // Reset class of the status button to default
                statusButton.classList.remove('btn-success', 'btn-danger', 'btn-secondary');
                statusButton.classList.add('btn'); // Ensure the button has the 'btn' class

                // Update status button text and color based on status
                statusButton.textContent = status.charAt(0).toUpperCase() + status.slice(
                1); // Capitalize first letter
                switch (status) {
                    case 'Present':
                        statusButton.classList.add('btn-success'); // Green for present
                        break;
                    case 'Absent':
                        statusButton.classList.add('btn-danger'); // Red for absent
                        break;
                    case 'Not Yet':
                        statusButton.classList.add('btn-secondary'); // Gray for not yet
                        break;
                    default:
                        statusButton.classList.add('btn-light'); // Default light button
                        break;
                }
            });
        });
    </script>

</x-appadmin-layout>
