<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void{
        Schema::create('test_bpms', function (Blueprint $table) {
            $table->bigIncrements('id_test_bpm');
            $table->unsignedBigInteger('id_purchase_order');
            $table->unsignedBigInteger('user_id'); // ← CAMBIAR
            $table->unsignedBigInteger('id_evaluation');

            $table->string('observations')->nullable();
            $table->string('name_provider');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_purchase_order')
                ->references('id_purchase_order')
                ->on('purchase_order')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')     
                ->on('users');

            $table->foreign('id_evaluation')
                ->references('id_evaluation')
                ->on('evaluation')
                ->cascadeOnDelete();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_bpms');
    }
};
