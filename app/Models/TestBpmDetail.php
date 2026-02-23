<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestBpmDetail extends Model
{
    protected $table = 'test_bpm_details';
    protected $primaryKey = 'id_test_bpm_detail';

    protected $fillable = [
        'id_test_bpm',
        'id_criterio_detail',
        'score',
    ];

    // 🔹 Relación con evaluación principal

    public function evaluation()
    {
        return $this->belongsTo(CheckBpm::class, 'id_test_bpm');
    }

    //  Relación con criterio
    public function criterioDetail()
    {
        return $this->belongsTo(CriteriosDetails::class, 'id_criterio_detail');
    }
}