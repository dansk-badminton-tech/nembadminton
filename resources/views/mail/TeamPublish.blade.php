<x-mail::message>
# {{$teamName}}

Besked:
@component('mail::panel')
    @foreach(explode("\n", $message) as $line)
        {{ $line }}<br>
    @endforeach
@endcomponent

# Se holdrunden via linket

<x-mail::button :url="$url">
Se holdrunden
</x-mail::button>

Denne email er sendt fra {{ config('app.name') }}
</x-mail::message>
