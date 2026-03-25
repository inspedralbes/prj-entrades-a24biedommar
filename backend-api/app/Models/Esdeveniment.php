<?php

namespace App\Models;

//================================ IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

//================================ CLASSE ============

/**
 * Model per a la taula `esdeveniments`.
 * Cada registre és un esdeveniment venible amb recinte, data i llindar de demanda per a la cua.
 */
class Esdeveniment extends Model
{
    /**
     * @var string
     */
    protected $table = 'esdeveniments';

    /**
     * @var bool
     */
    public $timestamps = false;

    //================================ PROPIETATS ============

    /**
     * @var list<string>
     */
    protected $fillable = [
        'titol',
        'descripcio',
        'data_esdeveniment',
        'nom_recinte',
        'url_imatge',
        'llindar_n',
        'latitud',
        'longitud',
        'actiu',
    ];

    //================================ RELACIONS ============

    /**
     * Zones de seient d’aquest esdeveniment.
     */
    public function zonesDeSeient(): HasMany
    {
        return $this->hasMany(ZonaSeient::class, 'esdeveniment_id');
    }

    //================================ CASTS ============

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data_esdeveniment' => 'datetime',
            'actiu' => 'boolean',
            'llindar_n' => 'integer',
            'latitud' => 'float',
            'longitud' => 'float',
        ];
    }
}
