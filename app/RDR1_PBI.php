<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RDR1_PBI extends Model
{
    protected $connection = 'sqlsrv_pbi';
    protected $table = 'RDR1';

    public function order()
    {
        return $this->belongsTo(ORDR_PBI::class, 'DocEntry', 'DocEntry');
    }
}
