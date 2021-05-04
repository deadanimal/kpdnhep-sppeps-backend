<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{

    protected $table = 'Bidang';
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
