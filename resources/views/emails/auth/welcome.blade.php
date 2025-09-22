@component('mail::layout')
{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

# Welcome to {{ config('app.name') }}, {{ $user->name }}!

Thank you for subscribing to {{ config('app.name') }}. We're excited to have you on board!

To get started, please click the button below to set your password and access your account:

@component('mail::button', ['url' => url(route('password.reset', [
    'token' => $token,
    'email' => $user->email
], false))])
Set Your Password
@endcomponent

This link will expire in {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60) }} minutes.

If you didn't create an account with us, no further action is required.

Thanks,<br>
{{ config('app.name') }} Team

{{-- Footer --}}
@slot('footer')
@component('mail::footer')
© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.

If you're having trouble clicking the "Set Your Password" button, copy and paste the URL below into your web browser:

{{ url(route('password.reset', [
    'token' => $token,
    'email' => $user->email
], false)) }}
@endcomponent
@endslot
@endcomponent
