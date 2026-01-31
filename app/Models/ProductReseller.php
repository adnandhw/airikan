<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReseller extends Model
{
    use HasFactory;

    use HasFactory, \App\Traits\HasStringId;

    // protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'description',
        'size',
        'category_id',
        'product_id', // Link to main product
        'type',
        'price',
        'image',
        'stock',
        'weight',
        'is_active', // Visibility toggle
        'tier_pricing',
    ];

    protected $appends = ['image_url'];

    protected $casts = [
        '_id' => 'string',
        'is_active' => 'boolean',
        'tier_pricing' => 'array',
    ];

    public $timestamps = true;

    /**
     * Relasi: ProductReseller -> Category
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
     * Relasi: ProductReseller -> Product (Parent)
     */
    public function product()
    {
        return $this->belongsTo(
            Product::class,
            'product_id',
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
