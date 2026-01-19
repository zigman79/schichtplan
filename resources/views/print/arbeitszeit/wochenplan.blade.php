<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Aktuelle Übersicht </title>
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
    <h3 style="text-align: center">
        aktuelle Wochenübersicht
    </h3>

    <table>

        <thead>
        <tr>
            <th class="first"></th>
            @foreach($tag as $entry)
                <th class="first">{{$entry}}</th>
            @endforeach
        </tr>
        </thead>

        <tbody>
        <tr class="even">
            <td>Anwesend</td>
        @foreach($arbeit as $entry)
            <td>
                @foreach($entry as $arbeitszeit)
                    {{ $arbeitszeit->user->name }}<br>
                @endforeach
            </td>
        @endforeach

        </tr>
        <tr class="odd">
            <td>Krank</td>
        @foreach($krank as $entry)
            <td>
                @foreach($entry as $arbeitszeit)
                    {{ $arbeitszeit->user->name }}<br>
                @endforeach
            </td>
        @endforeach

        </tr>
        <tr class="even">
            <td>Frei</td>
        @foreach($frei as $entry)
                <td>
                @foreach($entry as $arbeitszeit)
                    {{ $arbeitszeit->user->name }}<br>
                @endforeach
            </td>
        @endforeach
        </tr>
        <tr class="odd">
            <td>Urlaub</td>

        @foreach($urlaub as $entry)
            <td>
                @foreach($entry as $arbeitszeit)
                    {{ $arbeitszeit->user->name }}<br>
                @endforeach
            </td>
        @endforeach
        </tr>
        </tbody>

    </table>
</main>

</body>
</html>
