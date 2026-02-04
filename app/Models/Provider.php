<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Provider extends Model
{
    use HasFactory;

    protected $table = 'providers';
    protected $primaryKey = 'id_provider';

    protected $fillable = [
        'name',
        'plates',
        'state',
    ];

    public $timestamps = true;

    public function purchaseOrders()
    {
        return $this->hasMany(
            PurchaseOrder::class, 
            'id_provider', 
        );
    }

    public function products()
    {
        return $this->hasMany(
            Product::class, 
            'id_provider', 
        );
    }


}
