<x-mail::message>
# {{$team->name}}

Besked fra træneren:
@component('mail::panel')
    @foreach(explode("\n", $team->message) as $line)
        {{ $line }}<br>
    @endforeach
@endcomponent

# Detailer om holdrunden

Du skal spille på **{{$squad->resolvedName}}**:

<x-mail::table>
| Kategori       | Makker            |
|----------------|-------------------|
@foreach($squad->categories as $category)
    | {{ $category->name }} | {{ $category->players->pluck('name')->join(', ') }} |
@endforeach
</x-mail::table>

<x-mail::button :url="$url">
Se hele holdrunden
</x-mail::button>

Tak,<br>
{{ config('app.name') }}
</x-mail::message>
