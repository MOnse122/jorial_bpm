<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $primaryKey = 'id_product';

    protected $fillable = [
        'title',
        'description',
        'width',
        'height',
        'cal',
        'estatus',
        'id_provider',
    ];


    public $timestamps = true;
    
    public function provider()
    {
        return $this->belongsTo(
            Provider::class, 
            'id_product', 
        );
    }

    public function purchaseOrders()
    {
        return $this->hasMany(
            PurchaseOrder::class, 
            'id_product', 
        );
    }
}
