<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Jahresübersicht {{$year}}</title>
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
            font-size: 9pt;
            background: white;

            page-break-before: avoid;
            page-break-after: auto;
            page-break-inside: auto;
        }

        th, td {
            padding: 1.1mm 1mm;
            vertical-align: middle;
            line-height: 1;
            border-left: 1px solid #d7dae5;
        }

        td {
            text-align: center;
        }

        .first {
            width: 35mm;
            text-align: left;
            padding-left: 2mm;
            border-left-color: transparent;
        }

        tr.odd {
            background-color: #ebecf0;
        }

        .total {
            font-weight: bold;
        }

        .minus {
            color: #b91c1c;
        }

        h3 {
            margin-top: 0;
            margin-bottom: 4mm;
        }

    </style>

</head>
<body>

<main>

    <div>

        <h3 style="text-align: center">
            Jahresübersicht {{$year}} – Überstunden aller Mitarbeiter
        </h3>

        <table>

            <thead>
            <tr>
                <th class="first">Mitarbeiter</th>
                <th>Übertrag<br/>{{$year-1}}</th>
                @foreach($monate as $monat)
                    <th>{{ \Illuminate\Support\Str::substr($monat, 0, 3) }}</th>
                @endforeach
                <th>Überstunden</th>
                <th>Resturlaub</th>
            </tr>
            </thead>

            <tbody>
            @foreach($users as $user)
                <tr class="@if($loop->even) even @else odd @endif">
                    <td class="first">
                        {{ $user['name'] }}@if($user['minijob']) <small>(Minijob: Übertrag)</small>@endif
                    </td>
                    <td>
                        {{ $user['uebertrag'] }} h
                    </td>
                    @foreach($user['monate'] as $monat)
                        <td class="@if($monat['diff'] < 0) minus @endif">
                            {{ $monat['diff_nice'] }}
                        </td>
                    @endforeach
                    <td class="total @if($user['summe'] < 0) minus @endif">
                        {{ $user['summe_nice'] }} h
                    </td>
                    <td>
                        {{ $user['urlaub'] }}
                    </td>
                </tr>
            @endforeach
            </tbody>

        </table>

    </div>

</main>

</body>
</html>
