<?php

namespace App\Models;

//================================ IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

//================================ CLASSE ============

/**
 * Model per a la taula `tiquets`.
 * Entrada emesa amb hash per validació (QR).
 */
class Tiquet extends Model
{
    /**
     * @var string
     */
    protected $table = 'tiquets';

    /**
     * @var string
     */
    public const CREATED_AT = 'comprat_el';

    /**
     * @var null
     */
    public const UPDATED_AT = null;

    //================================ PROPIETATS ============

    /**
     * @var list<string>
     */
    protected $fillable = [
        'comanda_id',
        'seient_id',
        'hash_qr',
    ];

    //================================ RELACIONS ============

    /**
     * Comanda a la qual pertany el tiquet.
     */
    public function comanda(): BelongsTo
    {
        return $this->belongsTo(Comanda::class, 'comanda_id');
    }

    /**
     * Seient assignat a aquest tiquet.
     */
    public function seient(): BelongsTo
    {
        return $this->belongsTo(Seient::class, 'seient_id');
    }

    //================================ CASTS ============

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'comprat_el' => 'datetime',
        ];
    }
}
