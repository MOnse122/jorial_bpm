<?php

namespace App\Http\Controllers\PurchaseOrder;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Http\Resources\PurchaseOrderResource;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['provider', 'products', 'documents', 'orderDetails'])->get();
        return PurchaseOrderResource::collection($purchaseOrders);
    }

    /**
     * Show the form for creating a new resource.
     */

    public function store(Request $request)
    {
        return new PurchaseOrderResource(PurchaseOrder::create($request->all())
        ->response
        ->setStatusCode(201)
        
    );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $purchaseOrder = PurchaseOrder::with(['provider', 'products', 'documents', 'orderDetails'])->findOrFail($id);
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
