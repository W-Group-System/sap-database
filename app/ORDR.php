<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ORDR extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'ORDR';

    public function items()
    {
        return $this->hasMany(RDR1::class, 'DocEntry', 'DocEntry');
    }
}
