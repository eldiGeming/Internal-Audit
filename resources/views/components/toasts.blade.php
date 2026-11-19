{{--
    Toast stack. Livewire components call $this->dispatch('toast', message: '...', type: 'success|error|info').
    Redirects can flash session('toast') with the same shape.
--}}
<div
    x-data="{
        toasts: [],
        add(detail) {
            const toast = { id: Date.now() + Math.random(), type: detail.type ?? 'success', message: detail.message ?? '' };
            this.toasts.push(toast);
            setTimeout(() => this.remove(toast.id), 3800);
        },
        remove(id) { this.toasts = this.toasts.filter((t) => t.id !== id); },
    }"
    x-init="@if (session('toast')) add(@js(session('toast'))) @endif"
    x-on:toast.window="add($event.detail)"
    class="pointer-events-none fixed inset-x-4 bottom-4 z-[70] flex flex-col items-end gap-2 sm:inset-x-auto sm:right-6 sm:bottom-6"
    aria-live="polite"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-transition:enter="transition duration-300 ease-out-soft"
            x-transition:enter-start="translate-y-2 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition duration-200"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl bg-brand-950 px-4 py-3 text-sm text-white shadow-lg shadow-brand-950/20 ring-1 ring-white/10"
            role="status"
        >
            <span class="mt-0.5" :class="{ 'text-emerald-400': toast.type === 'success', 'text-red-400': toast.type === 'error', 'text-brand-300': toast.type === 'info' }">
                <template x-if="toast.type === 'error'"><x-icon name="warning-octagon" class="size-5" /></template>
                <template x-if="toast.type !== 'error'"><x-icon name="check-circle" class="size-5" /></template>
            </span>
            <p class="flex-1 font-medium" x-text="toast.message"></p>
            <button type="button" x-on:click="remove(toast.id)" class="-m-1 rounded p-1 text-white/60 hover:text-white" aria-label="Tutup notifikasi">
                <x-icon name="x" class="size-4" />
            </button>
        </div>
    </template>
</div>
