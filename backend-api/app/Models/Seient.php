<?php

namespace App\Models;

//================================ IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

//================================ CLASSE ============

/**
 * Model per a la taula `seients`.
 * Estat del seient (disponible, reservat, venut) i retenció temporal per usuari.
 */
class Seient extends Model
{
    /**
     * @var string
     */
    protected $table = 'seients';

    /**
     * @var bool
     */
    public $timestamps = false;

    //================================ PROPIETATS ============

    /**
     * @var list<string>
     */
    protected $fillable = [
        'zona_id',
        'etiqueta_fila',
        'numero_seient',
        'estat',
        'retingut_per_usuari_id',
        'caducitat_reserva',
    ];

    //================================ RELACIONS ============

    /**
     * Zona de seient a la qual pertany aquest seient.
     */
    public function zona(): BelongsTo
    {
        return $this->belongsTo(ZonaSeient::class, 'zona_id');
    }

    /**
     * Usuari que té la reserva activa, si n’hi ha.
     */
    public function usuariQueReté(): BelongsTo
    {
        return $this->belongsTo(Usuari::class, 'retingut_per_usuari_id');
    }

    /**
     * Tiquet emès per aquest seient un cop venut (com a màxim un).
     */
    public function tiquet(): HasOne
    {
        return $this->hasOne(Tiquet::class, 'seient_id');
    }

    //================================ CASTS ============

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'caducitat_reserva' => 'datetime',
            'estat' => 'string',
        ];
    }
}
