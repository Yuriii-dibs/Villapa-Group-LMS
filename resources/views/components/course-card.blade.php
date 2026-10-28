@props(['course'])
<x-card padding="p-0" class="group flex flex-col justify-between overflow-hidden transition-all duration-300 hover:-translate-y-1.5 hover:shadow-[0_16px_36px_-12px_rgba(10,25,47,0.12)] hover:border-gold/50">
    <div>
        @if ($course->thumbnail_path)
            <div class="overflow-hidden">
                <img src="{{ asset('storage/' . $course->thumbnail_path) }}" alt="{{ $course->title }}" class="h-40 w-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
            </div>
        @else
            <div class="relative h-40 w-full overflow-hidden bg-gradient-to-br from-[#071321] via-[#0A192F] to-[#14365D]">
                <img src="{{ asset('images/waves/wave-panel.svg') }}" alt="" class="absolute inset-x-0 bottom-0 h-full w-full transition-transform duration-500 ease-out group-hover:scale-105 opacity-85">
                <span class="absolute left-4 top-4 font-serif text-xl font-semibold leading-snug text-paper transition-transform duration-300 group-hover:translate-x-1">{{ Str::limit($course->title, 34) }}</span>
            </div>
        @endif
        <div class="p-5">
            <div class="mb-3 flex items-center justify-between gap-2">
                <x-badge tone="brand">{{ $course->category->name }}</x-badge>
            </div>
            <h3 class="font-serif text-lg font-semibold leading-snug text-navy">
                <a href="{{ route('courses.show', $course) }}" class="transition-colors duration-150 group-hover:text-indigo-600">{{ $course->title }}</a>
            </h3>
            <p class="mt-3 line-clamp-2 text-sm text-gray-600">{{ Str::limit($course->description, 100) }}</p>
        </div>
    </div>
    <div class="flex items-center justify-end border-t border-line bg-ivory px-5 py-3 text-xs text-gray-500">
        <a href="{{ route('courses.show', $course) }}" class="font-medium text-indigo-600 transition-colors duration-150 hover:text-navy">View subject &rarr;</a>
    </div>
</x-card>
