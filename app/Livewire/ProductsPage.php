<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Product;
use App\Models\Category;
use Livewire\WithPagination;

#[Title('Products - MRC-Shop')]

class ProductsPage extends Component
{
    use WithPagination;


    #[Url]
    public $selected_categories =[];
    #[Url]
    public $featured;
    #[Url]
    public $on_sale;
    #[Url]
    public $price_range = 300000;

    public function render()
    {
        $productQuery = Product::query()->where('is_active',1);

        if(!empty($this->selected_categories)){
            $productQuery->whereIn('category_id',$this->selected_categories);
        }

        if($this->featured){
            $productQuery->where('is_featured',1);
        }

        if($this->on_sale){
            $productQuery->where('is_featured',1);
        }

        if($this->price_range){
            $productQuery->whereBetween('price',[0,$this->price_range]);
        }

        return view('livewire.products-page',[
            'products' => $productQuery->paginate(9),
            'categories' => Category::where('is_active',1)->get(['id','name','slug']),
        ]);
    }
}
