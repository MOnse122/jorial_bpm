<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;
    protected $table = 'order_products';
    protected $primaryKey = 'id_order_product';
    protected $fillable = [
        'id_purchase_order',
        'id_product',
        'id_document',
        'document_type',

    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'id_purchase_order', 'id_purchase_order');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product', 'id_product');
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'id_document', 'id_document');
    }
}
