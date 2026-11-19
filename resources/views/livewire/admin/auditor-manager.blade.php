<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-brand-900">Akun auditor</h1>
            <p class="mt-1 text-sm text-slate-600">Buat, ubah, nonaktifkan, atau hapus akun login petugas auditor.</p>
        </div>
        <button type="button" wire:click="create" class="btn btn-primary">
            <x-icon name="user-plus" class="size-4" />
            Tambah akun auditor
        </button>
    </div>

    <div class="card mt-6">
        <div class="border-b border-slate-100 p-4">
            <div class="relative w-full sm:max-w-sm">
                <x-icon name="magnifying-glass" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-500" />
                <label for="search-auditor" class="sr-only">Cari auditor</label>
                <input id="search-auditor" type="search" wire:model.live.debounce.350ms="search" class="field pl-9" placeholder="Cari nama, username, atau email">
            </div>
        </div>

        @if ($auditors->isEmpty())
            <x-empty-state icon="users" :title="$search !== '' ? 'Tidak ada auditor yang cocok.' : 'Belum ada akun auditor.'" />
        @else
            <div class="relative overflow-x-auto">
                <table class="data-table min-w-[760px]">
                    <thead>
                        <tr>
                            <th class="w-12 text-center">No</th>
                            <th>Nama lengkap</th>
                            <th>Username / email login</th>
                            <th>Tim / departemen</th>
                            <th class="text-center">Audit</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($auditors as $auditor)
                            <tr wire:key="auditor-{{ $auditor->id }}">
                                <td class="text-center font-bold text-slate-600">{{ $auditors->firstItem() + $loop->index }}</td>
                                <td class="font-bold text-slate-900">{{ $auditor->name }}</td>
                                <td>
                                    <p class="font-medium">{{ $auditor->username }}</p>
                                    <p class="text-xs text-slate-600">{{ $auditor->email ?: 'tanpa email' }}</p>
                                </td>
                                <td class="text-slate-700">{{ $auditor->team ?: '-' }}</td>
                                <td class="text-center tabular-nums">{{ $auditor->audits_count }}</td>
                                <td>
                                    {{-- A real switch: this cell toggles login access, so it should look like a control, not a label. --}}
                                    <button type="button" role="switch" aria-checked="{{ $auditor->is_active ? 'true' : 'false' }}"
                                            wire:click="toggleActive({{ $auditor->id }})"
                                            aria-label="Akses login {{ $auditor->name }}"
                                            class="group mx-auto flex min-h-9 items-center gap-2.5 rounded-md px-1 text-sm font-semibold">
                                        <span @class([
                                            'relative h-5 w-9 shrink-0 rounded-[5px] transition-colors duration-200',
                                            'bg-emerald-600 group-hover:bg-emerald-700' => $auditor->is_active,
                                            'bg-slate-300 group-hover:bg-slate-400' => ! $auditor->is_active,
                                        ])>
                                            <span @class([
                                                'absolute top-0.5 left-0.5 size-4 rounded-[3px] bg-white shadow-sm transition-transform duration-200 ease-out-soft',
                                                'translate-x-4' => $auditor->is_active,
                                            ])></span>
                                        </span>
                                        <span @class(['w-16 text-left', 'text-emerald-800' => $auditor->is_active, 'text-slate-600' => ! $auditor->is_active])>{{ $auditor->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </button>
                                </td>
                                <td>
                                    <div class="flex justify-end gap-1">
                                        <button type="button" wire:click="edit({{ $auditor->id }})" class="btn btn-sm btn-soft"><x-icon name="pencil-simple" class="size-4" />Edit</button>
                                        <button type="button" wire:click="delete({{ $auditor->id }})"
                                                wire:confirm="Hapus akun {{ $auditor->name }}? {{ $auditor->audits_count }} audit miliknya tetap tersimpan tanpa pemilik dan hanya bisa dibuka admin."
                                                class="btn btn-sm btn-icon btn-danger-ghost" aria-label="Hapus akun {{ $auditor->name }}">
                                            <x-icon name="trash" class="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($auditors->hasPages())
                <div class="border-t border-slate-100 p-4">{{ $auditors->links() }}</div>
            @endif
        @endif
    </div>

    <x-modal show="showForm" :title="$editingId ? 'Edit akun auditor' : 'Tambah akun auditor'" icon="user-circle">
        <form wire:submit="save" id="auditor-form" class="space-y-4">
            <div>
                <label for="a-name" class="field-label">Nama lengkap <span class="text-red-600">*</span></label>
                <input id="a-name" type="text" wire:model="name" class="field" placeholder="contoh: Ziban Robani" autocomplete="off">
                @error('name') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="a-username" class="field-label">Username login <span class="text-red-600">*</span></label>
                    <input id="a-username" type="text" wire:model="username" class="field" placeholder="contoh: ziban" autocomplete="off">
                    @error('username') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="a-email" class="field-label">Email</label>
                    <input id="a-email" type="email" wire:model="email" class="field" placeholder="nama@perusahaan.co.id" autocomplete="off">
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label for="a-password" class="field-label">Password @unless ($editingId) <span class="text-red-600">*</span> @endunless</label>
                <input id="a-password" type="password" wire:model="password" class="field" autocomplete="new-password"
                       placeholder="{{ $editingId ? 'Kosongkan bila tidak diganti' : 'Minimal 8 karakter' }}">
                @error('password') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="a-team" class="field-label">Tim / departemen</label>
                    <input id="a-team" type="text" wire:model="team" class="field" placeholder="Tim Auditor Internal">
                    @error('team') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <span class="field-label">Status akun</span>
                    <label class="flex min-h-11 cursor-pointer items-center gap-2.5 text-sm font-medium text-slate-700">
                        <input type="checkbox" wire:model="isActive" class="size-4 rounded border-slate-300 text-brand-900 focus:ring-brand-600">
                        Aktif, dapat login
                    </label>
                </div>
            </div>
        </form>
        <x-slot:footer>
            <button type="button" x-on:click="$wire.showForm = false" class="btn btn-secondary">Batal</button>
            <button type="submit" form="auditor-form" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                <x-icon name="floppy-disk" class="size-4" />
                Simpan akun
            </button>
        </x-slot:footer>
    </x-modal>
</div>
