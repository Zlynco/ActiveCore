<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone Number</th> <!-- Tambahkan kolom phone_number -->
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($members as $member)
        <tr>
            <td>{{ $member->name }}</td>
            <td>{{ $member->email }}</td>
            <td>{{ $member->phone_number }}</td> <!-- Menampilkan phone_number -->
            <td>
                <a href="{{ route('admin.user.edit', $member->id) }}" class="btn btn-warning btn-sm">Edit</a>
                <form action="{{ route('admin.user.delete', $member->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>