<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Jahresübersicht {{$year}} für {{$user->name}}</title>
    <style>
        /* nunito-regular - latin */
        @font-face {
            font-family: 'Nunito';
            font-style: normal;
            font-weight: 400;
            src: url({{ storage_path('fonts\nunito-v20-latin-regular.ttf')  }}) format('truetype');
        }

        /* nunito-600 - latin */
        @font-face {
            font-family: 'Nunito';
            font-style: normal;
            font-weight: 600;
            src: url({{ storage_path('fonts\nunito-v20-latin-600.ttf')  }}) format('truetype');
        }

        /* nunito-700 - latin */
        @font-face {
            font-family: 'Nunito';
            font-style: normal;
            font-weight: 700;
            src: url({{ storage_path('fonts\nunito-v20-latin-700.ttf')  }}) format('truetype');
        }

        html, body {
            font-family: Nunito, ui-sans-serif;
        }

        @page {
            margin: 0;
            background-color: #f3f4f6
        }

        body {
            margin: 0;
            padding: 4mm;
        }


        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            background: white;

            page-break-before: avoid;
            page-break-after: auto;
            page-break-inside: auto;
        }

        th, td {
            padding: 1.1mm 1mm;
            vertical-align: middle;
            line-height: 1;
        }


        th, td {
            border-left: 1px solid #d7dae5
        }

        th {
            line-height: 1;
        }

        td {
            text-align: center;
        }

        .first {
            width: 20mm;
            text-align: left;
            padding-left: 2mm;
            border-left-color: transparent;
        }

        tr.odd {
            background-color: #ebecf0;
        }

        .ac {
            color: #319720;
            font-weight: bold;
        }

        h3 {
            margin-top: 0;
            margin-bottom: 4mm;
        }

        tfoot {
            font-weight: bold;
        }

    </style>

</head>
<body>

<main>

    @php
        \Carbon\Carbon::setlocale(config('app.locale'));
    @endphp

    <div>

        <h3 style="text-align: center">
            Jahresübersicht {{$year}} für {{$user->name}}
        </h3>

        <table>

            <thead>
            <tr>
                <th class="first">Monat</th>
                <th>Sollstunden</th>
                <th>Ist</th>
                <th>+/- Stunden</th>
                <th>Urlaub</th>
            </tr>
            <tr>
                <th>
                    Übertrag<br/>
                    {{$year-1}}
                </th>
                <th></th>
                <th></th>
                <th>{{$start->ueberstunden_nice}} h</th>
                <th>{{$start->urlaub}} (+{{$start->resturlaub }} Rest)</th>
            </tr>
            </thead>

            <tbody>
            @foreach($verlauf as $monat)
                <tr class="@if($loop->even) even @else odd @endif">
                    <td class="first">
                        {{$monat["monat"]->translatedFormat('F')}}
                    </td>

                    <td>
                        {{$monat["vorgabe_nice"]}} h
                    </td>

                    <td>
                        {{$monat["sum_nice"]}} h
                    </td>

                    <td>
                        {{$monat["diff_nice"]}} h
                    </td>
                    <td>
                        {{$monat["urlaub"]}}
                    </td>
                </tr>
            @endforeach
            </tbody>

            <tfoot>
            <tr>
                <td>Summe:</td>
                <td>
                </td>
                <td>
                </td>
                <td>
                    {{$end["summe_nice"]}}
                </td>
                <td>{{ $end["urlaub"] }}</td>
            </tr>
            </tfoot>
        </table>

    </div>


</main>

</body>
</html>
