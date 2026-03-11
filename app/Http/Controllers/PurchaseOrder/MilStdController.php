<?php

namespace App\Http\Controllers\PurchaseOrder;

use App\Http\Controllers\Controller;
use App\Models\OrderDetails;
use App\Models\PurchaseOrder;
use App\Models\MilStd;
use App\Models\LocalSampling;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MilStdController extends Controller
{

    public function show($id_purchase_order, $id_product = null)
    {
        return Inertia::render('Views/mil-std', [
            'id_purchase_order' => $id_purchase_order,
            'id_product'        => $id_product
        ]);
    }

    public function orderProducts($id_purchase_order)
    {
        $order = PurchaseOrder::with('products')
            ->where('id_purchase_order', $id_purchase_order)
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Orden no encontrada'], 404);
        }

        $orderDetails = OrderDetails::where('id_purchase_order', $id_purchase_order)
            ->get()
            ->map(function ($detail) {
                $detail->total_per_product =
                    $detail->individual_quantity * $detail->bulk_or_roll_quantity;
                return $detail;
            });

        $order->orderDetails = $orderDetails;

        return response()->json([
            'data' => $order
        ]);
    }


    public function inspection($id_purchase_order, $id_product)
    {
        $order = PurchaseOrder::with('products')
            ->where('id_purchase_order', $id_purchase_order)
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Orden no encontrada'], 404);
        }

        return Inertia::render('Views/localM', [
            'id_purchase_order' => $id_purchase_order,
            'id_product'        => $id_product,
        ]);
    }


    /**
     * Guardar MIL STD
     */
    public function addMilStd(Request $request, string $id_purchase_order, string $id_product)
    {

        $validated = $request->validate([
            'c1'               => 'nullable|integer',
            'c2'               => 'nullable|integer',
            'c3'               => 'nullable|integer',
            'inspection_level' => 'required|string',
            'sample_size'      => 'required|integer',
            'sample_acept'     => 'required|integer',
            'sample_reject'    => 'required|integer',
            'aql'              => 'required|numeric',
        ]);

        try {

            $milStd = MilStd::updateOrCreate(
                [
                    'id_purchase_order' => $id_purchase_order,
                    'id_product'        => $id_product,
                ],
                $validated
            );

            return response()->json([
                'message' => 'MilStd guardado con éxito',
                'milStd'  => $milStd
            ], 201);

        } catch (\Exception $e) {

            return Inertia::render('Views/localM', [
                'id_purchase_order' => $id_purchase_order,
                'id_product'        => $id_product,
            ]);
        }
    }


    /**
     * Guardar muestreo local
     */
    public function localSampling(Request $request, string $id_mil_std, string $id_purchase_order, string $id_product)
    {

        $validated = $request->validate([
            'width'             => 'required|numeric',
            'length'            => 'required|numeric',
            'thickness'         => 'required|numeric',
            'seal_resistance'   => 'required|string',
            'color_detachment'  => 'required|string',
            'piece_number'      => 'required|integer',
            'result_lote'       => 'required|string',
            'result_piece'      => 'required|string',
            'observation'       => 'nullable|string',
        ]);

        try {

            $sampling = LocalSampling::updateOrCreate(
                [
                    'id_mil_std' => $id_mil_std,
                    
                    'piece_number' => $validated['piece_number']
                ],
                $validated
            );

            return response()->json([
                'message' => 'LocalSampling guardado con éxito',
                'sampling' => $sampling
            ], 201);

        } catch (\Exception $e) {

            return Inertia::render('Views/localM', [
                'id_purchase_order' => $id_purchase_order,
                'id_product'        => $id_product,
            ]);
        }
    }

    public function saveInspection(Request $request, string $id_purchase_order, string $id_product)
    {

        // 1️⃣ Validar MIL STD
        $milStdData = $request->validate([
            'c1'               => 'nullable|integer',
            'c2'               => 'nullable|integer',
            'c3'               => 'nullable|integer',
            'inspection_level' => 'required|string',
            'sample_size'      => 'required|integer',
            'sample_acept'     => 'required|integer',
            'sample_reject'    => 'required|integer',
            'aql'              => 'required|numeric',
        ]);

        // 2️⃣ Validar Local Sampling
        $samplingData = $request->validate([
            'width'            => 'required|numeric',
            'length'           => 'required|numeric',
            'thickness'        => 'required|numeric',
            'seal_resistance'  => 'required|string',
            'color_detachment' => 'required|string',
            'piece_number'     => 'required|integer',
            'result_lote'      => 'required|string',
            'result_piece'     => 'required|string',
            'observation'      => 'nullable|string',
        ]);

        try {

            // Guardar MIL STD
            $milStd = MilStd::updateOrCreate(
                [
                    'id_purchase_order' => $id_purchase_order,
                    'id_product'        => $id_product,
                ],
                $milStdData
            );

            // Guardar Local Sampling
            LocalSampling::updateOrCreate(
                [
                    'id_mil_std'   => $milStd->id_mil_std,
                    'piece_number' => $samplingData['piece_number']
                ],
                array_merge($samplingData, [
                    'id_mil_std' => $milStd->id_mil_std
                ])
            );

            return response()->json([
                'message' => 'Inspección guardada correctamente'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
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