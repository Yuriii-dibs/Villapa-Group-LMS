<x-layouts.app>
    <x-slot:title>Categories - Administrator</x-slot:title>

    <div class="space-y-6">
        @if (session('status'))
            <div class="p-4 bg-green-50 border-l-4 border-green-400 text-green-700 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-red-50 border-l-4 border-red-400 text-red-700 text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="flex justify-between items-center bg-white shadow rounded-lg p-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Course Categories</h1>
                <p class="text-sm text-gray-500 mt-1">Manage institutional topics and course classifications.</p>
            </div>
            <a href="{{ route('admin.categories.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                + Create Category
            </a>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase text-gray-500">
                    <tr>
                        <th class="px-6 py-3 text-left">Category Name</th>
                        <th class="px-6 py-3 text-left">Slug</th>
                        <th class="px-6 py-3 text-left">Associated Courses</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($categories as $cat)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                {{ $cat->name }}
                            </td>
                            <td class="px-6 py-4 text-gray-500 font-mono text-xs">
                                {{ $cat->slug }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $cat->courses_count > 0 ? 'bg-indigo-50 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $cat->courses_count }} {{ Str::plural('course', $cat->courses_count) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-3">
                                <a href="{{ route('admin.categories.edit', $cat) }}" class="text-indigo-600 hover:text-indigo-900 text-xs font-semibold">
                                    Edit
                                </a>
                                <x-confirm-delete-modal
                                    :action="route('admin.categories.destroy', $cat)"
                                    title="Delete Category"
                                    :message="'Are you sure you want to delete category [' . $cat->name . ']? If courses are assigned to this category, deletion will be safely rejected.'"
                                    buttonText="Delete"
                                    buttonClass="text-red-600 hover:text-red-800 text-xs font-semibold transition"
                                />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4 border-t border-gray-200">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
