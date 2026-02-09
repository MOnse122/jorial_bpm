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
        Schema::create('documents', function (Blueprint $table) {
            $table->bigIncrements('id_document');
            $table->enum('document_type', ['FACTURA', 'REMISION', 'OTRO']);
            $table->string('number', 50);
            $table->date('date');
            $table->unsignedBigInteger('id_purchase_order');
            $table->foreign('id_purchase_order')->references('id_purchase_order')->on('purchase_order')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
