<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PurchaseOrder;


class OrderDetails extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'order_details';
    protected $primaryKey = 'id_order_detail';
    protected $fillable = [
        'id_purchase_order',
        'lot',
        'unit_measure',
        'bulk_or_roll_quantity',
        'individual_quantity',
        'non_conformity',
        'document_number',
        'id_document',
        'id_product',
    ];
    protected $casts = [
        'non_conformity' => 'boolean',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(
            PurchaseOrder::class, 
            'id_purchase_order');
    }
    public function product()
    {
        return $this->belongsTo(
            Product::class,
            'id_product',
            'id_product'
        );
    }

        


}
