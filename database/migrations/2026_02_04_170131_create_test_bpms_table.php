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
        Schema::create('test_bpms', function (Blueprint $table) {
            $table->id('id_test_bpm');

            $table->enum('result', ['PASS', 'FAIL']);
            $table->text('observations')->nullable();
            $table->date('date');

            $table->unsignedBigInteger('id_purchase_order');
            
            $table->timestamps();

            $table->foreign('id_purchase_order')
                ->references('id_purchase_order')
                ->on('purchase_order')
                ->cascadeOnDelete();
            $table->softDeletes();


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
