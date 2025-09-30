<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    //
    protected $fillable = [
        'from_currency_id',
        'to_currency_id',
        'updated_at',
        'rate',
    ];
}
