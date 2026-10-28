<x-layouts.app>
    <x-slot:title>Edit Category - Administrator</x-slot:title>

    <div class="max-w-xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Edit Category</h1>
            <a href="{{ route('admin.categories.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                Cancel
            </a>
        </div>

        @if ($errors->any())
            <div class="p-4 bg-red-50 border-l-4 border-red-400 text-red-700 text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="bg-white shadow rounded-lg p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Category Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                <textarea name="description" id="description" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-500 @enderror">{{ old('description', $category->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                    Update Category
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
