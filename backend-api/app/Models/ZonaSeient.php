<?php

namespace App\Models;

//================================ IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

//================================ CLASSE ============

/**
 * Model per a la taula `zones_de_seient`.
 * Agrupa seients amb preu base i color per al mapa.
 */
class ZonaSeient extends Model
{
    /**
     * @var string
     */
    protected $table = 'zones_de_seient';

    /**
     * @var bool
     */
    public $timestamps = false;

    //================================ PROPIETATS ============

    /**
     * @var list<string>
     */
    protected $fillable = [
        'esdeveniment_id',
        'nom_zona',
        'preu_base',
        'codi_color',
    ];

    //================================ RELACIONS ============

    /**
     * Esdeveniment al qual pertany la zona.
     */
    public function esdeveniment(): BelongsTo
    {
        return $this->belongsTo(Esdeveniment::class, 'esdeveniment_id');
    }

    /**
     * Seients dins d’aquesta zona.
     */
    public function seients(): HasMany
    {
        return $this->hasMany(Seient::class, 'zona_id');
    }

    //================================ CASTS ============

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'preu_base' => 'decimal:2',
        ];
    }
}
