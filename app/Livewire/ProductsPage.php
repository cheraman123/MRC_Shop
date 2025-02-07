<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Livewire\Mrc\Navbar;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Product;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

#[Title('Products - MRC-Shop')]

class ProductsPage extends Component
{

    use LivewireAlert;
    use WithPagination;
    


    #[Url]
    public $selected_categories =[];
    #[Url]
    public $featured;
    #[Url]
    public $on_sale;
    #[Url]
    public $price_range = 300000;

    #[Url]
    public $sort = 'latest';

    //add product to cart methed

    public function addToCart($product_id){
    //    dd($product_id);

    $total_count = CartManagement::addItemsToCart($product_id);

    $this->dispatch('update-cart-count',total_count: $total_count)->to(Navbar::class);

    $this->alert('success','Product added to the cart successfully',[
       'position' => 'bottom-end',
       'timmer' => 3000,
       'toast' => true,
    ]);
    }
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

        if($this->sort == 'price'){
            $productQuery->orderBy('price');
        }

        if($this->sort == 'latest'){
            $productQuery->latest();
        }

        return view('livewire.products-page',[
            'products' => $productQuery->paginate(9),
            'categories' => Category::where('is_active',1)->get(['id','name','slug']),
        ]);
    }
}
