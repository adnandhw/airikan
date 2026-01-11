<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reseller extends Model
{
    use HasFactory;
    
    use HasFactory, \App\Traits\HasStringId;

    // protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'businessName',
        'email',
        'phone',
        'address',
    ];
}
