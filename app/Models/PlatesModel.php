<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatesModel extends Model
{
    use HasFactory;

    protected $table = 'plates';
    protected $primaryKey = 'id_plate';

    protected $fillable = [
        'plate_number',
        'id_provider'
    ];

    public function provider()
    {
        return $this->belongsTo(
            Provider::class,
            'id_provider',
            'id_provider'
        );
    }

    
}