<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RDR1_CCC extends Model
{
    protected $connection = 'sqlsrv_ccc';
    protected $table = 'RDR1';

    public function order()
    {
        return $this->belongsTo(ORDR_CCC::class, 'DocEntry', 'DocEntry');
    }
}
