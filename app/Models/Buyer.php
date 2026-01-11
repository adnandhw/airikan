<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Buyer extends Model
{
    use HasFactory, \App\Traits\HasStringId;
    
    // Properties handled by trait

    // protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'password',
        'province_id',
        'city_id',
        'district_id',
        'village_id',
        'address',
        'postal_code',
        'is_reseller',
        'reseller_status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    protected $appends = ['firstName', 'lastName'];

    public function getFirstNameAttribute()
    {
        return explode(' ', $this->name)[0] ?? '';
    }

    public function getLastNameAttribute()
    {
        $parts = explode(' ', $this->name);
        return isset($parts[1]) ? implode(' ', array_slice($parts, 1)) : '';
    }

    public function setFirstNameAttribute($value)
    {
        $lastName = $this->lastName ?? '';
        $this->attributes['name'] = trim("$value $lastName");
    }

    public function setLastNameAttribute($value)
    {
        $firstName = $this->firstName ?? '';
        $this->attributes['name'] = trim("$firstName $value");
    }

    public function province()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\Province::class, 'province_id', 'code');
    }

    public function city()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\City::class, 'city_id', 'code');
    }

    public function district()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\District::class, 'district_id', 'code');
    }

    public function village()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\Village::class, 'village_id', 'code');
    }
}
