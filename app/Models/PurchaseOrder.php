<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchase_order';
    protected $primaryKey = 'id_purchase_order';

    protected $fillable = [
        'folio',
        'date',
        'status',
        'id_provider',
        'id_product',
        'id_order_product',
        'id_user',
        'id_purchase_order',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

    // Relación con usuario
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // Relación con proveedor
    public function provider()
    {
        return $this->belongsTo(Provider::class, 'id_provider');
    }

    // Relación con productos de la orden
    public function orderDetails()
    {
        return $this->hasMany(OrderDetails::class, 'id_purchase_order');
    }

    // Relación con BPM
    public function checkBpm()
    {
        return $this->hasMany(CheckBpm::class, 'id_purchase_order');
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class, 'id_purchase_order');
    }
    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'order_products',
            'id_order_product',
            'id_purchase_order',
            'id_product'
        )->withPivot(['quantity', 'unit_measure', 'non_conformity'])
        ->withTimestamps();
    }


}
