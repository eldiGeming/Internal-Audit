<?php

namespace App\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'logo_path'])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::deleted(function (Company $company): void {
            $company->deleteLogoFile();
        });
    }

    public function deleteLogoFile(): void
    {
        if ($this->logo_path) {
            Storage::disk('public')->delete($this->logo_path);
        }
    }

    /**
     * @return HasMany<Audit, $this>
     */
    public function audits(): HasMany
    {
        return $this->hasMany(Audit::class);
    }

    /**
     * Company names for the "client" autocomplete: only companies of audits the user may see.
     *
     * @return Collection<int, string>
     */
    public static function suggestionsFor(User $user): Collection
    {
        return static::query()
            ->whereHas('audits', fn ($query) => $query->visibleTo($user))
            ->orderBy('name')
            ->limit(200)
            ->pluck('name');
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    /**
     * Base64 data URI of the logo, used by the PDF renderer which cannot fetch URLs.
     */
    public function logoDataUri(): ?string
    {
        $disk = Storage::disk('public');

        if (! $this->logo_path || ! $disk->exists($this->logo_path)) {
            return null;
        }

        return 'data:'.$disk->mimeType($this->logo_path).';base64,'.base64_encode($disk->get($this->logo_path));
    }
}
