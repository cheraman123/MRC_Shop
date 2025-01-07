<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Livewire\Mrc\Navbar;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use App\Helpers\CartManagement;
use Livewire\Attributes\Title;

#[Title('Product Detail -MRC-Shop')]

class ProductDetailPage extends Component
{
    use LivewireAlert;
    public $slug;

    public $quantity = 1;


    public function mount($slug){
        $this->slug = $slug;
    }

    
    public function increaseQty(){
        $this->quantity++;
    }

    public function decreaseQty(){
        if ($this->quantity >1){
            $this->quantity--;
        }
    }

    
    public function addToCart($product_id){
        //    dd($product_id);
    
        $total_count = CartManagement::addItemToCart($product_id);
    
        $this->dispatch('update-cart-count',total_count: $total_count)->to(Navbar::class);
    
        $this->alert('success','Product added to the cart successfully',[
           'position' => 'bottom-end',
           'timmer' => 3000,
           'toast' => true,
        ]);
    }
    public function render()
    {

        return view('livewire.product-detail-page',[
            'product' => Product::where('slug',$this->slug)->firstOrFail(),
        ]);
    }
}
