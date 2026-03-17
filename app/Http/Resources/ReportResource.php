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
                        'id'   => $test->users->id, // Usamos 'id' de la tabla users
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
                    ->pluck('product')
                    ->filter()
                    ->unique('id_product')
                    ->values()
                    ->map(fn($product) => [
                        'id_product' => $product->id_product,
                        'title'      => $product->title,
                        'code'       => $product->code,

                        // Extraemos MIL STD de la relación de la orden
                        'details_mil_std' => $this->mil_stds->where('id_product', $product->id_product)->first() ? [
                            'id_mil_std'       => $this->mil_stds->first()->id_mil_std,
                            'id_purchase_order' => $this->mil_stds->first()->id_purchase_order,
                            'id_product'        => $this->mil_stds->first()->id_product,
                            'c1'               => $this->mil_stds->first()->c1,
                            'c2'               => $this->mil_stds->first()->c2,
                            'c3'               => $this->mil_stds->first()->c3,
                            'inspection_level' => $this->mil_stds->first()->inspection_level,
                            'sample_size'      => $this->mil_stds->first()->sample_size,
                            'sample_acept'     => $this->mil_stds->first()->sample_acept,
                            'sample_reject'    => $this->mil_stds->first()->sample_reject,
                            'aql'              => $this->mil_stds->first()->aql,

                            'samplings' => $this->mil_stds->first()->samplings->map(fn($sampling) => [
                                'id_sampling' => $sampling->id_sampling,
                                'width' => $sampling->width,
                                'length' => $sampling->length,
                                'thickness' => $sampling->thickness,
                                'seal_resistance' => $sampling->seal_resistance,
                                'color_detachment' => $sampling->color_detachment,
                            ]),
                        ] : null,


                    ]);
            }),

            
        ];
    }
}