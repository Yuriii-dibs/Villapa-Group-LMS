<form method="GET" action="{{ route('courses.index') }}" class="mb-8 grid grid-cols-1 gap-4 rounded-xl border border-line/80 bg-[#FFFEFA] shadow-[0_2px_14px_rgba(10,25,47,0.03)] p-6 md:grid-cols-4">
    <div>
        <label for="search" class="block text-[11px] font-medium uppercase tracking-[0.14em] text-gray-500">Search</label>
        <input type="text" name="search" id="search" value="{{ $currentSearch }}" placeholder="e.g. Laravel"
               class="mt-1.5 block w-full rounded-lg border border-line/90 bg-[#FAF8F2] px-3.5 py-2.5 text-sm focus:border-gold/70 focus:bg-[#FFFEFA] focus:ring-1 focus:ring-gold/70 transition">
    </div>
    <div>
        <label for="category" class="block text-[11px] font-medium uppercase tracking-[0.14em] text-gray-500">Category</label>
        <select name="category" id="category" class="mt-1.5 block w-full rounded-lg border border-line/90 bg-[#FAF8F2] px-3.5 py-2.5 text-sm focus:border-gold/70 focus:bg-[#FFFEFA] focus:ring-1 focus:ring-gold/70 transition">
            <option value="">All categories</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->slug }}" {{ $currentCategory === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="difficulty" class="block text-[11px] font-medium uppercase tracking-[0.14em] text-gray-500">Level</label>
        <select name="difficulty" id="difficulty" class="mt-1.5 block w-full rounded-lg border border-line/90 bg-[#FAF8F2] px-3.5 py-2.5 text-sm focus:border-gold/70 focus:bg-[#FFFEFA] focus:ring-1 focus:ring-gold/70 transition">
            <option value="">All levels</option>
            <option value="beginner" {{ $currentDifficulty === 'beginner' ? 'selected' : '' }}>Beginner</option>
            <option value="intermediate" {{ $currentDifficulty === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
            <option value="advanced" {{ $currentDifficulty === 'advanced' ? 'selected' : '' }}>Advanced</option>
        </select>
    </div>
    <div class="flex items-end gap-2">
        <x-button type="submit" class="flex-1">Filter</x-button>
        @if ($currentSearch || $currentCategory || $currentDifficulty)
            <x-button :href="route('courses.index')" variant="secondary">Reset</x-button>
        @endif
    </div>
</form>

@if ($courses->isEmpty())
    <x-empty-state title="No subjects match your search" text="Try different keywords or reset the filters.">
        <x-button :href="route('courses.index')">View all subjects</x-button>
    </x-empty-state>
@else
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($courses as $course)
            <x-course-card :course="$course" />
        @endforeach
    </div>
    <div class="mt-10">{{ $courses->links() }}</div>
@endif
