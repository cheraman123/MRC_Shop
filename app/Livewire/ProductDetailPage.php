<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use Livewire\Attributes\Title;

#[Title('Product Detail -MRC-Shop')]

class ProductDetailPage extends Component
{
    public $slug;

    public function mount($slug){
        $this->slug = $slug;
    }
    public function render()
    {

        return view('livewire.product-detail-page',[
            'product' => Product::where('slug',$this->slug)->firstOrFail(),
        ]);
    }
}
