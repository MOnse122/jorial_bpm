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


            'provider' => [
                'id_provider' => $this->provider->id_provider,
                'name' => $this->provider->name,
                'state' => $this->provider->state,
            ],

            'products' => $this->products->map(function ($product) {
                return [
                    'id_product' => $product->id_product,
                    'title' => $product->title,
                    'code' => $product->code,
                    'description' => $product->description,
                    'width' => $product->width,
                    'height' => $product->height,
                    'cal' => $product->cal,
                ];
            }),

            'documents' => $this->documents->map(function ($document) {
                return [
                    'id_document' => $document->id_document,
                    'document_type' => $document->document_type,
                    'number' => $document->number,
                    'date' => $document->date,
                ];
            }),
            'order_details' => $this->orderDetails->map(function ($detail) {
                return [
                    'id_order_detail' => $detail->id_order_detail,
                    'lot' => $detail->lot,
                    'unit_measure' => $detail->unit_measure,
                    'bulk_or_roll_quantity' => $detail->bulk_or_roll_quantity,
                    'individual_quantity' => $detail->individual_quantity,
                    'non_conformity' => $detail->non_conformity,
                    'document_number' => $detail->document_number,
                ];
            }),
            'details' => $this->details->map(function ($detail) {
                return [
                    'id_product' => $detail->product->id_product,
                    'title' => $detail->product->title,
                    'unit_measure' => $detail->unit_measure,
                    'bulk_or_roll_quantity' => $detail->bulk_or_roll_quantity,
                    'individual_quantity' => $detail->individual_quantity,
                    'lot' => $detail->lot,
                    'document_type' => $detail->document_type,
                    'number' => $detail->number,
                    'non_conformity' => $detail->non_conformity,
                ];
            }),
        ];
    }
}

