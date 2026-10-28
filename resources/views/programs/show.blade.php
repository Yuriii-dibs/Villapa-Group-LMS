@auth
    <x-layouts.app>
        <x-slot:title>{{ $program->title }} – UEP LMS</x-slot:title>
        @include('programs.partials.show-content')
    </x-layouts.app>
@else
    <x-layouts.guest>
        <x-slot:title>{{ $program->title }} – UEP LMS</x-slot:title>
        <x-public-header />
        <div class="mx-auto w-full max-w-6xl px-4 pb-20 pt-10 sm:px-6 lg:px-8">
            @include('programs.partials.show-content')
        </div>
        <x-public-footer />
    </x-layouts.guest>
@endauth
