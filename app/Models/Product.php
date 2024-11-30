<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name','slug','images','category_id','description','price','is_active','is_featured','in_stock','on_sale'];

    protected $casts = ['images'=> 'array'];

    public function category(){
        return $this->belongsTo(Category::class);


    }
    public function product()
{
    return $this->belongsTo(Product::class);
}
    public function orderitem(){
        return $this->hasMany(Orderitem::class);
    }
}
