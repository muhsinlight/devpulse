<x-mail::message>
# New contact message

Someone reached out through the DevPulse contact form.

**Name:** {{ $name }}

**Email:** {{ $email }}

---

{{ $body }}

<x-mail::panel>
Reply directly to this email to respond to {{ $name }}.
</x-mail::panel>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
