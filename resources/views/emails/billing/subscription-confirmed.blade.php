@component('mail::message')
# Welcome to {{config('app.name')}} Premium!

Hi {{ $userName ?? ($user->name ?? 'there') }},

Thank you for your purchase! Your subscription to **{{ $productName }}** has been confirmed.

## Order Details

**Amount:** {{ $displayAmount }}
**Product:** {{ $productName }}
**Order ID:** {{ $orderId }}

---

## Your Plan Benefits

@if(!empty($benefits))
    @component('mail::panel')
    @foreach($benefits as $benefit)
        * {{ $benefit }}
    @endforeach
    @endcomponent
@else
    @component('mail::panel')
    * All premium features included
    @endcomponent
@endif

@component('mail::button', ['url' => route('dashboard'), 'color' => 'blue'])
Access Your Dashboard
@endcomponent

If you have any questions or need assistance, please don't hesitate to reach out to our support team.

Thanks,
The {{ config('app.name') }} Team
@endcomponent