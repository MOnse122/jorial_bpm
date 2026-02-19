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
        Schema::create('providers', function (Blueprint $table) {
            $table->bigIncrements('id_provider');
            $table->string('name');
            $table->enum('state', ['NORMAL', 'REDUCIDA', 'SEVERA'])->default('NORMAL');
            $table->timestamps();
            $table->softDeletes();

        });

        DB::table('providers')->insert([
        ['name' => 'Plastics', 'state' => 'NORMAL', 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'Plast', 'state' => 'REDUCIDA', 'created_at' => now(), 'updated_at' => now()],

        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
