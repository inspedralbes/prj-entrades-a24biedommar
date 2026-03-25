<?php

namespace App\Models;

// ================================ IMPORTS ============

use Database\Factories\UsuariFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// ================================ CLASSE ============

/**
 * Model Eloquent per a la taula `usuaris`.
 * Representa comptes d’accés (administrador o client) i és el model d’autenticació de l’aplicació.
 */
class Usuari extends Authenticatable
{
    /** @use HasFactory<UsuariFactory> */
    use HasApiTokens;

    use HasFactory;
    use Notifiable;

    /**
     * Taula física a PostgreSQL (noms en català segons db/init.sql).
     *
     * @var string
     */
    protected $table = 'usuaris';

    /**
     * Nom de la columna de creació (no s’usa `created_at` per defecte).
     */
    public const CREATED_AT = 'creat_el';

    /**
     * No hi ha columna d’actualització a l’esquema SQL.
     *
     * @var null
     */
    public const UPDATED_AT = null;

    // ================================ PROPIETATS ============

    /**
     * Camps assignables en massa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nom',
        'correu_electronic',
        'contrasenya',
        'rol',
    ];

    /**
     * Camps ocults en serialització (JSON).
     *
     * @var list<string>
     */
    protected $hidden = [
        'contrasenya',
    ];

    // ================================ MÈTODES PÚBLICS ============

    /**
     * Retorna el nom de la columna de contrasenya per a l’autenticació Laravel.
     */
    public function getAuthPasswordName(): string
    {
        return 'contrasenya';
    }

    /**
     * La taula no té columna `remember_token`; es desactiva el recordatori de sessió.
     */
    public function getRememberTokenName(): ?string
    {
        return null;
    }

    /**
     * Comandes associades a l’usuari (comprador).
     */
    public function comandes(): HasMany
    {
        return $this->hasMany(Comanda::class, 'usuari_id');
    }

    /**
     * Seients que aquest usuari té retinguts temporalment (reserva activa).
     */
    public function seientsRetinguts(): HasMany
    {
        return $this->hasMany(Seient::class, 'retingut_per_usuari_id');
    }

    // ================================ CASTS ============

    /**
     * Conversió de tipus d’atributs.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'creat_el' => 'datetime',
            'contrasenya' => 'hashed',
            'rol' => 'string',
        ];
    }
}
