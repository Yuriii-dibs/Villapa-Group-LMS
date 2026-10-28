@props(['label' => null, 'name', 'type' => 'text', 'value' => null, 'hint' => null])
<div>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-navy">{{ $label }}</label>
    @endif
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
           @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
           {{ $attributes->merge(['class' => 'mt-1.5 block w-full rounded-lg border border-line bg-paper px-3 py-2.5 text-sm text-navy placeholder:text-gray-400 transition-colors duration-150 ease-in-out focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600']) }}>
    @if ($hint)
        <p class="mt-1 text-xs text-gray-400">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
    @enderror
</div>
