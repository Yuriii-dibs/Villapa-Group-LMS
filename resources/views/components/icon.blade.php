@props(['name', 'class' => 'h-5 w-5'])
@php
    $paths = [
        'home'     => 'M3 10.5 12 3l9 7.5M5 9.5V20h5v-5h4v5h5V9.5',
        'book'     => 'M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5v-15ZM4 20.5A2.5 2.5 0 0 0 6.5 23H20',
        'compass'  => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18ZM15.5 8.5l-2 5-5 2 2-5 5-2Z',
        'chat'     => 'M4 5h16v11H9l-5 4V5Z',
        'calendar' => 'M7 3v3M17 3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z',
        'video'    => 'M3 7h11v10H3V7ZM14 11l7-4v10l-7-4',
        'users'    => 'M9 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 6.5M18 14.2A6.5 6.5 0 0 1 21.5 20',
        'check'    => 'M5 12.5l4.5 4.5L19 7.5',
        'clock'    => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18ZM12 7v5l3 2',
        'tag'      => 'M3 12V4h8l10 10-8 8L3 12ZM7.5 8.5h.01',
        'inbox'    => 'M3 13l3-8h12l3 8M3 13v6h18v-6M3 13h5l1 2h6l1-2h5',
        'user'     => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4 21a8 8 0 0 1 16 0',
        'menu'     => 'M4 7h16M4 12h16M4 17h16',
        'close'    => 'M6 6l12 12M18 6 6 18',
        'logout'   => 'M15 4h4a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-4M10 16l-4-4 4-4M6 12h10',
        'plus'     => 'M12 5v14M5 12h14',
        'search'   => 'M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14ZM20 20l-4-4',
        'arrow'    => 'M5 12h14M13 6l6 6-6 6',
        'folder'   => 'M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-6l-2-2H5a2 2 0 0 0-2 2Z',
        'flag'     => 'M3 21v-4m0 0V5a2 2 0 0 1 2-2h6.5l1 1H19a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-6.5l-1-1H5a2 2 0 0 0-2 2Z',
        'document' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z',
        'edit'     => 'M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5Z',
        'key'      => 'M15 7a4 4 0 1 0-5.657 5.657l-6.343 6.343H6v-3h3v-3h3l.657-.657A4 4 0 0 0 15 7Z',
        'trash'    => 'M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6',
    ];
@endphp
<svg {{ $attributes->merge(['class' => $class]) }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <path d="{{ $paths[$name] ?? $paths['book'] }}" />
</svg>
