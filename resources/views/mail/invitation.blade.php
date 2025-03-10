<x-mail::message>
Hej,

Du er blevet inviteret til {{$invitation->clubhouse->name}}. Brug følgende link for at blive medlem:

<x-mail::button :url="$url">
    Accepter invitation
</x-mail::button>

Tak fordi du valgte NemBadminton!

Med venlig hilsen,
NemBadminton
</x-mail::message>
