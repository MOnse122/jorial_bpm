<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\OrderDetails;

class MilStd extends Model
{
    use HasFactory;

    protected $table = 'mil_stds';
    protected $primaryKey = 'id_mil_std';

    protected $fillable = [
        'id_purchase_order',
        'id_product',
        'c1',
        'c2',
        'c3',
        'inspection_level',
        'sample_size',
        'sample_acept',
        'sample_reject',
        
        'aql',
    ];

    public function orderDetail()
    {
        return $this->belongsTo(
            OrderDetails::class,
            'id_order_detail'
        );
    }

    public function samplings()
    {
        return $this->hasMany(
            LocalSampling::class,
            'id_mil_std'
        );
    }
}