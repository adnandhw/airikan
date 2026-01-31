<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    
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
        'slug',
        'discount_percentage',
        'discount_duration',
        'discount_start_date',
        'discount_end_date',
        'weight',
    ];

    protected static function booted()
    {
        static::creating(function ($product) {
            if (empty($product->slug)) {
                 $slug = \Illuminate\Support\Str::slug($product->name);
                 // Ensure uniqueness if needed, but for now simple slug
                 $product->slug = $slug;
            }
        });

        static::updating(function ($product) {
            // Only update slug if explicit or empty, usually good to keep stable
            if (empty($product->slug)) {
                 $product->slug = \Illuminate\Support\Str::slug($product->name);
            }
        });
    }

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
