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
 * Admin CRUD for auditor accounts. Login checks the hashed password; the password the admin issues is also
 * kept encrypted (issued_password) so "Salin akun" can copy the full login details, password included, at
 * any time. It is never rendered into the page: it is fetched only when the copy button is clicked.
 * Leave the password field empty when editing to keep the current one.
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

    /** The one-time "account ready to share" dialog, shown after a password was set. */
    public bool $showShare = false;

    public string $shareText = '';

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
            $attributes['issued_password'] = $validated['password'];
        }

        if ($this->editingId) {
            $user = $this->auditor($this->editingId);
            $user->update($attributes);
            $message = "Akun {$attributes['name']} diperbarui.";
        } else {
            $user = User::query()->create($attributes);
            $message = "Akun auditor {$attributes['name']} dibuat.";
        }

        $this->showForm = false;
        $this->reset('password');
        $this->dispatch('toast', message: $message, type: 'success');

        if (filled($validated['password'])) {
            $this->shareText = self::loginInfo($user, $validated['password']);
            $this->showShare = true;
        }
    }

    /**
     * Full login details for one auditor, password included when the admin issued it through this screen.
     * Called from the copy button, so the password never sits in the page HTML.
     */
    public function loginInfoFor(int $userId): string
    {
        $user = $this->auditor($userId);

        return self::loginInfo($user, $user->issued_password);
    }

    public function closeShare(): void
    {
        $this->showShare = false;
        $this->shareText = '';
    }

    /**
     * Closing the share dialog (button, X or Escape) forgets the password, so it only exists for that one view.
     */
    public function updatedShowShare(bool $isOpen): void
    {
        if (! $isOpen) {
            $this->shareText = '';
        }
    }

    /**
     * Plain-text login details to paste into a chat or email. Accounts whose password was set before
     * issued passwords were kept have none to copy; the text then sends the auditor to the admin.
     */
    private static function loginInfo(User $user, ?string $password = null): string
    {
        return implode(PHP_EOL, array_filter([
            'Akun '.config('app.name'),
            'Nama: '.$user->name,
            'Username: '.$user->username,
            $user->email ? 'Email: '.$user->email : null,
            'Password: '.($password ?? 'hubungi admin'),
            'Link login: '.route('login'),
            '',
            'Masuk memakai username atau email di atas.',
        ], fn (?string $line): bool => $line !== null));
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
