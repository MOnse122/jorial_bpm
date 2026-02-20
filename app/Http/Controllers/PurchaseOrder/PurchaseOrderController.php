<?php

namespace App\Http\Controllers\PurchaseOrder;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\OrderDetails;
use App\Models\PlatesModel;
use App\Models\OrderProduct;
use App\Models\Document;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchaseOrder = PurchaseOrder::with([
            'provider',
            'orderDetails.product',
            'orderDetails.plate',
        ])->get();

        return PurchaseOrderResource::collection($purchaseOrder);
    }

    /**
     * Show the form for creating a new resource.
     */
public function store(Request $request){
    $request->validate([
        'date' => 'required|date',
        'status' => 'required|in:PENDIENTE,COMPLETADA,CANCELADA',
        'id_provider' => 'required|exists:providers,id_provider',
        'products' => 'required|array|min:1',
        'products.*.id_product' => 'required|exists:products,id_product',
        'plates' => 'nullable|array',
        'plates.*' => 'string|min:6|max:10',
        'document_type' => 'nullable|string',
        'document_number' => 'nullable|string',
    ]);

    DB::beginTransaction();

    try {
        $folio = 'PO-' . time();

        // 🔹 Crear orden
        $purchaseOrder = PurchaseOrder::create([
            'folio' => $folio,
            'date' => $request->date,
            'status' => $request->status,
            'id_provider' => $request->id_provider,
        ]);

        $idPlate = null;

        // 🔹 Manejo de placa
        if ($request->has('plates') && count($request->plates) > 0) {
            $plateNumber = strtoupper($request->plates[0]);
            $plate = PlatesModel::firstOrCreate([
                'plate_number' => $plateNumber,
                'id_provider' => $request->id_provider,
            ]);
            $idPlate = $plate->id_plate;
        }

        //  Insertar productos
        foreach ($request->products as $product) {
            // Aseguramos que cada producto tenga document_number y document_type
            $documentNumber = $request->document_number ?? $product['document_number'] ?? null;
            $documentType = $request->document_type ?? $product['document_type'] ?? null;

            //  OrderDetails
            OrderDetails::create([
                'id_purchase_order' => $purchaseOrder->id_purchase_order,
                'id_product' => $product['id_product'],
                'id_plate' => $idPlate,
                'id_document' => $product['id_document'] ?? null,
                'unit_measure' => $product['unit_measure'] ?? null,
                'document_number' => $documentNumber,
                'document_type' => $documentType,
                'bulk_or_roll_quantity' => $product['bulk_or_roll_quantity'] ?? 0,
                'individual_quantity' => $product['individual_quantity'] ?? 0,
                'lot' => $product['lot'] ?? null,
                'non_conformity' => $product['non_conformity'] ?? false,
            ]);

            //  OrderProducts
            OrderProduct::create([
                'id_purchase_order' => $purchaseOrder->id_purchase_order,
                'id_product' => $product['id_product'],
                'document_number' => $documentNumber, // ⬅ siempre document_number
                'document_type' => $documentType,     // ⬅ siempre document_type
                'id_document' => $product['id_document'] ?? null,
                
            ]);
        }

        DB::commit();

        return (new PurchaseOrderResource(
            $purchaseOrder->load([
                'provider',
                'orderDetails',
                'orderDetails.product',
                'orderDetails.plate',
                'orderProducts',
                'orderProducts.product',
                'orderProducts.document'
            ])
        ))->response()->setStatusCode(201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'error' => 'Error al guardar',
            'details' => $e->getMessage()
        ], 500);
    }
}




    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $purchaseOrder = PurchaseOrder::with([
            'provider',
            'orderDetails.product',
            'orderDetails.plate',
        ])->findOrFail($id);

        return new PurchaseOrderResource($purchaseOrder);

        return Inertia::render('Test', [
        'id_purchase_order' => $id
    ]);
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
            'details.*.non_conformity' => 'required|boolean',
        ]);


        return response()->json(['message' => 'Datos validados correctamente', 'data' =>  $request->all()]);
    }
    
}
