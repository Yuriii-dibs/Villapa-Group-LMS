@props(['count' => 22])
<div class="pointer-events-none fixed inset-0 z-20 overflow-hidden select-none" aria-hidden="true">
    @php
        // Pre-calculated deterministic values for stable, jitter-free rendering
        $petals = [
            ['left' => '3%',  'size' => 18, 'dur' => 11.5, 'delay' => 0.0, 'sway' => 4.2, 'opacity' => 0.85],
            ['left' => '8%',  'size' => 14, 'dur' => 14.0, 'delay' => 3.2, 'sway' => 5.1, 'opacity' => 0.70],
            ['left' => '14%', 'size' => 20, 'dur' => 10.2, 'delay' => 6.5, 'sway' => 3.8, 'opacity' => 0.90],
            ['left' => '19%', 'size' => 13, 'dur' => 15.5, 'delay' => 1.8, 'sway' => 4.7, 'opacity' => 0.65],
            ['left' => '25%', 'size' => 16, 'dur' => 12.8, 'delay' => 8.1, 'sway' => 4.0, 'opacity' => 0.80],
            ['left' => '31%', 'size' => 22, 'dur' => 9.8,  'delay' => 4.5, 'sway' => 3.6, 'opacity' => 0.88],
            ['left' => '37%', 'size' => 15, 'dur' => 13.4, 'delay' => 2.2, 'sway' => 4.9, 'opacity' => 0.75],
            ['left' => '43%', 'size' => 17, 'dur' => 11.0, 'delay' => 7.0, 'sway' => 4.1, 'opacity' => 0.82],
            ['left' => '49%', 'size' => 12, 'dur' => 16.0, 'delay' => 0.8, 'sway' => 5.3, 'opacity' => 0.60],
            ['left' => '55%', 'size' => 19, 'dur' => 10.6, 'delay' => 5.2, 'sway' => 3.9, 'opacity' => 0.85],
            ['left' => '61%', 'size' => 14, 'dur' => 14.5, 'delay' => 9.4, 'sway' => 4.6, 'opacity' => 0.72],
            ['left' => '67%', 'size' => 21, 'dur' => 10.0, 'delay' => 3.8, 'sway' => 3.7, 'opacity' => 0.90],
            ['left' => '73%', 'size' => 15, 'dur' => 13.0, 'delay' => 1.2, 'sway' => 4.4, 'opacity' => 0.78],
            ['left' => '79%', 'size' => 18, 'dur' => 11.8, 'delay' => 7.8, 'sway' => 4.3, 'opacity' => 0.84],
            ['left' => '85%', 'size' => 13, 'dur' => 15.0, 'delay' => 4.0, 'sway' => 5.0, 'opacity' => 0.68],
            ['left' => '91%', 'size' => 20, 'dur' => 10.4, 'delay' => 8.7, 'sway' => 3.8, 'opacity' => 0.88],
            ['left' => '96%', 'size' => 16, 'dur' => 12.2, 'delay' => 2.6, 'sway' => 4.5, 'opacity' => 0.75],
            ['left' => '5%',  'size' => 15, 'dur' => 13.6, 'delay' => 10.1,'sway' => 4.8, 'opacity' => 0.70],
            ['left' => '22%', 'size' => 17, 'dur' => 11.2, 'delay' => 11.4,'sway' => 4.2, 'opacity' => 0.82],
            ['left' => '51%', 'size' => 21, 'dur' => 10.8, 'delay' => 12.0,'sway' => 3.9, 'opacity' => 0.86],
            ['left' => '76%', 'size' => 14, 'dur' => 14.2, 'delay' => 10.8,'sway' => 4.7, 'opacity' => 0.68],
            ['left' => '88%', 'size' => 19, 'dur' => 11.0, 'delay' => 11.8,'sway' => 4.0, 'opacity' => 0.84],
        ];
    @endphp

    @foreach ($petals as $i => $p)
        <div class="petal-particle"
             style="left: {{ $p['left'] }};
                    width: {{ $p['size'] }}px;
                    height: {{ (int) ($p['size'] * 1.25) }}px;
                    animation-duration: {{ $p['dur'] }}s, {{ $p['sway'] }}s;
                    animation-delay: -{{ $p['delay'] }}s, -{{ $p['delay'] }}s;
                    opacity: {{ $p['opacity'] }};">
            <svg viewBox="0 0 30 38" class="w-full h-full drop-shadow-sm filter" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="sakuraGrad-{{ $i }}" x1="10%" y1="0%" x2="90%" y2="100%">
                        <stop offset="0%" stop-color="#FFF6F8" stop-opacity="0.95"/>
                        <stop offset="45%" stop-color="#F9CAD4" stop-opacity="0.88"/>
                        <stop offset="85%" stop-color="#EDA2B2" stop-opacity="0.82"/>
                        <stop offset="100%" stop-color="#D97A8F" stop-opacity="0.75"/>
                    </linearGradient>
                </defs>
                <!-- Delicate curved sakura petal with subtle notch -->
                <path d="M15,1 C22,7 29,17 26,28 C23,35 17,37 15,33 C13,37 7,35 4,28 C1,17 8,7 15,1 Z"
                      fill="url(#sakuraGrad-{{ $i }})"/>
                <!-- Subtle translucent petal vein -->
                <path d="M15,5 Q15,22 15,31" stroke="#FDE4EB" stroke-width="0.7" stroke-linecap="round" opacity="0.6"/>
            </svg>
        </div>
    @endforeach
</div>
