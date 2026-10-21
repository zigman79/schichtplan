<x-mail::message>
Hallo Frau Pohle,

anbei die Arbeitszeiten für den Monat {{ $zeit }}.

@if(count($standard))
**Minijobber Standard** – abgerechnet werden höchstens die Monatsstunden, Mehrstunden werden in den nächsten Monat übertragen.

<x-mail::table>
| Name | gearbeitet | Übertrag Vormonat | abzurechnen | Übertrag Folgemonat |
|:-----|-----------:|------------------:|------------:|--------------------:|
@foreach($standard as $user)
| {{ $user["user"] }} | {{ $user["gearbeitet_nice"] }} | {{ $user["uebertrag_alt_nice"] }} | **{{ $user["sum_nice"] }}** ({{ $user["sum"] }}h) | {{ $user["uebertrag_neu_nice"] }} |
@endforeach
</x-mail::table>

@endif
@if(count($data))
@if(count($standard))
**Weitere Minijobber**

@endif
@foreach($data as $user)
{{ $user["user"] }}: {{ $user["sum_nice"] }} hh:mm ({{ $user["sum"] }}h)<br>
@endforeach
@endif

mit freundlichen Grüßen

Michael Labuschke<br>
Golfpark Hufeisensee GmbH & Co. KG.
</x-mail::message>
