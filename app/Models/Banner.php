<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;
    
    use HasFactory, \App\Traits\HasStringId;

    // protected $connection = 'mysql';

    protected $fillable = ['image'];
}
