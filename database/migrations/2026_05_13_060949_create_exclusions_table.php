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
        Schema::create('exclusions', function (Blueprint $table) {
            $table->id();

            // La opción que DISPARA la exclusión (ej: "Talla M")
            $table->foreignId('producto_opciones_id')
                ->constrained('product_options')
                ->cascadeOnDelete();

            // El atributo que se bloquea (ej: "Color")
            $table->unsignedBigInteger('attribute_id');
            $table->foreign('attribute_id')
                ->references('id')
                ->on('attributes')
                ->cascadeOnDelete();

            // El valor específico que se bloquea (ej: "Amarillo")
            $table->unsignedBigInteger('value_id');
            $table->foreign('value_id')
                ->references('id')
                ->on('values')
                ->cascadeOnDelete();

            // Evitar duplicados
            $table->unique(
                ['producto_opciones_id', 'attribute_id', 'value_id'],
                'exclusion_unica'
            );

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exclusions');
    }
};
