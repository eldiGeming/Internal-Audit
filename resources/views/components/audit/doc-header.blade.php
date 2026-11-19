@props(['company', 'title'])

{{-- Kop dokumen: formal header used on the printable sheets (mirrors the PDF layout). --}}
<div class="flex flex-col-reverse gap-4 border-b-2 border-brand-900 pb-5 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0">
        <h2 class="text-xl font-extrabold tracking-wide text-brand-900 uppercase sm:text-2xl">{{ $title }}</h2>
        <div class="mt-1.5 space-y-0.5 text-sm text-slate-700">{{ $slot }}</div>
    </div>
    <div class="grid h-16 w-32 shrink-0 place-items-center rounded-lg bg-slate-50 p-1.5 ring-1 ring-slate-200">
        @if ($company->logoUrl())
            <img src="{{ $company->logoUrl() }}" alt="Logo {{ $company->name }}" class="max-h-full max-w-full object-contain" draggable="false">
        @else
            <span class="text-xs text-slate-500 italic">Logo client</span>
        @endif
    </div>
</div>
