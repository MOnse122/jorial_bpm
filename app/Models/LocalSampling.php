<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;



class LocalSampling extends Model
{
    use HasFactory;

    protected $table = 'local_sampling';
    protected $primaryKey = 'id_sampling';

    protected $fillable = [
        'id_mil_std',
        'width',
        'length',
        'thickness',
        'seal_resistance',
        'color_detachment',
        'piece_number',
        'result',
        'observation',
    ];

    public function milStd()
    {
        return $this->belongsTo(
            MilStd::class,
            'id_mil_std'
        );
    }
}
