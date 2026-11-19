@props(['stats'])

{{--
    One ruled surface for a row of figures, in place of a separate card (and icon) per number.
    Each stat: label, value, optional tone (text class used only when the value is not zero),
    optional hint, optional severity (renders the severity marker as the label).
    Pass the wide-screen column count as a class, e.g. class="lg:grid-cols-4".
--}}
<dl {{ $attributes->class(['grid grid-cols-2 gap-px overflow-hidden rounded-xl bg-slate-200 ring-1 ring-slate-200']) }}>
    @foreach ($stats as $stat)
        @php $isZero = $stat['value'] === 0 || $stat['value'] === '0'; @endphp
        <div @class(['bg-white px-4 py-3.5 sm:px-5', 'col-span-2 sm:col-span-1' => $loop->first && count($stats) % 2 === 1])>
            <dt class="text-sm font-semibold text-slate-600">
                @isset($stat['severity'])
                    <x-severity :severity="$stat['severity']" large />
                @else
                    {{ $stat['label'] }}
                @endisset
            </dt>
            <dd @class([
                'mt-1 text-3xl leading-none font-extrabold',
                'text-slate-500' => $isZero,
                $stat['tone'] ?? 'text-brand-900' => ! $isZero,
            ])>{{ $stat['value'] }}</dd>
            @isset($stat['hint'])
                <dd class="mt-1.5 text-xs text-slate-600">{{ $stat['hint'] }}</dd>
            @endisset
        </div>
    @endforeach
</dl>
