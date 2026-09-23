<x-mail::message>
Hej,

Du er blevet inviteret til {{$invitation->clubhouse->name}}. Brug følgende link for at blive medlem:

<x-mail::button :url="$url">
    Accepter invitation
</x-mail::button>

Tak fordi du valgte Nembadminton!

Med venlig hilsen,
Nembadminton
</x-mail::message>
