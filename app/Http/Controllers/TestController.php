<?php

namespace App\Http\Controllers;

use App\Models\CheckBpm;
use App\Models\TestBpmDetail;
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
            'users_id' => 'required|exists:users,id',
            'name_provider' => 'required|string',
            'observations' => 'nullable|string',
            'details' => 'required|array'
        ]);

        DB::beginTransaction();

        try {

            $sectorWeight = [
                'A' => 5,
                'B' => 4,
                'C' => 3,
                'D' => 2,
                'E' => 5,
            ];

            $totalScore = 0;
            $maxScore = 0;
            $hasExclusiveNo = false;

            foreach ($request->details as $detail) {

                if ($detail['score'] === 'NA') continue;

                $weight = $sectorWeight[$detail['sector']] ?? 0;
                $maxScore += $weight;

                if ($detail['score'] === 'SI') {
                    $totalScore += $weight;
                }

                if ($detail['sector'] === 'E' && $detail['score'] === 'NO') {
                    $hasExclusiveNo = true;
                }
            }

            $percentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

            if ($hasExclusiveNo) $result = 'NO CONFORMIDAD';
            elseif ($percentage >= 91) $result = 'APROBADO';
            elseif ($percentage >= 70) $result = 'CONDICIONAL';
            else $result = 'RECHAZADO';

            // 🔹 Crear evaluación principal
            $evaluation = CheckBpm::create([
                'id_purchase_order' => $request->id_purchase_order,
                'users_id' => $request->users_id,
                'name_provider' => $request->name_provider,
                'observations' => $request->observations,
                'total_score' => $totalScore,
                'percentage' => $percentage,
                'result' => $result,
            ]);

            // 🔹 Guardar detalles usando modelo
            foreach ($request->details as $detail) {

                TestBpmDetail::create([
                    'id_test_bpm' => $evaluation->id_test_bpm,
                    'id_criterio_detail' => $detail['id_criterio_detail'],
                    'score' => $detail['score'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Evaluación guardada correctamente',
                'result' => $result,
                'percentage' => $percentage
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
