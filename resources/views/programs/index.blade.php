@auth
    <x-layouts.app>
        <x-slot:title>Program Catalog – UEP LMS</x-slot:title>

        <x-page-header title="Program Catalog" subtitle="Browse our degree programs and explore their courses." />

        @include('programs.partials.grid')
    </x-layouts.app>
@else
    <x-layouts.guest>
        <x-slot:title>Program Catalog – UEP LMS</x-slot:title>

        <x-public-header />

        <div class="mx-auto w-full max-w-6xl px-4 pb-20 pt-10 sm:px-6 lg:px-8">
            <x-page-header title="Program Catalog" subtitle="Browse our degree programs and explore their courses." />

            @include('programs.partials.grid')
        </div>

        <x-public-footer />
    </x-layouts.guest>
@endauth
