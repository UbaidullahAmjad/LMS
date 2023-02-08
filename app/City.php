<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'name',
        'state_id',
        'country_id'
    ];

    public function State() {
        return $this->belongsTo('\App\State')->withDefault();
    }

    public function Country() {
        return $this->belongsTo('\App\Country')->withDefault();
    }
}
