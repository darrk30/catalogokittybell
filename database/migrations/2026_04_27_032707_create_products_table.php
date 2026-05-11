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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2)->default(0.00);
            $table->decimal('precio_con_descuento', 10, 2)->default(0.00);
            $table->integer('descuento')->default(0.00);
            $table->decimal('stock', 10, 2)->default(0.00);
            $table->string('imagen_path')->nullable();
            $table->string('imagen_path_tallas')->nullable();
            $table->boolean('estado')->default(true);
            $table->string('slug');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('categorie_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['user_id', 'slug']);
            $table->unique(['user_id', 'codigo']);
            $table->index('nombre');
            $table->index('codigo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
