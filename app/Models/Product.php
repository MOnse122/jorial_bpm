<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';
    protected $primaryKey = 'id_product';

    protected $fillable = [
        'title',
        'code',
        'description',
        'width',
        'height',
        'cal',
        'state',
    ];


    public $timestamps = true;


    public function purchaseOrder()
    {
        return $this->hasMany(
            PurchaseOrder::class, 
            'id_purchase_order', 
        );
    }
     
    //SCOPES
    public function scopeActive($query)
    {
        return $query->where('state', 'ACTIVO');
    }

    public function scopeInactive($query)
    {
        return $query->where('state', 'INACTIVO');
    }

    public function scopeSearch($query, $value = null)
    {
        if (!$value) return $query;

        return $query->where(function ($q) use ($value) {
            $q->where('title', 'like', "%{$value}%")
            ->orWhere('code', 'like', "%{$value}%")
            ->orWhere('description', 'like', "%{$value}%")
            ->orWhere('width', 'like', "%{$value}%")
            ->orWhere('height', 'like', "%{$value}%")
            ->orWhere('cal', 'like', "%{$value}%")
            ->orWhere('state', 'like', "%{$value}%");
        });
    }

    public function orders()
    {
        return $this->belongsToMany(
            PurchaseOrder::class,
            'order_product',
            'id_product',
            'id_purchase_order'
        )->withPivot(['quantity', 'unit_measure', 'non_conformity'])
        ->withTimestamps();
    }




}
