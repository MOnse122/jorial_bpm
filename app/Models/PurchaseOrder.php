<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;



class PurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'purchase_orders';
    protected $primaryKey = 'id_purchase_order';

    protected $fillable = [
        'folio',
        'date',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(
            User::class, 
            'id_user', 
        );
    }

    public function provider()
    {
        return $this->belongsTo(
            Provider::class, 
            'id_provider', 
        );
    }

    public function product()
    {
        return $this->belongsTo(
            Product::class, 
            'id_product', 
        );
    }

    public function checkBpm()
    {
        return $this->hasMany(
            CheckBpm::class, 
            'id_check_bpm', 
        );
    }

    public function documents()
    {
        return $this->hasMany(
            Documents::class, 
            'id_document', 
        );
    }


}
