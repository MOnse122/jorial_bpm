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
        Schema::create('plates', function (Blueprint $table) {
            $table->bigIncrements('id_plate');
            $table->string('plate_number');
            $table->unsignedBigInteger('id_provider');
            $table->timestamps();

            $table->foreign('id_provider')
                ->references('id_provider')
                ->on('providers')
                ->onDelete('cascade');

            $table->unique(['plate_number', 'id_provider']);
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plates');
    }


};
