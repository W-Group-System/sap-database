<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RDR1 extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'RDR1';

    public function order()
    {
        return $this->belongsTo(ORDR::class, 'DocEntry', 'DocEntry');
    }
}
