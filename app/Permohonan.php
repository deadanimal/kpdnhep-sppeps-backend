<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permohonan extends Model
{

    protected $table = 'Permohonan';
    const CREATED_AT = 'tarikh_cipta';
    const UPDATED_AT = 'tarikh_kemaskini';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id'
    ];

}
