<?php

namespace Database\Factories;

//================================ IMPORTS ============

use App\Models\Usuari;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

//================================ CLASSE ============

/**
 * Factory per generar registres d’`Usuari` en entorns de prova.
 *
 * @extends Factory<Usuari>
 */
class UsuariFactory extends Factory
{
    /**
     * @var class-string<Usuari>
     */
    protected $model = Usuari::class;

    /**
     * Contrasenya per defecte hashada (una sola vegada).
     */
    protected static ?string $contrasenyaPerDefecte = null;

    //================================ MÈTODES ============

    /**
     * Estat inicial d’un usuari generat.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        if (static::$contrasenyaPerDefecte === null) {
            static::$contrasenyaPerDefecte = Hash::make('contrasenya');
        }

        return [
            'nom' => fake()->name(),
            'correu_electronic' => fake()->unique()->safeEmail(),
            'contrasenya' => static::$contrasenyaPerDefecte,
            'rol' => 'client',
        ];
    }

    /**
     * Usuari amb rol administrador.
     */
    public function administrador(): static
    {
        return $this->state(function (array $atributs) {
            return [
                'rol' => 'admin',
            ];
        });
    }
}
