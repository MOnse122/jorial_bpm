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
        Schema::create('criterios', function (Blueprint $table) {
            $table->bigIncrements('id_criterio');
            
            $table->string('description');

            $table->timestamps();
            $table->softDeletes();
        });

        // Insertar criterios
        DB::table('criterios')->insert([
            // A. INGRESO DEL PROVEEDOR
            ['description' => 'Registro en bitácora de ingreso'],
            ['description' => 'Identificación vigente del proveedor'],
            ['description' => 'Lavado y/o desinfección de manos al ingreso'],
            ['description' => 'Notifica enfermedad, herida o lesión visible (si aplica)'],

            // B. HIGIENE PERSONAL (BPM)
            ['description' => 'Uso de cofia y cubrebocas'],
            ['description' => 'Uniforme limpio y completo'],
            ['description' => 'Zapato cerrado'],
            ['description' => 'Sin joyería, relojes o artículos sueltos'],
            ['description' => 'Uñas cortas y limpias (sin esmalte)'],
            ['description' => 'Sin uso de perfumes, lociones o maquillaje'],

            // C. INSPECCIÓN DEL TRANSPORTE
            ['description' => 'Unidad limpia (sin basura u objetos sueltos)'],
            ['description' => 'Sin humedad, derrames u olores extraños'],
            ['description' => 'Sin presencia de plagas (roedores, insectos, aves)'],
            ['description' => 'Sin daños estructurales que expongan el material'],
            ['description' => 'Fumigación vigente (evidencia disponible o enviada previamente)'],

            // D. CONDICIONES DEL MATERIAL
            ['description' => 'Material limpio y protegido'],
            ['description' => 'Empaques íntegros (sin rupturas)'],
            ['description' => 'Etiquetado completo y legible'],
            ['description' => 'Identificación clara de lote'],
            ['description' => 'Certificado de calidad disponible'],
            ['description' => 'Ficha técnica disponible (si aplica)'],

            // Exclusivo para bolsas
            ['description' => 'Sin presencia de madera'],
            ['description' => 'Sin presencia de metal (viruta/fragmentos)'],
            ['description' => 'Sin presencia de vidrio (polvo/fragmentos)'],
            ['description' => 'Sin presencia de plástico rígido u objetos extraños'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('criterios');
    }
};
