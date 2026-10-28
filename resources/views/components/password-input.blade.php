@props(['label' => 'Password', 'name' => 'password', 'autocomplete' => 'current-password'])
<div x-data="{ show: false }">
    <label for="{{ $name }}" class="block text-sm font-medium text-navy">{{ $label }}</label>
    <div class="relative mt-1.5">
        <input :type="show ? 'text' : 'password'" id="{{ $name }}" name="{{ $name }}" required autocomplete="{{ $autocomplete }}"
               class="block w-full rounded-lg border border-line bg-paper px-3 py-2.5 pr-16 text-sm text-navy transition-colors duration-150 ease-in-out focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 px-3 text-xs font-medium text-indigo-600 transition-colors duration-150 hover:text-navy"
                x-text="show ? 'Hide' : 'Show'">Show</button>
    </div>
    @error($name)
        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
    @enderror
</div>
