<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestBPM extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'test_bpms';
    protected $primaryKey = 'id_test_bpm';
    protected $fillable = [
        'id_purchase_order',
        'result',
        'observations',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Un Test BPM pertenece a una Orden de Compra
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(
            PurchaseOrder::class,
            'id_purchase_order'
        );
    }


    /**
     * Un Test BPM genera un MIL-STD (solo uno)
     */
    public function milStd()
    {
        return $this->hasOne(
            MilStd::class,
            'id_mil_std'
        );
    }
}
