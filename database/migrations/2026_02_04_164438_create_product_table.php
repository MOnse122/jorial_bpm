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
            $table->bigIncrements('id_product');
            $table->string('title');
            $table->string('description');
            $table->decimal('width', 5, 2);
            $table->decimal('height', 5, 2);
            $table->decimal('cal', 5, 2);
            $table->enum('state', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->unsignedBigInteger('id_provider');
            $table->foreign('id_provider')->references('id_provider')->on('providers');            
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};
