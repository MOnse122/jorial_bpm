<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CheckBpm extends Model
{
    use HasFactory;

    protected $table = 'check_bpms';
    protected $primaryKey = 'id_check_bpm';

    protected $fillable = [
        'id_check_bpm',
        'id_purchase_order',
        'result',
        'observations',
        'date',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

    public function purchaseOrder()
    {
        return $this->hasOne(
            PurchaseOrder::class, 
            'id_purchase_order', 
        );
    }
    
    public function questions()
    {
        return $this->hasOne(
            TestBPM::class, 
            'id_test_bpm', 
        );
    }

}
