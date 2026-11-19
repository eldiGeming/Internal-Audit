{{-- Department switcher: underlined tabs on wide screens, a select on phones. Expects $departmentOptions. --}}
<div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:gap-1 sm:border-b sm:border-slate-300">
    <div class="sm:hidden">
        <label for="dept-picker" class="field-label">Departemen</label>
        <select id="dept-picker" wire:model.live="departmentId" class="field font-semibold">
            @foreach ($departmentOptions as $option)
                <option value="{{ $option->id }}">{{ $loop->iteration }}. {{ $option->displayName() }}</option>
            @endforeach
        </select>
    </div>
    <div class="hidden min-w-0 flex-wrap items-end gap-x-1 sm:flex" role="tablist" aria-label="Pilih departemen">
        @foreach ($departmentOptions as $option)
            @php $isActive = $option->id === $departmentId; @endphp
            <button type="button" wire:click="selectDepartment({{ $option->id }})" wire:key="dept-tab-{{ $option->id }}"
                    role="tab" aria-selected="{{ $isActive ? 'true' : 'false' }}"
                    @class([
                        '-mb-px inline-flex min-h-11 items-center gap-2 border-b-[3px] px-3 text-sm whitespace-nowrap transition-colors',
                        'border-brand-900 font-bold text-brand-900' => $isActive,
                        'border-transparent font-semibold text-slate-600 hover:border-slate-300 hover:text-brand-900' => ! $isActive,
                    ])>
                <span @class(['text-xs tabular-nums', 'text-brand-700' => $isActive, 'text-slate-500' => ! $isActive])>{{ $loop->iteration }}</span>
                {{ $option->displayName() }}
            </button>
        @endforeach
    </div>
    <button type="button" wire:click="openDepartmentModal" class="btn btn-sm btn-ghost self-start text-brand-800 sm:mb-1 sm:self-end">
        <x-icon name="plus" class="size-4" />
        Departemen baru
    </button>
</div>
