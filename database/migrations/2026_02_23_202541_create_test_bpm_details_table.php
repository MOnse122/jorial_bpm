<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_bpm_details', function (Blueprint $table) {

            $table->bigIncrements('id_test_bpm_detail');

            $table->foreignId('id_test_bpm')
                  ->constrained('test_bpms', 'id_test_bpm')
                  ->onDelete('cascade');

            $table->foreignId('id_criterio_detail')
                  ->constrained('criterios_details', 'id_criterio_detail')
                  ->onDelete('cascade');

            $table->string('score', 10);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_bpm_details');
    }
};