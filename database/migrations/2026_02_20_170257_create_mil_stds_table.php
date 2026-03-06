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
        Schema::create('mil_stds', function (Blueprint $table) {
            $table->bigIncrements('id_mil_std');
            $table->unsignedBigInteger('id_purchase_order');

            $table->unsignedBigInteger('id_product');

            $table->enum('c1', ['1', '2', '3']);
            $table->enum('c2', ['1', '2', '3']);
            $table->enum('c3', ['1', '2', '3']);

            $table->string('inspection_level', 20);
            $table->decimal('aql', 4, 2);
            $table->integer('sample_size');
            $table->decimal('sample_acept', 8, 2);
            $table->decimal('sample_reject', 8, 2);

            $table->timestamps();

            $table->foreign('id_product')
                ->references('id_product')
                ->on('products')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mil_stds');
    }
};
