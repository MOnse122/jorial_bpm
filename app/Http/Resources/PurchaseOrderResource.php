<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_purchase_order' => $this->id_purchase_order,
            'folio' => $this->folio,
            'date' => $this->date,
            'status' => $this->status,
            
            'provider' => $this->provider ? [
                'id_provider' => $this->provider->id_provider,
                'name' => $this->provider->name,
            ] : null,

            'details' => $this->orderDetails->map(function ($detail) {
                return [
                    'id_order_detail' => $detail->id_order_detail,
                    'lot' => $detail->lot,
                    'unit_measure' => $detail->unit_measure,
                    'bulk_or_roll_quantity' => $detail->bulk_or_roll_quantity,
                    'individual_quantity' => $detail->individual_quantity,
                    'non_conformity' => $detail->non_conformity,
                    'document_number' => $detail->document_number,
                    'document_type' => $detail->document_type,
                    

                    'product' => $detail->product ? [
                        'id_product' => $detail->product->id_product,
                        'title' => $detail->product->title,
                        'code' => $detail->product->code,
                        'description' => $detail->product->description,
                    ] : null,

                    'plate' => $detail->plate ? [
                        'id_plate' => $detail->plate->id_plate,
                        'plate_number' => $detail->plate->plate_number,
                    ] : null,

                ];
            }),

        ];
    }

}

