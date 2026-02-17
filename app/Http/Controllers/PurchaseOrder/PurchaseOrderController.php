<?php

namespace App\Http\Controllers\PurchaseOrder;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\OrderDetails;
use App\Models\PlatesModel;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchaseOrder = PurchaseOrder::with([
            'provider',
            'orderDetails.product'
        ])->get();

        return PurchaseOrderResource::collection($purchaseOrder);
    }


    /**
     * Show the form for creating a new resource.
     */

public function store(Request $request)
{
    $request->validate([
        'date' => 'required|date',
        'status' => 'required|in:PENDIENTE,COMPLETADA,CANCELADA',
        'id_provider' => 'required|exists:providers,id_provider',
        'products' => 'required|array|min:1',
        'products.*.id_product' => 'required|exists:products,id_product',
        'plates' => 'nullable|array',
        'plates.*' => 'string|min:6|max:10',

    ]);

    $folio = 'PO-' . time();

    $purchaseOrder = PurchaseOrder::create([
        'folio' => $folio,
        'date' => $request->date,
        'status' => $request->status,
        'id_provider' => $request->id_provider,
    ]);

    if ($request->has('plates')) {
        foreach ($request->plates as $plate) {
            $existingPlate = PlatesModel::where('plate_number', strtoupper($plate))
                ->where('id_provider', $request->id_provider)
                ->first();

            if (!$existingPlate) {
                PlatesModel::create([
                    'plate_number' => strtoupper($plate),
                    'id_provider' => $request->id_provider,
                ]);
            }
        }
    }

    // Crear order_details
    foreach ($request->products as $product) {
        OrderDetails::create([
            'id_purchase_order' => $purchaseOrder->id_purchase_order,
            'id_product' => $product['id_product'],
            'unit_measure' => $product['unit_measure'] ?? null,
            'bulk_or_roll_quantity' => $product['bulk_or_roll_quantity'] ?? 0,
            'individual_quantity' => $product['individual_quantity'] ?? 0,
            'lot' => $product['lot'] ?? null,
            'document_type' => $product['document_type'] ?? null,
            'number' => $product['number'] ?? null,
            'non_conformity' => $product['non_conformity'] ?? false,
        ]);
    }

    return (new PurchaseOrderResource(
        $purchaseOrder->load([
            'provider.plates',       // placas del proveedor
            'orderDetails.product',  // productos
        ])
    ))->response()->setStatusCode(201);
}






    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $purchaseOrder = PurchaseOrder::with([
            'provider',
            'orderDetails.product'
        ])->findOrFail($id);

        return new PurchaseOrderResource($purchaseOrder);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->update($request->all());

        return new PurchaseOrderResource($purchaseOrder);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->delete();

        return response()->json(['message' => 'Orden de compra eliminada']);

    }

    public function validateArray(Request $request)
    {
        $request->validate([
            'provider_id' => 'required|exists:providers,id_provider',
            'order_number' => 'required|string',
            'date' => 'required|date',

            'details' => 'required|array|min:1',

            'details.*.id_product' => 'required|exists:products,id_product',
            'details.*.unit_measure' => 'required|string',
            'details.*.bulk_or_roll_quantity' => 'required|integer',
            'details.*.individual_quantity' => 'required|integer',
            'details.*.lot' => 'required|string',
            'details.*.document_type' => 'required|in:FACTURA,REMISION,OTRO',
            'details.*.number' => 'required|string',
            'details.*.non_conformity' => 'required|boolean',
        ]);


        return response()->json(['message' => 'Datos validados correctamente', 'data' =>  $request->all()]);
    }
    
}
