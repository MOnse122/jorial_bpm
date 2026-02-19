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
        'document_type',
        'id_product',
        'id_plate',
        'id_order_product',
    ];

    protected $casts = [
        'non_conformity' => 'boolean',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(
            PurchaseOrder::class,
            'id_purchase_order',
            'id_purchase_order'
        );
    }

    public function product()
    {
        return $this->belongsTo(
            Product::class,
            'id_product',
            'id_product'
        );
    }

    public function plate()
    {
        return $this->belongsTo(
            PlatesModel::class,
            'id_plate',
            'id_plate'
        );
    }

    public function orderProduct()
    {
        return $this->belongsTo(
            OrderProduct::class,
            'id_order_product',
            'id_order_product'
        );
    }


}
