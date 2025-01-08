<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Livewire\Mrc\Navbar;
use Livewire\Component;
use Livewire\Attribute\Title;

#[Title('Cart - MRC-Shop')]
class CartPage extends Component
{
    public $cart_items = [];

    public $grand_total;

    public function mount(){
        $this->cart_items = CartManagement::getCartItemsFromCookie();

        $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);
    }

    public function removeItem($product_id){
        $this->cart_items = CartManagement::removeCartItem('$product_id');

        $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);

        $this->dispatch('update-cart-count',total_count:count($this->cart_items))->to(Navbar::class);
    }

    public function increaseQty($product_id){
        $this->cart_items = CartManagement::increamentQuantityToCartItem($product_id);
        $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);
    }

    public function decreaseQty($product_id){
        $this->cart_items = CartManagement::decreamentQuantityToCartItem($product_id);
        $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);
    }

    public function render()
    {
        return view('livewire.cart-page');
    }
}

