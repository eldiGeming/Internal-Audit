{{-- Department switcher: pills on wide screens, a select on phones. Expects $departmentOptions. --}}
<div class="flex flex-col gap-3 sm:flex-row sm:items-center">
    <div class="sm:hidden">
        <label for="dept-picker" class="field-label">Departemen</label>
        <select id="dept-picker" wire:model.live="departmentId" class="field font-semibold">
            @foreach ($departmentOptions as $option)
                <option value="{{ $option->id }}">{{ $loop->iteration }}. {{ $option->displayName() }}</option>
            @endforeach
        </select>
    </div>
    <div class="hidden flex-wrap gap-2 sm:flex" role="tablist" aria-label="Pilih departemen">
        @foreach ($departmentOptions as $option)
            @php $isActive = $option->id === $departmentId; @endphp
            <button type="button" wire:click="selectDepartment({{ $option->id }})" wire:key="pill-{{ $option->id }}"
                    role="tab" aria-selected="{{ $isActive ? 'true' : 'false' }}"
                    @class([
                        'btn btn-sm',
                        'btn-primary' => $isActive,
                        'btn-secondary' => ! $isActive,
                    ])>
                <x-icon name="buildings" class="size-4" />
                {{ $option->displayName() }}
            </button>
        @endforeach
    </div>
    <button type="button" wire:click="openDepartmentModal" class="btn btn-sm btn-soft">
        <x-icon name="plus" class="size-4" />
        Departemen baru
    </button>
</div>
