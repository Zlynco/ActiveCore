<x-appadmin-layout>
    <div class="container">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edit Member</h1>
        </div>
        <form action="{{ route('admin.user.update', $user->id) }}" method="POST">
            @csrf
            @method('POST') <!-- Perlu diganti menjadi PUT -->
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" required>
            </div>
            <div class="mb-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ $user->phone_number }}">
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</x-appadmin-layout>
