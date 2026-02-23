<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CriteriosDetails extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'criterios_details';
    protected $primaryKey = 'id_criterio_detail';

    protected $fillable = [
        'id_criterio',
        'sector',
        
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function criterio()
    {
        return $this->belongsTo(
            Criterios::class,
            'id_criterio',
            'id_criterio'
        );
    }

    public $timestamps = true;
}
