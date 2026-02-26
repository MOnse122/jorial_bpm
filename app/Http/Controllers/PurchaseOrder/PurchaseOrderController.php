<?php

namespace App\Http\Controllers\PurchaseOrder;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\OrderDetails;
use App\Models\PlatesModel;
use App\Models\OrderProduct;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;



class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $purchaseOrders = PurchaseOrder::with('provider')->get();

        if ($request->wantsJson()) {
            return PurchaseOrderResource::collection($purchaseOrders);
        }

        return Inertia::render('Views/PUView', [
            'orders' => PurchaseOrderResource::collection($purchaseOrders)
        ]);
    }
    

    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request){
        $request->validate([
            'date' => 'required|date',
            'status' => 'required|in:PENDIENTE,COMPLETADA,CANCELADA,PENDIENTE1,PENDIENTE2',
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
                'message' => 'Error al crear la orden',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function test(PurchaseOrder $purchase_order)
    {
        return Inertia::render('Views/Test', [
            'id_purchase_order' => $purchase_order->id_purchase_order
        ]);
    }
    /**
     * Display the specified resource.
     */
    public function show($id) 
    {
        $purchaseOrder = PurchaseOrder::with([
            'provider', 
            'orderDetails.product', 
            'orderDetails.plate',
        ])->findOrFail($id);

        // Si la petición viene de Axios/AJAX, devolvemos JSON
        if (request()->wantsJson()) {
            return new PurchaseOrderResource($purchaseOrder);
        }

        // Si es una carga normal del navegador, devolvemos la vista de edición
        return Inertia::render('Views/edit_PO', [
            'order' => new PurchaseOrderResource($purchaseOrder)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {
        $request->validate([
            'date' => 'required|date',
            'id_provider' => 'required|exists:providers,id_provider',
            'products' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);
            
            $purchaseOrder->update($request->only(['date', 'status', 'id_provider']));

            $idPlate = null;
            if (!empty($request->plates)) {
                $plate = PlatesModel::firstOrCreate([
                    'plate_number' => strtoupper($request->plates[0]),
                    'id_provider' => $request->id_provider,
                ]);
                $idPlate = $plate->id_plate;
            }

            // 3. Sincronización inteligente de productos
            $incomingProductIds = collect($request->products)->pluck('id_product')->toArray();

            // Eliminamos de la BD solo los productos que ya NO vienen en el request
            OrderDetails::where('id_purchase_order', $id)
                ->whereNotIn('id_product', $incomingProductIds)
                ->delete();
                
            OrderProduct::where('id_purchase_order', $id)
                ->whereNotIn('id_product', $incomingProductIds)
                ->delete();

            foreach ($request->products as $product) {
                // Buscamos si el producto ya existe en la orden
                $detail = OrderDetails::updateOrCreate(
                    [
                        'id_purchase_order' => $id,
                        'id_product' => $product['id_product']
                    ],
                    [
                        'id_plate' => $idPlate,
                        'unit_measure' => $product['unit_measure'] ?? 'pz',
                        'bulk_or_roll_quantity' => $product['bulk_or_roll_quantity'] ?? 0,
                        'individual_quantity' => $product['individual_quantity'] ?? 0,
                        'lot' => $product['lot'] ?? null,
                        'document_number' => $product['document_number'] ?? null,
                        'document_type' => $product['document_type'] ?? 'FACTURA',
                        'non_conformity' => $product['non_conformity'] ?? false,
                    ]
                );

                OrderProduct::updateOrCreate(
                    [
                        'id_purchase_order' => $id,
                        'id_product' => $product['id_product']
                    ],
                    [
                        'document_number' => $product['document_number'] ?? null,
                        'document_type' => $product['document_type'] ?? 'FACTURA',
                    ]
                );
            }

            DB::commit();
            return redirect()->back()->with('message', 'Orden actualizada correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function destroy(string $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        $purchaseOrderStatus = $purchaseOrder->status;
        
        if($purchaseOrderStatus == 'PENDIENTE'){
            $purchaseOrder->delete();
            return response()->json(['message' => 'Orden de compra eliminada']);
        }

        return response()->json(['message' => 'No se puede eliminar la orden de compra, ya que tiene datos relacionados']);

    }


    public function edit($id)
    {
        $order = PurchaseOrder::with(['provider', 'plates', 'details.product'])->findOrFail($id);
        
        return inertia('Views/edit_PO', [
            'order' => $order
        ]);
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
