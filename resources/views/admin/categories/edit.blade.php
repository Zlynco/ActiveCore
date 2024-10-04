<x-appadmin-layout>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Category</h1>
        <a href="{{ route('admin.category') }}" class="btn btn-secondary">Back to List</a>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
                        @csrf
                        @method('PUT') <!-- Spoofing method PUT -->
                    
                        <div class="form-group">
                            <label for="name">Category Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                        </div>
                    
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control">{{ $category->description }}</textarea>
                        </div>
                    
                        <button type="submit" class="btn btn-primary">Update Category</button>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</x-appadmin-layout>
