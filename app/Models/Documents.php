<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Documents extends Model
{
    use HasFactory;
    protected $table = 'documents';
    protected $primaryKey = 'id_document';
    protected $fillable = [
        'document_type',
        'number',
        'date',
        'id_purchase_order',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(
            PurchaseOrder::class, 
            'id_purchase_order', 
        );
    }
}
