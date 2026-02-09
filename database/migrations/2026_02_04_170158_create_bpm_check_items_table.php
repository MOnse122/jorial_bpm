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
        Schema::create('bpm_check_items', function (Blueprint $table) {
 $table->bigIncrements('id_item');

            $table->unsignedBigInteger('id_test_bpm');
            $table->string('criterion');
            $table->enum('value', ['PASS', 'FAIL', 'NA']);
            

            $table->timestamps();

            $table->foreign('id_test_bpm')
                ->references('id_test_bpm')
                ->on('test_bpms');
            $table->softDeletes();

                
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bpm_check_items');
    }
};
