<x-appadmin-layout>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Class Logs</h1>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($logs->isEmpty())
                        <p>No logs found.</p>
                    @else
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Class ID</th>
                                    <th>Action</th>
                                    <th>Changes</th>
                                    <th>User</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $log)
                                    <tr>
                                        <td>{{ $log->class_id }}</td>
                                        <td>{{ $log->action }}</td>
                                        <td>
                                            @php
                                                $changes = json_decode($log->changes, true);
                                            @endphp
                                            @if (isset($changes['original']))
                                                <strong>Original:</strong>
                                                <pre>{{ json_encode($changes['original'], JSON_PRETTY_PRINT) }}</pre>
                                                <strong>Updated:</strong>
                                                <pre>{{ json_encode($changes['updated'], JSON_PRETTY_PRINT) }}</pre>
                                            @else
                                                <pre>{{ json_encode($changes, JSON_PRETTY_PRINT) }}</pre>
                                            @endif
                                        </td>
                                        <td>{{ $log->user->name }}</td>
                                        <td>{{ $log->created_at }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-appadmin-layout>
