<x-appadmin-layout>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add New Class</h1>
        <a href="{{ route('admin.kelas') }}" class="btn btn-secondary">Back to Manage Class</a>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('admin.classes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="image">Class Image</label>
                            <input type="file" id="image" name="image" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="name">Class Name</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" required></textarea>
                        </div>
                        <!--hari-->
                        <div class="form-group">
                            <label for="day_of_week">Day of the Week</label>
                            <select name="day_of_week" class="form-control" required>Senin
                                <option value="">Select Day</option>
                                <option value="Senin">Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu">Sabtu</option>
                                <option value="Minggu">Minggu</option>
                            </select>
                            
                        </div>
                        <div class="form-group">
                            <label for="time">Time</label>
                            <input type="time" name="time" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="price">Price</label>
                            <input type="number" id="price" name="price" class="form-control" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="quota">Quota</label>
                            <input type="number" name="quota" id="quota" class="form-control" value="{{ old('quota', $class->quota ?? '') }}" required>
                        </div>                                                
                        <div class="form-group">
                            <label for="coach_id">Coach</label>
                            <select id="coach_id" name="coach_id" class="form-control" required>
                                @foreach ($coaches as $coach)
                                    <option value="{{ $coach->id }}">{{ $coach->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-appadmin-layout>
