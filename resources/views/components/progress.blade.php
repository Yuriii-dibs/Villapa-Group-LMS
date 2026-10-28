@props(['value' => 0, 'label' => true])
@php $pct = max(0, min(100, (int) $value)); @endphp
<div {{ $attributes }}>
    @if ($label)
        <div class="mb-1.5 flex justify-between text-xs text-gray-500">
            <span>Progress</span><span class="font-medium text-navy">{{ $pct }}%</span>
        </div>
    @endif
    <div class="h-1.5 w-full overflow-hidden rounded-full bg-line" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
        <div class="h-full rounded-full bg-indigo-600 transition-all duration-700 ease-out" style="width: {{ $pct }}%"></div>
    </div>
</div>
