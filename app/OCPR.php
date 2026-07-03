<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OCPR extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'OCPR';
}
