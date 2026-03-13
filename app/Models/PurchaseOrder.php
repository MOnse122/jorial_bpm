<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


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
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'date' => 'date',
    ];

    public $timestamps = true;

    // --- RELATIONSHIPS ---

    /**
     * User who created the order
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * 🔹 Fixed: Only one provider method (removed duplicate)
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'id_provider', 'id_provider');
    }

    /**
     * Relationship with OrderDetails
     */
    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetails::class, 'id_purchase_order', 'id_purchase_order');
    }

    /**
     * Relationship with BPM Tests
     */
    public function test_bpms(): HasMany
    {
        return $this->hasMany(CheckBpm::class, 'id_purchase_order', 'id_purchase_order');
    }

    /**
     * Relationship with Mil Stds
     */


    public function mil_stds(): HasMany
    {
        return $this->hasMany(MilStd::class, 'id_purchase_order', 'id_purchase_order');
    }

    /**
     * Relationship with Local Sampling
     */
/**
 * Si tu columna en la tabla 'local_sampling' se llama diferente
 * (por ejemplo: 'purchase_order_id' o 'id_orden'), cámbiala aquí.
 */
// En PurchaseOrder.php
    public function local_sampling()
    {
        return $this->hasManyThrough(
            LocalSampling::class,
            OrderDetails::class,
            'id_order_detail', // Llave foránea en OrderDetails
            'id_sampling',   // Llave foránea en LocalSampling (ajusta según tu BD)
            'id_purchase_order', // Llave local en PurchaseOrder
            'id_order_detail'    // Llave local en OrderDetails
        );
    }
    /**
     * Pivot relationship for products
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'order_products',
            'id_purchase_order',
            'id_product'
        );
    }
}