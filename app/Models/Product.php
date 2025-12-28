<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'image',
        'brand_id',
        'category_id',
        'subcategory_id',
        'is_active',
        'is_stock',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_stock' => 'boolean',
        'price' => 'decimal:2',
    ];

    // Relationships
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function orderdetail(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }
}
