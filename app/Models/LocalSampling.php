<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;


class LocalSampling extends Model
{
    use HasFactory, SoftDeletes;

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
