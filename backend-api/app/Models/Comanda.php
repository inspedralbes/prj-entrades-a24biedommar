<?php

namespace App\Models;

//================================ IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

//================================ CLASSE ============

/**
 * Model per a la taula `comandes`.
 * Registra imports, estat de pagament i vinculació amb la passarel·la.
 */
class Comanda extends Model
{
    /**
     * @var string
     */
    protected $table = 'comandes';

    /**
     * @var string
     */
    public const CREATED_AT = 'creat_el';

    /**
     * @var null
     */
    public const UPDATED_AT = null;

    //================================ PROPIETATS ============

    /**
     * @var list<string>
     */
    protected $fillable = [
        'usuari_id',
        'import_total',
        'estat',
        'id_intencio_pagament',
    ];

    //================================ RELACIONS ============

    /**
     * Usuari comprador de la comanda.
     */
    public function usuari(): BelongsTo
    {
        return $this->belongsTo(Usuari::class, 'usuari_id');
    }

    /**
     * Tiquets (entrades) inclosos en la comanda.
     */
    public function tiquets(): HasMany
    {
        return $this->hasMany(Tiquet::class, 'comanda_id');
    }

    //================================ CASTS ============

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'creat_el' => 'datetime',
            'import_total' => 'decimal:2',
            'estat' => 'string',
        ];
    }
}
