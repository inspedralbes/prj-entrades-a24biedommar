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
        'latitud',
        'longitud',
        'actiu',
        'tm_id',
        'tm_imatges',
        'tm_venue',
        'tm_classificacions',
        'tm_preus',
        'tm_artistes',
        'tm_descripcio',
        'tm_informacio',
        'tm_url_oficial',
        'tm_mapa_seients',
        'tm_accessibilitat',
        'tm_aparcament',
        'tm_portes_info',
        'tm_hora',
        'tm_ciutat',
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
