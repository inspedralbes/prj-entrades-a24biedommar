<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('favorits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuari_id')->constrained('usuaris')->onDelete('cascade');
            $table->string('event_id'); // ID de Ticketmaster
            $table->string('event_nom');
            $table->string('event_data')->nullable();
            $table->text('event_imatge')->nullable();
            $table->timestamp('creat_el')->useCurrent();

            // Evitar duplicats del mateix event per al mateix usuari
            $table->unique(['usuari_id', 'event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorits');
    }
};
