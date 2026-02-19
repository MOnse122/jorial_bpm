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
        Schema::create('evaluation', function (Blueprint $table) {
            $table->bigIncrements('id_evaluation');
            $table->unsignedBigInteger('id_criterio_detail');


            $table->integer('score');
            $table->timestamps();

            $table->foreign('id_criterio_detail')
                ->references('id_criterio_detail')
                ->on('criterios_details');
            $table->softDeletes();

            


        });
        


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation');
    }
};
