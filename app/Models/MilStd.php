<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class MilStd extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mil_stds';
    protected $primaryKey = 'id_mil_std';

    protected $fillable = [
        'id_test_bpm',
        'inspection_level',
        'sample_size',
        'result',
        'material_disposition',
        'aql',
        'accept_reject'

    ];

    protected $casts = [
        'date' => 'date',
    ];


    public function testBpm()
    {
        return $this->belongsTo(
            TestBPM::class,
            'id_test_bpm'
        );
    }
}
