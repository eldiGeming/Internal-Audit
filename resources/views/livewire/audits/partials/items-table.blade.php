{{--
    Checklist items of one department. Shared by step 2 (Checklist) and step 3 (Daftar Periksa).
    Expects: $department (with items), $withCriteria (bool). Binds to $items[id][field] on the component.
--}}
@php
    $columns = $withCriteria
        ? 'md:grid-cols-[3.25rem_minmax(0,2fr)_minmax(0,1fr)_minmax(0,1.5fr)_12.5rem]'
        : 'md:grid-cols-[3.25rem_minmax(0,2fr)_minmax(0,1.6fr)_12.5rem]';
    $criteriaFallback = $department->criteriaText();
@endphp

<div class="overflow-hidden rounded-xl ring-1 ring-slate-200">
    <div class="hidden gap-3 bg-slate-50 px-4 py-3 text-xs font-bold tracking-wide text-brand-900 uppercase md:grid {{ $columns }}">
        <span class="text-center">No</span>
        <span>Pertanyaan / poin pemeriksaan</span>
        @if ($withCriteria) <span>Criteria</span> @endif
        <span>Catatan / bukti objektif</span>
        <span class="text-center">Hasil pemeriksaan</span>
    </div>

    @if ($department->items->isEmpty())
        <x-empty-state icon="list-checks" title="Belum ada item pemeriksaan" class="border-t border-slate-100 bg-white">
            Tambahkan item lalu tulis pertanyaannya, atau impor dari file template.
            <x-slot:actions>
                <button type="button" wire:click="addItems(1)" class="btn btn-add">
                    <x-icon name="plus" class="size-4" />
                    Tambah item
                </button>
            </x-slot:actions>
        </x-empty-state>
    @endif

    <ol class="divide-y divide-slate-100 bg-white">
        @foreach ($department->items as $item)
            @php
                $state = $items[$item->id] ?? null;
                $result = $state['result'] ?? '';
            @endphp
            @continue($state === null)
            <li wire:key="item-{{ $item->id }}" class="grid grid-cols-1 gap-3 px-4 py-4 md:items-start {{ $columns }}">
                <div class="flex items-center justify-between md:flex-col md:justify-start md:gap-1 md:pt-2">
                    <span class="text-sm font-bold text-slate-700"><span class="md:hidden">Item </span>{{ $loop->iteration }}</span>
                    <button type="button" wire:click="removeItem({{ $item->id }})"
                            @if ($item->correctiveAction ?? false) wire:confirm="Item ini memiliki dokumen CAR. Hapus item beserta CAR-nya?" @endif
                            class="btn btn-icon btn-danger-ghost" aria-label="Hapus item {{ $loop->iteration }}">
                        <x-icon name="trash" class="size-4" />
                    </button>
                </div>

                <div>
                    <label for="q-{{ $item->id }}" class="field-label md:sr-only">Pertanyaan / poin pemeriksaan</label>
                    <textarea id="q-{{ $item->id }}" rows="2" wire:model.live.blur="items.{{ $item->id }}.question" class="field" placeholder="Tulis pertanyaan atau poin pemeriksaan"></textarea>
                    @error("items.{$item->id}.question") <p class="field-error">{{ $message }}</p> @enderror
                </div>

                @if ($withCriteria)
                    <div>
                        <label for="c-{{ $item->id }}" class="field-label md:sr-only">Criteria</label>
                        <input id="c-{{ $item->id }}" type="text" wire:model.live.blur="items.{{ $item->id }}.criteria" class="field"
                               placeholder="{{ $criteriaFallback !== '' ? Str::limit($criteriaFallback, 40) : 'Criteria item' }}">
                        @error("items.{$item->id}.criteria") <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div>
                    <label for="e-{{ $item->id }}" class="field-label md:sr-only">Catatan / bukti objektif</label>
                    <textarea id="e-{{ $item->id }}" rows="2" wire:model.live.blur="items.{{ $item->id }}.evidence" class="field" placeholder="Catatan atau bukti objektif"></textarea>
                    @error("items.{$item->id}.evidence") <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div @class([
                    'space-y-2 rounded-lg p-2 ring-1 ring-inset transition-colors',
                    'bg-emerald-50 ring-emerald-200' => $result === 'sesuai',
                    'bg-red-50 ring-red-200' => $result === 'tidak_sesuai',
                    'bg-slate-50 ring-slate-200' => $result === '',
                ])>
                    <label for="r-{{ $item->id }}" class="field-label mb-0 md:sr-only">Hasil pemeriksaan</label>
                    <select id="r-{{ $item->id }}" wire:model.live="items.{{ $item->id }}.result"
                            @class(['field font-semibold', 'text-emerald-800' => $result === 'sesuai', 'text-red-800' => $result === 'tidak_sesuai'])>
                        <option value="">Pilih hasil</option>
                        <option value="sesuai">Sesuai</option>
                        <option value="tidak_sesuai">Tidak sesuai</option>
                    </select>
                    @if ($result === 'tidak_sesuai')
                        <label for="s-{{ $item->id }}" class="sr-only">Kategori temuan</label>
                        <select id="s-{{ $item->id }}" wire:model.live="items.{{ $item->id }}.severity" class="field text-red-900">
                            <option value="major">1. Major</option>
                            <option value="minor">2. Minor</option>
                            <option value="observasi">3. Observasi</option>
                        </select>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</div>
