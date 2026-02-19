<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



class Criterios extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'criterios';
    protected $primaryKey = 'id_criterio';

    protected $fillable = [
        'description',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;
}
