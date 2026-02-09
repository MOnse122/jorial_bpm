<?php

namespace App\Http\Controllers\PurchaseOrder;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(['message' => 'Listado de órdenes de compra']);
    }

    /**
     * Show the form for creating a new resource.
     */

    public function store(Request $request)
    {
        $request->validate([
            'unit_measure' => 'required|string|max:255',
            'bulk_or_roll_quantity' => 'required|integer',
            'individual_quantity' => 'required|integer',
            'document_number' => 'required|string|max:255',
            'non_conformity' => 'required|boolean',
            'lot' => 'required|string|max:255',
            'document_type' => 'required|in:FACTURA,REMISION,OTRO',
            'number' => 'required|string|max:255',
            
        ]);

         $purchaseOrder = PurchaseOrder::create([
            'unit_measure' => $request->unit_measure,
            'bulk_or_roll_quantity' => $request->bulk_or_roll_quantity,
            'individual_quantity' => $request->individual_quantity,
            'document_number' => $request->document_number,
            'non_conformity' => $request->non_conformity,
            'lot' => $request->lot,
            'document_type' => $request->document_type,
            'number' => $request->number,

        ]);
        return response()->json($purchaseOrder, 201);
    

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json(['message' => "Detalles de la orden de compra con ID: $id"]);
        
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'unit_measure' => 'required|string|max:255',
            'bulk_or_roll_quantity' => 'required|integer',
            'individual_quantity' => 'required|integer',
            'document_number' => 'required|string|max:255',
            'non_conformity' => 'required|boolean',
            'lot' => 'required|string|max:255',
            'document_type' => 'required|in:FACTURA,REMISION,OTRO',
            'number' => 'required|string|max:255',
        ]);

        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->update([
            'unit_measure' => $request->unit_measure,
            'bulk_or_roll_quantity' => $request->bulk_or_roll_quantity,
            'individual_quantity' => $request->individual_quantity,
            'document_number' => $request->document_number,
            'non_conformity' => $request->non_conformity,
            'lot' => $request->lot,
            'document_type' => $request->document_type,
            'number' => $request->number,
        ]);

        return response()->json($purchaseOrder, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->delete();

        return response()->json(['message' => 'Orden de compra eliminada correctamente.']);
    }
    
}
