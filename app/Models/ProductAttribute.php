<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAttribute extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_attributes';

    protected $fillable = [
        'product_id',
        'color',
        'ram',
        'price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
