@props(['value' => 0, 'late' => false])
@php($nilai = max(0, min(100, (int) $value)))
<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    <div class="h-1.5 w-full min-w-16 overflow-hidden rounded-full bg-slate-200"
         role="progressbar" aria-valuenow="{{ $nilai }}" aria-valuemin="0" aria-valuemax="100">
        <div class="h-full rounded-full {{ $late ? 'bg-red-500' : 'bg-indigo-600' }}" style="width: {{ $nilai }}%"></div>
    </div>
    <span class="w-10 shrink-0 text-right text-xs tabular-nums text-slate-600">{{ $nilai }}%</span>
</div>