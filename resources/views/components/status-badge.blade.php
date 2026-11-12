@props(['status'])
@php
    $gaya = [
        'perencanaan' => ['bg-slate-100 text-slate-700 ring-slate-500/20', 'bg-slate-500'],
        'berjalan' => ['bg-blue-50 text-blue-700 ring-blue-600/20', 'bg-blue-600'],
        'selesai' => ['bg-green-50 text-green-700 ring-green-600/20', 'bg-green-600'],
        'ditunda' => ['bg-amber-50 text-amber-800 ring-amber-600/20', 'bg-amber-500'],
        'terlambat' => ['bg-red-50 text-red-700 ring-red-600/20', 'bg-red-600'],
    ];
    [$badan, $titik] = $gaya[$status] ?? $gaya['perencanaan'];
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {$badan}"]) }}>
    <span class="h-1.5 w-1.5 rounded-full {{ $titik }}"></span>{{ ucfirst($status) }}
</span>