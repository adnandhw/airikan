<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;

    use HasFactory, \App\Traits\HasStringId;
    
    // protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'slug',
        'image',
        'types',
    ];

    protected $casts = [
        // 'types' => 'array', // Removed to allow custom comma-separated logic
    ];

    /**
     * Accessor: Convert comma-separated string to Array
     */
    public function getTypesAttribute($value)
    {
        if (is_null($value)) return [];
        
        // If it's already an array (unlikely from DB text, but possible in runtime)
        if (is_array($value)) return $value;

        // Try decoding if it happens to be valid JSON (legacy support)
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Otherwise explode by comma
        // Remove empty values
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    /**
     * Mutator: Convert Array to comma-separated string
     */
    public function setTypesAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['types'] = implode(',', $value);
        } elseif (is_string($value)) {
             // Try to see if it is JSON
             $decoded = json_decode($value, true);
             if (is_array($decoded)) {
                 $this->attributes['types'] = implode(',', $decoded);
             } else {
                 $this->attributes['types'] = $value;
             }
        } else {
            $this->attributes['types'] = $value;
        }
    }

    /**
     * Relasi: Category -> Products
     */
    public function products()
    {
        return $this->hasMany(
            Product::class,
            'category_id',
            'id'
        );
    }

    /**
     * Helper image url
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? url('storage/' . $this->image)
            : null;
    }
}
