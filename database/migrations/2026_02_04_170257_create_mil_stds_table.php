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

            $table->unsignedBigInteger('id_test_bpm');

            $table->string('inspection_level', 20);
            $table->decimal('aql', 4, 2);
            $table->enum('accept_reject', ['ACCEPT', 'REJECT']);
            $table->integer('sample_size');
            $table->enum('result', ['PASS', 'FAIL']);
            $table->string('material_disposition', 100);

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
        Schema::dropIfExists('mil_stds');
    }
};
