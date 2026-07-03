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
    public function bdeName()
    {
        return $this->hasOne(OSLP_PBI::class, 'SlpCode', 'SlpCode');
    }
    public function contactName()
    {
        return $this->hasOne(OCPR_PBI::class, 'CntctCode', 'CntctCode');
    }
}
