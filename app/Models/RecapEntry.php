<?php

namespace App\Models;

use Database\Factories\RecapEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A manually entered recap row ("New Input" on the Rekap page), for departments audited outside the app.
 */
#[Fillable(['audit_id', 'department', 'auditee', 'auditor', 'total', 'sesuai', 'major', 'minor', 'observasi', 'note'])]
class RecapEntry extends Model
{
    /** @use HasFactory<RecapEntryFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'sesuai' => 'integer',
            'major' => 'integer',
            'minor' => 'integer',
            'observasi' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Audit, $this>
     */
    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class);
    }
}
