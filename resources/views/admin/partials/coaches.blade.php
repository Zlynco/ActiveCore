@if ($coaches->isEmpty())
    <p>No coaches pending approval.</p>
@else
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($coaches as $coach)
                <tr>
                    <td>{{ $coach->name }}</td>
                    <td>{{ $coach->email }}</td>
                    <td>{{ ucfirst($coach->status) }}</td>
                    <td>
                        @if ($coach->status === 'pending')
                            <form action="{{ route('admin.coach.approve', $coach->id) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-success">Approve</button>
                            </form>
                            <form action="{{ route('admin.coach.reject', $coach->id) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-danger">Reject</button>
                            </form>
                        @endif

                        <!-- Edit Button -->
                        <a href="{{ route('admin.coach.edit', $coach->id) }}" class="btn btn-warning">Edit</a>

                        <!-- Delete Button -->
                        <form action="{{ route('admin.coach.delete', $coach->id) }}" method="POST"
                            style="display:inline;">
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
