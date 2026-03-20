<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id_purchase_order' => $this->id_purchase_order,
            'folio'             => $this->folio,
            'date'              => $this->date,
            'status'            => $this->status,
            'details'           =>$this->details,

            'provider' => [
                'id_provider' => $this->provider->id_provider ?? null,
                'name'        => $this->provider->name ?? null,
            ],

            'plates' => $this->whenLoaded('orderDetails', function () {
                return $this->orderDetails
                    ->pluck('plate')
                    ->filter()
                    ->unique('id_plate')
                    ->values()
                    ->map(fn($plate) => [
                        'id_plate'     => $plate->id_plate,
                        'plate_number' => $plate->plate_number,
                    ]);
            }),

            'details' => $this->whenLoaded('orderDetails', function () {
                return $this->orderDetails->map(fn($detail) => [
                    'id_order_detail' => $detail->id_order_detail,
                    'lot'             => $detail->lot,
                    'unit_measure'    => $detail->unit_measure,
                  
                ]);
            }),

            'test_bpm' => $this->whenLoaded('test_bpms', function () {
                return $this->test_bpms->map(fn($test) => [
                    'id_test_bpm'   => $test->id_test_bpm,
                    'name_provider' => $test->name_provider,
                    'result'        => $test->result,
                    
                    // Acceso al objeto usuario
                    'user' => $test->users ? [
                        'id'   => $test->users->id, 
                        'name' => $test->users->name,
                    ] : null,
                        
                    'percentage'    => $test->percentage,
                    'observations'  => $test-> observations,
                    'details'       => $test->details->map(fn($d) => [
                        'score'  => $d->score,
                        'sector' => $d->criterio_detail->sector ?? null,

                    ]),
                ]);
            }),

            'products' => $this->whenLoaded('orderDetails', function () {
                return $this->orderDetails
                    ->map(function ($detail) {

                        $product = $detail->product;

                        if (!$product) return null;

                        $milStd = $this->mil_stds
                            ->where('id_product', $product->id_product)
                            ->first();

                        return [
                            'id_product' => $product->id_product,
                            'title'      => $product->title,
                            'code'       => $product->code,
                            'width'      => $product->width,
                            'height'     => $product->height,
                            'cal'        => $product->cal,

                            // 🔥 AQUÍ YA FUNCIONA EL LOTE
                            'lot' => $detail->lot,
                            'unit_measure' => $detail->unit_measure,

                            'details_mil_std' => $milStd ? [
                                'id_mil_std'        => $milStd->id_mil_std,
                                'id_purchase_order' => $milStd->id_purchase_order,
                                'id_product'        => $milStd->id_product,
                                
                                'c1'               => $milStd->c1,
                                'c2'               => $milStd->c2,
                                'c3'               => $milStd->c3,
                                'inspection_level' => $milStd->inspection_level,
                                'sample_size'      => $milStd->sample_size,
                                'sample_acept'     => $milStd->sample_acept,
                                'sample_reject'    => $milStd->sample_reject,
                                'aql'              => $milStd->aql,

                                'samplings' => $milStd->samplings->map(fn($sampling) => [
                                    'id_sampling'      => $sampling->id_sampling,
                                    'width'            => $sampling->width,
                                    'length'           => $sampling->length,
                                    'thickness'        => $sampling->thickness,
                                    'seal_resistance'  => $sampling->seal_resistance,
                                    'color_detachment' => $sampling->color_detachment,
                                ]),
                            ] : null,
                        ];
                    })
                    ->filter() // quita nulls
                    ->values();
            }),

            
        ];
    }
}