<x-mail::message>
    # Afbud fra {{ $name }}

    Hej,

    Du har modtaget et afbud. Her er detaljerne:

    Datoer hvor der ikke kan spilles:
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
