<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ORDR_CCC extends Model
{
    protected $connection = 'sqlsrv_ccc';
    protected $table = 'ORDR';
    
    public function items()
    {
        return $this->hasMany(RDR1_CCC::class, 'DocEntry', 'DocEntry');
    }
}