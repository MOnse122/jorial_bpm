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
        Schema::create('purchase_order', function (Blueprint $table) {
            $table->bigIncrements('id_purchase_order');
            $table->string('folio')->unique();
            $table->date('date');
            $table->enum('status', ['OPEN', 'CLOSED', 'CANCELLED']);

            $table->unsignedBigInteger('id_provider');
            $table->softDeletes();


            $table->timestamps();


            $table->foreign('id_provider')
                ->references('id_provider')
                ->on('providers')
                ->cascadeOnDelete();

            
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order');
    }
};
