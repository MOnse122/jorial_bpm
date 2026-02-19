<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // IMPORTANTE: Agrega esta línea

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->bigIncrements('id_document');
            $table->string('document_type');
            $table->timestamps();
        });

        DB::table('documents')->insert([
            ['document_type' => 'FACTURA', 'created_at' => now(), 'updated_at' => now()],
            ['document_type' => 'REMISION', 'created_at' => now(), 'updated_at' => now()],
            ['document_type' => 'OTRO', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};