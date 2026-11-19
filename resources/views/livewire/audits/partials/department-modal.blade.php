<x-modal show="showDepartmentModal" title="Departemen baru" icon="buildings">
    <form wire:submit="createDepartment" id="department-form" class="space-y-4">
        <div>
            <label for="new-dept-name" class="field-label">Nama departemen <span class="text-red-600">*</span></label>
            <input id="new-dept-name" type="text" wire:model="newDepartmentName" class="field" placeholder="contoh: Logistik, HRD, Purchasing">
            @error('newDepartmentName') <p class="field-error">{{ $message }}</p> @enderror
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="new-dept-auditee" class="field-label">Auditee</label>
                <input id="new-dept-auditee" type="text" wire:model="newDepartmentAuditee" class="field" placeholder="Penanggung jawab">
                @error('newDepartmentAuditee') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="new-dept-auditor" class="field-label">Auditor</label>
                <input id="new-dept-auditor" type="text" wire:model="newDepartmentAuditor" class="field" placeholder="Nama auditor">
                @error('newDepartmentAuditor') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>
        <p class="field-hint">Departemen juga muncul di tabel Identitas Audit dan baris Rekap.</p>
    </form>
    <x-slot:footer>
        <button type="button" x-on:click="$wire.showDepartmentModal = false" class="btn btn-secondary">Batal</button>
        <button type="submit" form="department-form" class="btn btn-primary" wire:loading.attr="disabled" wire:target="createDepartment">
            <x-icon name="plus" class="size-4" />
            Buat departemen
        </button>
    </x-slot:footer>
</x-modal>
