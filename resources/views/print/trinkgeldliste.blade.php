<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Trinkgeldliste</title>
    <style>
        @font-face {
            font-family: 'Nunito';
            font-style: normal;
            font-weight: 400;
            src: url({{ storage_path('fonts\nunito-v20-latin-regular.ttf') }}) format('truetype');
        }
        @font-face {
            font-family: 'Nunito';
            font-style: normal;
            font-weight: 700;
            src: url({{ storage_path('fonts\nunito-v20-latin-700.ttf') }}) format('truetype');
        }

        html, body {
            font-family: Nunito, ui-sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 0;
        }

        @page {
            margin: 12mm 10mm;
        }

        h2 {
            text-align: center;
            margin-bottom: 8mm;
            font-size: 14pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #b0b0b0;
            padding: 2mm 2.5mm;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background-color: #e5e7eb;
            font-weight: 700;
            font-size: 9pt;
        }

        td.name {
            text-align: left;
            white-space: nowrap;
            font-weight: 600;
            width: 30mm;
        }

        td.sum, th.sum {
            font-weight: 700;
            background-color: #e5e7eb;
            width: 15mm;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .day-col {
            width: 15mm;
        }
    </style>
</head>
<body>

<h2>Trinkgeldliste</h2>

<table>
    <thead>
        <tr>
            <th style="text-align: left">Mitarbeiter</th>
            <th class="day-col">Montag</th>
            <th class="day-col">Dienstag</th>
            <th class="day-col">Mittwoch</th>
            <th class="day-col">Donnerstag</th>
            <th class="day-col">Freitag</th>
            <th class="day-col">Samstag</th>
            <th class="day-col">Sonntag</th>
            <th class="sum">Summe</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td class="name">{{ $user->name }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td class="sum"></td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
