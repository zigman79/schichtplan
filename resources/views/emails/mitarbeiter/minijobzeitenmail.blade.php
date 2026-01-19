<x-mail::message>
    Hallo Frau Pohle,

anbei deine Arbeitszeiten für den Monat {{ $zeit }}.

@foreach($data as $user)
    {{ $user["user"] }}: {{ $user["sum_nice"] }} hh:mm ({{ $user["sum"] }}h)
@endforeach

mit freundlichen Grüßen

Michael Labuschke
Golfpark Hufeisensee GmbH & Co. KG.
</x-mail::message>
