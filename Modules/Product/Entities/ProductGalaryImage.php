<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductGalaryImage extends Model
{
    use HasFactory;
    protected $casts = [
        'id' => 'integer',
        'product_id' => 'integer',
        'images_source' => 'string',
        'media_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    protected $table = "product_galary_images";
    protected $guarded = ["id"];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function getImagesSource($value): string
    {
        return (string) $value;
    }
}
