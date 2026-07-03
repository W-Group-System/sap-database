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
    public function bdeName()
    {
        return $this->hasOne(OSLP_CCC::class, 'SlpCode', 'SlpCode');
    }
    public function contactName()
    {
        return $this->hasOne(OCPR_CCC::class, 'CntctCode', 'CntctCode');
    }
}