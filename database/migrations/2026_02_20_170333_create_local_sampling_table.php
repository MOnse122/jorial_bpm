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
        Schema::create('local_sampling', function (Blueprint $table) {
            $table->id('id_sampling');

            $table->unsignedBigInteger('id_mil_std');

            $table->decimal('width', 8, 2);
            $table->decimal('length', 8, 2);
            $table->decimal('thickness', 6, 2);

            $table->string('seal_resistance', 25);
            $table->string('color_detachment', 25);

            $table->integer('piece_number');

            $table->enum('result_lote', ['APROBADO', 'RECHAZADO']);
            $table->enum('result_piece', ['1', '2', '3', '4']);
            $table->text('observation')->nullable();

            $table->timestamps();

            $table->foreign('id_mil_std')
            ->references('id_mil_std')
            ->on('mil_stds'
            )->onDelete('cascade');
            
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('local_sampling');
    }
};
