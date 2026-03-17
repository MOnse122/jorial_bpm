<?php

namespace App\Http\Controllers;
use App\Http\Resources\ReportResource;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;


class PurchaseOrderPdfController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id_purchase_order)
    {
        $purchaseOrder = PurchaseOrder::with([
            'provider',
            'orderDetails',
            'orderDetails.product',
            'orderDetails.plate',
            'test_bpms.users',
            'mil_stds',
            'local_sampling.mil_std',
            'test_bpms.details.criterio_detail'
        ])->findOrFail($id_purchase_order);

        return new ReportResource($purchaseOrder);
    }

    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
