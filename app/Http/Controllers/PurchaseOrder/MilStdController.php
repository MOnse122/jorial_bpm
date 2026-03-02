<?php

namespace App\Http\Controllers\PurchaseOrder;

use App\Http\Controllers\Controller;
use App\Models\OrderDetails;
use App\Models\PurchaseOrder;
use Inertia\Inertia;
use Illuminate\Http\Request;

class MilStdController extends Controller
{
    // Esta función maneja AMBAS vistas: la lista y la inspección individual
    public function show($id_purchase_order, $id_product = null) 
    {
        return Inertia::render('Views/mil-std', [
            'id_purchase_order' => $id_purchase_order,
            'id_product'        => $id_product // Puede ser null, y está bien
        ]);
    }

    public function orderProducts($id_purchase_order)
    {
        // 1. Buscamos la orden
        $order = PurchaseOrder::with('products')
            ->where('id_purchase_order', $id_purchase_order)
            ->first();

        // 2. VALIDACIÓN: Si no existe, devolvemos error en lugar de morir (Error 500)
        if (!$order) {
            return response()->json(['error' => 'Orden no encontrada'], 404);
        }

        // 3. Buscamos los detalles (lotes, etc.)
        $orderDetails = OrderDetails::where('id_purchase_order', $id_purchase_order)->get();
        
        // 4. Inyectamos los detalles al objeto de la orden
        $order->orderDetails = $orderDetails;

        return response()->json([
            'data' => $order
        ]);
    }

        //Buscar la purchase order y asociarla a la Order details
        
        // 1. Buscamos la orden
        public function inspection($id_purchase_order, $id_order_detail)
        {
             $order = PurchaseOrder::with('products')
            ->where('id_purchase_order', $id_purchase_order)
            ->first();

            // 2. VALIDACIÓN: Si no existe, devolvemos error en lugar de morir (Error 500)
            if (!$order) {
                return response()->json(['error' => 'Orden no encontrada'], 404);
            }

            // 3. Buscamos los detalles (lotes, etc.)
            $orderDetails = OrderDetails::where('id_purchase_order', $id_purchase_order)->get();
            
            // 4. Inyectamos los detalles al objeto de la orden
            $order->orderDetails = $orderDetails;

            // return response()->json([
            //     'data' => $order
            // ]);

            return Inertia::render('Views/localM', [
                'id_purchase_order' => $id_purchase_order,
                'id_product'        => $id_order_detail
            ]);
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
