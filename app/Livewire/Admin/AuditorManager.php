<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin CRUD for auditor accounts. Passwords are never shown; leave the field empty to keep the current one.
 */
#[Title('Akun Auditor')]
class AuditorManager extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $username = '';

    public string $email = '';

    public string $team = '';

    public string $password = '';

    public bool $isActive = true;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetValidation();
        $this->reset('editingId', 'name', 'username', 'email', 'password');
        $this->team = 'Tim Auditor Internal';
        $this->isActive = true;
        $this->showForm = true;
    }

    public function edit(int $userId): void
    {
        $user = $this->auditor($userId);

        $this->resetValidation();
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = (string) $user->email;
        $this->team = (string) $user->team;
        $this->password = '';
        $this->isActive = $user->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'max:50', Rule::unique('users', 'username')->ignore($this->editingId)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)],
            'team' => ['nullable', 'string', 'max:255'],
            'password' => [$this->editingId ? 'nullable' : 'required', 'string', Password::min(8)],
            'isActive' => ['boolean'],
        ], attributes: [
            'name' => 'nama lengkap',
            'team' => 'tim / departemen',
        ]);

        $attributes = [
            'name' => trim($validated['name']),
            'username' => strtolower($validated['username']),
            'email' => $validated['email'] ?: null,
            'team' => $validated['team'] ?: null,
            'is_active' => $validated['isActive'],
            'role' => UserRole::Auditor,
        ];

        if (filled($validated['password'])) {
            $attributes['password'] = $validated['password'];
        }

        if ($this->editingId) {
            $this->auditor($this->editingId)->update($attributes);
            $message = "Akun {$attributes['name']} diperbarui.";
        } else {
            User::query()->create($attributes);
            $message = "Akun auditor {$attributes['name']} dibuat.";
        }

        $this->showForm = false;
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function toggleActive(int $userId): void
    {
        $user = $this->auditor($userId);
        $user->update(['is_active' => ! $user->is_active]);

        $this->dispatch('toast', message: $user->name.($user->is_active ? ' diaktifkan.' : ' dinonaktifkan.'), type: 'info');
    }

    public function delete(int $userId): void
    {
        $user = $this->auditor($userId);
        $name = $user->name;
        $user->delete();

        $this->dispatch('toast', message: "Akun {$name} dihapus. Audit miliknya tetap tersimpan.", type: 'info');
    }

    private function auditor(int $userId): User
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return User::query()->where('role', UserRole::Auditor)->findOrFail($userId);
    }

    public function render(): View
    {
        return view('livewire.admin.auditor-manager', [
            'auditors' => User::query()
                ->where('role', UserRole::Auditor)
                ->withCount('audits')
                ->when($this->search !== '', fn (Builder $query) => $query->where(fn (Builder $q) => $q
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('username', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")))
                ->orderBy('name')
                ->paginate(15),
        ]);
    }
}
