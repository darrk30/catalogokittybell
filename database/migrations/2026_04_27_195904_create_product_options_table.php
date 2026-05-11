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
        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable();
            $table->decimal('precio_extra', 10, 2)->default(0.00);
            $table->decimal('stock', 10, 2)->default(0.00);
            $table->string('imagen_path')->nullable();
            $table->boolean('estado')->default(true);
            $table->foreignId('attribute_id')->constrained()->onDelete('cascade');
            $table->foreignId('value_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_options');
    }
};
