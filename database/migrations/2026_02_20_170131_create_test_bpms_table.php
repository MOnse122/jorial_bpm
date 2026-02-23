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
            $table->unsignedBigInteger('users_id');

            $table->string('observations')->nullable();
            $table->string('name_provider');
            $table->integer('total_score');
            $table->decimal('percentage', 5, 2);
            $table->string('result', 50);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_purchase_order')
                ->references('id_purchase_order')
                ->on('purchase_order')
                ->cascadeOnDelete();

            $table->foreign('users_id')
                ->references('id')     
                ->on('users');

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
