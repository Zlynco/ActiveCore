<x-appadmin-layout>
    <div class="container">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edit Coach</h1>
        </div>
        
        <form action="{{ route('admin.coach.update', $coach->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $coach->name }}" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ $coach->email }}" required>
            </div>
            <div class="mb-3">
                <label for="categories" class="form-label">Categories</label>
                <select id="categories" name="categories[]" class="form-control" multiple required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ in_array($category->id, $coach->categories->pluck('id')->toArray()) ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Hold down the Ctrl (Windows) or Command (Mac) button to select multiple options.</small>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
        </form>

        <!-- Form untuk Approve -->
        <form action="{{ route('admin.coach.approve', $coach->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('PUT')
            <button type="submit" class="btn btn-success mt-3">Approve</button>
        </form>

        <!-- Form untuk Reject -->
        <form action="{{ route('admin.coach.reject', $coach->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('PUT')
            <button type="submit" class="btn btn-danger mt-3">Reject</button>
        </form>
    </div>
</x-appadmin-layout>
