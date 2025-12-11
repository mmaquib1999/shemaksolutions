@component('mail::message')
# You are invited to join {{ $invitation->owner->name ?? 'the team' }}

{{ $invitation->owner->name ?? 'A teammate' }} has invited you to join the team as a **{{ ucfirst($invitation->role) }}**.

- Email: {{ $invitation->email }}
- Invited by: {{ $invitation->owner->email ?? 'n/a' }}

@component('mail::button', ['url' => $acceptUrl])
Accept Invitation
@endcomponent

If the button does not work, copy and paste this link in your browser:
{{ $acceptUrl }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
