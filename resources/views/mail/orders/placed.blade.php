<x-mail::message>
# Order Placed Sucessfully!

Thank You For Order . Your order number is : {{$order->id}}.

<x-mail::button :url="$url">
View Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
