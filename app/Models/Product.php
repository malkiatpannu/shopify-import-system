<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable     =   [
        'upload_id',
        'handle',
        'title',
        'body_html',
        'vendor',
        'product_type',
        'tags',
        'published',
        'sku',
        'price',
        'compare_at_price',
        'requires_shipping',
        'taxable',
        'inventory_tracker',
        'inventory_qty',
        'inventory_policy',
        'fulfillment_service',
        'weight',
        'weight_unit',
        'image_src',
        'image_position',
        'image_alt_text',
        'shopify_product_id',
        'shopify_variant_id',
        'status',
        'error_message',
    ];

    protected $casts        =   [
        'published'         =>  'boolean',
        'requires_shipping' =>  'boolean',
        'taxable'           =>  'boolean',
        'price'             =>  'decimal:2',
        'compare_at_price'  =>  'decimal:2',
        'weight'            =>  'decimal:2',
    ];

    public function upload(): BelongsTo
    {
        return $this->belongsTo(Upload::class);
    }

    public function importRecords(): HasMany
    {
        return $this->hasMany(ImportRecord::class);
    }

    public function errorLogs(): HasMany
    {
        return $this->hasMany(ErrorLog::class);
    }
}