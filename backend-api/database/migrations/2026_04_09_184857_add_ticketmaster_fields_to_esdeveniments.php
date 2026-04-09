<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('esdeveniments', function (Blueprint $table) {
            $table->string('tm_id', 100)->nullable()->after('id');
            $table->json('tm_imatges')->nullable()->after('url_imatge');
            $table->json('tm_venue')->nullable()->after('nom_recinte');
            $table->json('tm_classificacions')->nullable()->after('descripcio');
            $table->json('tm_preus')->nullable()->after('data_esdeveniment');
            $table->json('tm_artistes')->nullable()->after('tm_classificacions');
            $table->text('tm_descripcio')->nullable()->after('descripcio');
            $table->text('tm_informacio')->nullable()->after('tm_descripcio');
            $table->string('tm_url_oficial', 500)->nullable()->after('url_imatge');
            $table->string('tm_mapa_seients', 500)->nullable()->after('tm_url_oficial');
            $table->json('tm_accessibilitat')->nullable()->after('tm_informacio');
            $table->json('tm_aparcament')->nullable()->after('tm_accessibilitat');
            $table->json('tm_portes_info')->nullable()->after('tm_aparcament');
            $table->string('tm_hora', 20)->nullable()->after('data_esdeveniment');
            $table->string('tm_ciutat', 100)->nullable()->after('nom_recinte');
            $table->dropColumn('llindar_n');
        });
    }

    public function down(): void
    {
        Schema::table('esdeveniments', function (Blueprint $table) {
            $table->dropColumn([
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
            ]);
            $table->integer('llindar_n')->default(100);
        });
    }
};