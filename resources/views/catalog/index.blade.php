@auth
    <x-layouts.app>
        <x-slot:title>Subject Catalog - UEP LMS</x-slot:title>

        <x-page-header title="Subject Catalog" subtitle="Browse published subjects and filter by topic or level." />

        @include('catalog.partials.grid')
    </x-layouts.app>
@else
    <x-layouts.guest>
        <x-slot:title>Subject Catalog - UEP LMS</x-slot:title>

        <x-public-header />

        <div class="mx-auto w-full max-w-6xl px-4 pb-20 pt-10 sm:px-6 lg:px-8">
            <x-page-header title="Subject Catalog" subtitle="Browse published subjects and filter by topic or level." />

            @include('catalog.partials.grid')
        </div>

        <x-public-footer />
    </x-layouts.guest>
@endauth
