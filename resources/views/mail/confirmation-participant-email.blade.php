<x-mail::message>
    # Bekræftelse på afbud

    Hej {{ $name }},

    Vi har modtaget dine afbud. Her er detaljerne:

    Datoer hvor du ikke kan spille:
    @foreach ($unavailableDates as $date)
        - {{ $date }}
    @endforeach

    Besked:
    @if ($optionalMessage)
        {{ $optionalMessage }}
    @endif

    Tak fordi du brugt nembadminton!

    Med venlig hilsen,
    Nembadminton
</x-mail::message>
