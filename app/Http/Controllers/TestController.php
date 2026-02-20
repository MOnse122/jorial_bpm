<?php

namespace App\Http\Controllers;

use App\Models\CheckBpm;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\CriteriosDetails;


class TestController extends Controller

{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Views/Test');
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_purchase_order' => 'required|exists:purchase_order,id_purchase_order',
            'user_id' => 'required|exists:users,id',
            'id_evaluation' => 'required|exists:evaluation,id_evaluation',
            'observations' => 'nullable|string',
            'name_provider' => 'required|string',
            'details' => 'required|array'
        ]);

        DB::beginTransaction();

        try {

            // 1️⃣ Crear test
            $test = CheckBpm::create([
                'id_purchase_order' => $request->id_purchase_order,
                'user_id' => $request->user_id,
                'id_evaluation' => $request->id_evaluation,
                'observations' => $request->observations,
                'name_provider' => $request->name_provider,
            ]);

            // 2️⃣ Insertar detalles (24 criterios)
            foreach ($request->details as $detail) {
                DB::table('evaluation_details')->insert([
                    'id_evaluation' => $request->id_evaluation,
                    'id_criterio_detail' => $detail['id_criterio_detail'],
                    'score' => $detail['score'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Evaluación guardada correctamente'
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $criterios = CriteriosDetails::with(['criterio'])
            ->get();
            
        
        return response()->json($criterios);
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
