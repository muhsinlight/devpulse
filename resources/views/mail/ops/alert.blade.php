<x-mail::message>
# {{ $title }}

@isset($intro)
{{ $intro }}

@endisset

@foreach ($rows as $label => $value)
**{{ $label }}:** {{ $value }}

@endforeach

@isset($actionUrl)
<x-mail::button :url="$actionUrl" color="primary">
{{ $actionLabel ?? 'View details' }}
</x-mail::button>
@endisset

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
