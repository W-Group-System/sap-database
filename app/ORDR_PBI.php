<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ORDR_PBI extends Model
{
    protected $connection = 'sqlsrv_pbi';
    protected $table = 'ORDR';

    public function items()
    {
        return $this->hasMany(RDR1_PBI::class, 'DocEntry', 'DocEntry');
    }
}
