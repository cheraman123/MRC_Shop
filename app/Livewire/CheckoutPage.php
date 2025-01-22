<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Models\Order;
use App\Models\Address;
use App\Mail\OrderPlaced;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Title;
use Livewire\Component;
use Stripe\Checkout\Session;
use Stripe\Stripe;

#[Title('Checkout')]
class CheckoutPage extends Component
{

    public $first_name;
    public $last_name;
    public $phone;
    public $street_address;
    public $city;
    public $state;
    public $zip_code;
    public $cuntry;
    public $payment_method;

    public function mount(){
        $cart_items = CartManagement::getCartItemsFromCookie();

        if(count($cart_items)==0){
            return redirect('/products');
        }
    }


    public function placeOderd(){
        $this->validate([
            'first_name' => 'requires',
            'last_name' => 'required',
            'phone' => 'required',
            'street_addres' => 'required',
            'city'  => 'required',
            'state' => 'required',
            'zip_code' => 'required',
            'payment_metod' => 'required',
        ]);

        $cart_items = CartManagement::getCartItemsFromCookie();

        $line_items =[];

        foreach($cart_items as $item){
            $line_items[] = [
                'price_data' => [
                    'currency' => 'INR',
                    'unit_amount' =>$item['unit_amount'] * 100,
                    'product_data' => [
                        'name' => $item['name'],

                    ]
                    ],
                    'quantity' => $item['quantity'],
            ];
        }

        $order = new Order();

        $order->user_id = auth()->user()->id;

        $order->grand_total = CartManagement::calculateGrandTotal($cart_items);

        $order->payment_method = $this->payment_method;

        $order->payment_status = 'pending';

        $order->status = 'new';

        $order->currency = 'inr';

        $order->shipping_amount = 0;

        $order->shipping_method = 'non';

        $order->notes = 'Order Placed by '.auth()->user()->name;

        $address = new Address();

        $address->first_name = $this->first_name;
        $address->last_name = $this->last_name;
        $address->phone = $this->phone;
        $address->street_address = $this->street_address;
        $address->city = $this->city;
        $address->state = $this->state;
        $address->zip_code = $this->zip_code;

        $redirect_url = '';

        if($this->payment_method =='stripe'){
             
            Stripe::setApiKey(env('SRIPE_SECRET'));

            $sessionCheckout = Session::create([
                'payment_method_types' => ['card'],
                'customer_email' => auth()->user()->email,
                'line_items' => $line_items,
                'mode' => 'payment',
                'success_url' => route('sucess').'?session_id={CHECLOUT_SESSION_ID}',
                'cancel_url' => route('cancel'),
            ]);

            $redirect_url = $sessionCheckout->url;
        }else{

            $redirect_url = route('sucess');
        }

        $order->save();
        $address->order_id = $order->id;

        $address->save();

        $order->items()->createMany($cart_items);

        CartManagement::clearCartItems();

        Mail::to(request()->user())->send(new OrderPlaced($order));

        return redirect($redirect_url);

    }
    public function render()
    {
        $cart_items = CartManagement::getCartItemsFromCookie();
        $grand_total = CartManagement::calculateGrandTotal($cart_items);
        return view('livewire.checkout-page',[
            'cart_items' => $cart_items,
            'grand_total' => $grand_total,
        ]);
    }
}
