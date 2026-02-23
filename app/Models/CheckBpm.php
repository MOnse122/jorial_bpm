<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class CheckBpm extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'test_bpms';
    protected $primaryKey = 'id_test_bpm';

    protected $fillable = [
        'id_purchase_order',
        'users_id',
        'name_provider',
        'observations',
        'total_score',
        'percentage',
        'result'
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;



    public function user()
    {
        return $this->hasOne(
            User::class, 
            'users_id', 
        );
        
    }
    // Relación con detalle de evaluación

    public function details()
    {
        return $this->hasMany(TestBpmDetail::class, 'id_test_bpm');
    }

    public function purchaseOrder()
    {
        return $this->hasOne(
            PurchaseOrder::class, 
            'id_purchase_order', 
        );
    }
}
