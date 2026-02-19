<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;



return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('criterios_details', function (Blueprint $table) {
            $table->bigIncrements('id_criterio_detail');
            $table->unsignedBigInteger('id_criterio');
            $table->string('sector');
            
            $table->timestamps();

            $table->foreign('id_criterio')
                ->references('id_criterio')
                ->on('criterios');
            $table->softDeletes();

        });

        DB::table('criterios_details')->insert([
            // A
            ['id_criterio' => 1, 'sector' => 'A'],
            ['id_criterio' => 2, 'sector' => 'A'],
            ['id_criterio' => 3, 'sector' => 'A'],
            ['id_criterio' => 4, 'sector' => 'A'],

            // B
            ['id_criterio' => 5, 'sector' => 'B'],
            ['id_criterio' => 6, 'sector' => 'B'],
            ['id_criterio' => 7, 'sector' => 'B'],
            ['id_criterio' => 8, 'sector' => 'B'],
            ['id_criterio' => 9, 'sector' => 'B'],
            ['id_criterio' => 10, 'sector' => 'B'],

            // C
            ['id_criterio' => 11, 'sector' => 'C'],
            ['id_criterio' => 12, 'sector' => 'C'],
            ['id_criterio' => 13, 'sector' => 'C'],
            ['id_criterio' => 14, 'sector' => 'C'],
            ['id_criterio' => 15, 'sector' => 'C'],

            // D
            ['id_criterio' => 16, 'sector' => 'D'],
            ['id_criterio' => 17, 'sector' => 'D'],
            ['id_criterio' => 18, 'sector' => 'D'],
            ['id_criterio' => 19, 'sector' => 'D'],
            ['id_criterio' => 20, 'sector' => 'D'],
            ['id_criterio' => 21, 'sector' => 'D'],
            ['id_criterio' => 22, 'sector' => 'D'],
            ['id_criterio' => 23, 'sector' => 'D'],
            ['id_criterio' => 24, 'sector' => 'D'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('criterios_details');
    }
};
