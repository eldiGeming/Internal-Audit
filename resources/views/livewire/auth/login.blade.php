<div x-data="{ showPassword: false, forgotOpen: false }" class="card p-6 shadow-xl shadow-brand-950/5 sm:p-9">
    <h2 class="text-2xl font-extrabold tracking-tight text-brand-900">Masuk ke sistem</h2>
    <p class="mt-1 text-sm text-slate-600">Gunakan akun yang diberikan administrator untuk membuka dashboard audit.</p>

    @if (session('status'))
        <div class="mt-5 flex items-start gap-2.5 rounded-lg bg-amber-50 p-3 text-sm font-medium text-amber-900 ring-1 ring-amber-200 ring-inset" role="alert">
            <x-icon name="warning" class="mt-0.5 size-4" />
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if ($showDemo)
        <div class="mt-6 rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200 ring-inset">
            <p class="text-xs font-semibold text-slate-600">Isi cepat akun demo (hanya mode lokal)</p>
            <div class="mt-2 grid grid-cols-2 gap-2">
                <button type="button" wire:click="fillDemo('admin')" class="btn btn-sm btn-secondary">
                    <x-icon name="shield-check" class="size-4 text-brand-700" />
                    Admin
                </button>
                <button type="button" wire:click="fillDemo('auditor')" class="btn btn-sm btn-secondary">
                    <x-icon name="user-circle" class="size-4 text-emerald-700" />
                    Auditor
                </button>
            </div>
        </div>
    @endif

    <form wire:submit="authenticate" class="mt-6 space-y-4" novalidate>
        <div>
            <label for="login" class="field-label">Username atau email <span class="text-red-600">*</span></label>
            <div class="relative">
                <x-icon name="user" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-500" />
                <input id="login" type="text" wire:model="login" autocomplete="username" autofocus
                       placeholder="contoh: auditor atau nama@perusahaan.co.id"
                       class="field pl-9" @error('login') aria-invalid="true" aria-describedby="login-error" @enderror>
            </div>
            @error('login') <p id="login-error" class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="field-label">Password <span class="text-red-600">*</span></label>
            <div class="relative">
                <x-icon name="lock-simple" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-500" />
                <input id="password" x-bind:type="showPassword ? 'text' : 'password'" type="password" wire:model="password"
                       autocomplete="current-password" placeholder="Masukkan password"
                       class="field pr-11 pl-9" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                <button type="button" x-on:click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 grid w-11 place-items-center rounded-r-lg text-slate-500 hover:text-slate-800"
                        x-bind:aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'">
                    <span x-show="!showPassword"><x-icon name="eye" class="size-4" /></span>
                    <span x-cloak x-show="showPassword"><x-icon name="eye-slash" class="size-4" /></span>
                </button>
            </div>
            @error('password') <p id="password-error" class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between gap-3 pt-1">
            <label class="inline-flex min-h-11 cursor-pointer items-center gap-2 text-sm text-slate-700 select-none">
                <input type="checkbox" wire:model="remember" class="size-4 rounded border-slate-300 text-brand-900 focus:ring-brand-600">
                Ingat saya
            </label>
            <button type="button" x-on:click="forgotOpen = true" class="min-h-11 text-sm font-semibold text-brand-700 hover:text-brand-900 hover:underline">
                Lupa password?
            </button>
        </div>

        <button type="submit" class="btn btn-primary group w-full min-h-11" wire:loading.attr="disabled" wire:target="authenticate">
            <span wire:loading.remove wire:target="authenticate">Masuk</span>
            <span wire:loading wire:target="authenticate">Memeriksa...</span>
            <x-icon name="sign-in" class="size-4 transition-transform duration-200 group-hover:translate-x-0.5" />
        </button>
    </form>

    {{-- Forgot password: accounts are managed by the administrator --}}
    <div x-cloak x-show="forgotOpen" x-transition.opacity class="fixed inset-0 z-[60] grid place-items-center bg-brand-950/50 p-4" x-on:keydown.escape.window="forgotOpen = false">
        <div x-on:click.outside="forgotOpen = false" class="card w-full max-w-md p-6" role="dialog" aria-modal="true" aria-labelledby="forgot-title">
            <div class="flex items-start gap-3">
                <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-700">
                    <x-icon name="key" class="size-5" />
                </span>
                <div>
                    <h3 id="forgot-title" class="text-base font-bold text-brand-900">Reset password</h3>
                    <p class="mt-1 text-sm leading-relaxed text-slate-600">
                        Akun auditor dikelola oleh administrator. Hubungi administrator sistem untuk mengatur ulang password Anda.
                    </p>
                </div>
            </div>
            <div class="mt-5 flex justify-end">
                <button type="button" x-on:click="forgotOpen = false" class="btn btn-secondary">Tutup</button>
            </div>
        </div>
    </div>
</div>
