<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::guest')]
#[Title('Login')]
class Login extends Component
{
    public string $login = '';

    public string $password = '';

    public bool $remember = false;

    /**
     * Accepts a username or an email address, like the reference login form.
     */
    public function authenticate(): void
    {
        $this->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ], attributes: [
            'login' => 'username atau email',
        ]);

        $throttleKey = Str::transliterate(Str::lower($this->login).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'login' => 'Terlalu banyak percobaan login. Coba lagi dalam '.RateLimiter::availableIn($throttleKey).' detik.',
            ]);
        }

        $field = filter_var($this->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [$field => trim($this->login), 'password' => $this->password, 'is_active' => true];

        if (! Auth::attempt($credentials, $this->remember)) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'login' => 'Username/email atau password salah, atau akun sedang nonaktif.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        session()->regenerate();

        $this->redirectIntended(route('home'));
    }

    /**
     * Quick-fill buttons for the demo accounts, available only in the local environment.
     */
    public function fillDemo(string $role): void
    {
        abort_unless(app()->isLocal(), 404);

        [$this->login, $this->password] = $role === 'admin'
            ? ['admin', 'admin123']
            : ['auditor', 'auditor123'];

        $this->resetErrorBag();
    }

    public function render(): View
    {
        return view('livewire.auth.login', [
            'showDemo' => app()->isLocal(),
        ]);
    }
}
