<?php

//================================ NAMESPACES / IMPORTS ============

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

//================================ PROPIETATS / ATRIBUTS ==========

//================================ MÈTODES / FUNCIONS ===========

/**
 * Model Eloquent per a la taula `favorits`.
 * Relacio entre usuari i esdeveniment (M'interessa).
 */
class Favorit extends Model
{
    /**
     * Taula física a PostgreSQL.
     *
     * @var string
     */
    protected $table = 'favorits';

    /**
     * Camps assignables en massa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'usuari_id',
        'event_id',
        'event_nom',
        'event_data',
        'event_imatge',
    ];

    /**
     * No hi ha columna d'actualització a l'esquema SQL.
     *
     * @var null
     */
    public const UPDATED_AT = null;

    /**
     * Relació amb l'usuari que ha guardat el favorit.
     */
    public function usuari(): BelongsTo
    {
        return $this->belongsTo(Usuari::class, 'usuari_id');
    }

    /**
     * Comprova si un event és favorit per a un usuari.
     */
    public static function isFavorit(int $usuariId, string $eventId): bool
    {
        return self::where('usuari_id', $usuariId)
            ->where('event_id', $eventId)
            ->exists();
    }
}