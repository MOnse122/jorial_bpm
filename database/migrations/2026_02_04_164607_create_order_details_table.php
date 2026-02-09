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
        Schema::create('order_details', function (Blueprint $table) {
            $table->bigIncrements('id_order_detail');

            $table->unsignedBigInteger('id_purchase_order');
            $table->unsignedBigInteger('id_product');

            $table->string('lot', 50)->nullable();
            $table->string('unit_measure', 20);

            $table->integer('bulk_or_roll_quantity');
            $table->integer('individual_quantity');

            $table->boolean('non_conformity')->default(false);

            $table->string('document_number', 50)->nullable();

            $table->timestamps();

            $table->foreign('id_purchase_order')
                ->references('id_purchase_order')
                ->on('purchase_order')
                ->onDelete('cascade');

            $table->foreign('id_product')
                ->references('id_product')
                ->on('products')
                ->onDelete('restrict');
            $table->softDeletes();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
