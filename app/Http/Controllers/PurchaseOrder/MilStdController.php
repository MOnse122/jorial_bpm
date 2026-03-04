<?php

namespace App\Http\Controllers\PurchaseOrder;

use App\Http\Controllers\Controller;
use App\Models\OrderDetails;
use App\Models\PurchaseOrder;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



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
            // 1. Buscamos la orden con sus productos relacionados
            $order = PurchaseOrder::with('products')
                ->where('id_purchase_order', $id_purchase_order)
                ->first();

            if (!$order) {
                return response()->json(['error' => 'Orden no encontrada'], 404);
            }

            // 2. Buscamos los detalles y añadimos el cálculo dinámicamente
            $orderDetails = OrderDetails::where('id_purchase_order', $id_purchase_order)
                ->get()
                ->map(function ($detail) {
                    // Creamos un atributo nuevo con el resultado de la multiplicación
                    $detail->total_per_product = $detail->individual_quantity * $detail->bulk_or_roll_quantity;
                    return $detail;
                });

            
            // 3. Inyectamos los detalles calculados
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

            if (!$order) {
                return response()->json(['error' => 'Orden no encontrada'], 404);
            }

            return Inertia::render('Views/localM', [
                'id_purchase_order'      => $id_purchase_order,
                'id_product'             => $id_order_detail,
            ]);
        }

        // //CANTIDAD COMPLETA DE PRODUCTOS EN LA ORDEN
        // public function totalProducts($id_order_detail)
        // {
        //     $order = OrderDetails::where('id_order_detail', $id_order_detail)
        //     ->sum(DB::raw('individual_quantity * bulk_or_roll_quantity'));
                
        //     return response()->json([
        //         'total_products' => $order
                
        //     ]);

        //     try {
        //         $order = OrderDetails::where('id_order_detail', $id_order_detail)
        //         ->sum(DB::raw('individual_quantity * bulk_or_roll_quantity'));
        //     } catch (\Throwable $th) {
        //         return response()->json([
        //             'error' => 'Error al calcular la cantidad total de productos'
        //         ], 500);
        //     }

        //     return response()->json([
        //         'total_products' => $order
        //     ]);
           
        // }

       



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
