<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    
    use HasFactory, \App\Traits\HasStringId;

    // protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'description',
        'size',
        'category_id',
        'type',
        'price',
        'image',
        'stock',
        'discount_percentage',
        'discount_duration',
        'discount_start_date',
        'discount_end_date',
    ];

    protected $casts = [
        '_id' => 'string',
    ];

    public $timestamps = true;

    /**
     * Relasi: Product -> Category
     */
    public function category()
    {
        return $this->belongsTo(
            Category::class,
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
