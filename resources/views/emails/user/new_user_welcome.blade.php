@component('mail::message')
# Welcome to Insan Bumi Mandiri

Thanks for signing up. **We Really appreciate** it. Let us _know if we can_ do more to please you!

@component('mail::button', ['url' => ''])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
