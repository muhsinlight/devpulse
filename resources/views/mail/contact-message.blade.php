<x-mail::message>
# New contact message

**Name:** {{ $name }}

**Email:** {{ $email }}

{{ $body }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
