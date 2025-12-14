<x-mail::message>
# {{$teamName}}

Du skal spille på {{$squadName}} i kategorierne:

@foreach($categories as $category)
- {{ $category['name'] }} @if($category['partner'])sammen med {{$category['partner']}} @endif <br>
@endforeach

<x-mail::table>
| Kategori       | Makker            |
|----------------|-------------------|
@foreach($categories as $category)
    | {{ $category['name'] }} | {{ $category['partner'] ?? '—' }} |
@endforeach
</x-mail::table>

<x-mail::button :url="$url">
Se holdrunden
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
