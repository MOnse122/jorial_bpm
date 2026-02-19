<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class CheckBpm extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'check_bpms';
    protected $primaryKey = 'id_check_bpm';

    protected $fillable = [
        'observations',
        'name_provider',
        'id_purchase_order',
        'users_id',
        'id_evaluation',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

    public function evaluation()
    {
        return $this->hasOne(
            Evaluation::class, 
            'id_evaluation', 
        );
    }

    public function user()
    {
        return $this->hasOne(
            User::class, 
            'users_id', 
        );
    }

    public function purchaseOrder()
    {
        return $this->hasOne(
            PurchaseOrder::class, 
            'id_purchase_order', 
        );
    }
}
