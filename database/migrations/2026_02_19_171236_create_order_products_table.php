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
        Schema::create('order_products', function (Blueprint $table) {

            $table->bigIncrements('id_order_product');
            $table->unsignedBigInteger('id_document')->nullable();

            $table->unsignedBigInteger('id_purchase_order');
            $table->unsignedBigInteger('id_product');

            $table->string('document_type')->nullable();
            $table->string('document_number')->nullable();


            $table->timestamps();


            $table->foreign('id_purchase_order')
                ->references('id_purchase_order')
                ->on('purchase_order')
                ->onDelete('cascade');

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
        Schema::dropIfExists('order_products');
    }
};
