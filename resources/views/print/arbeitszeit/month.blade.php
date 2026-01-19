<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Arbeitsplan - {{$month}}-{{$year}}</title>
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

        .table-wrapper {
            background: white;
            border-radius: 3mm;
            padding: 1mm;
            margin-bottom: 4mm;
            border: 2px solid #f3f4f6;

            page-break-after: auto;
            page-break-inside: avoid;
        }

        .table-outer {
            background: white;
        }


        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12pt;
            border-radius: 2mm;
            overflow: hidden;
        }

        tr {
        }

        th, td {
            padding: 1mm 1mm;
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
            width: 50mm;
            text-align: left;
            padding-left: 2mm;
            border-left-color: transparent;
        }

        .tournament {
            color: darkgoldenrod;
            font-size: 3mm;
        }

        tr.odd {
            background-color: #ebecf0;
        }

        .ac {
            color: #319720;
            font-weight: bold;
        }

        .ac {
            color: #319720;
        }

    </style>

</head>
<body>

<main>

    @foreach($weeks as $week)
        <div class="table-wrapper">
            <div class="table-outer">
                <table>

                    <thead>
                    <tr>
                        <th class="first">
                            <strong>{{ $week[0]->format('d.m.Y') }} - {{ $week[6]->format('d.m.Y') }} </strong>
                        </th>
                        @foreach($week as $numberOfDay => $day)
                            <th>

                                <div class="font-semibold">
                                    {{$dayLabels[$numberOfDay]}}
                                </div>

                                <small> {{$day->format('d.m.Y')}} </small>

                            </th>
                        @endforeach
                    </tr>
                    </thead>


                    <tbody>
                    <tr class="odd">
                        <td class="first">
                        </td>
                        @foreach($week as $numberOfDay => $day)
                            <td>
                                @if(isset($tournaments[$day->format('Y-m-d')]))
                                    @foreach($tournaments[$day->format('Y-m-d')] as $t)
                                        @if($loop->index > 0)
                                            <br>
                                        @endif
                                        <span class="ac"> {{Str::before($t["turniername"]," ")}}</span>
                                    @endforeach
                                @endif
                                    @if(isset($feiertage[$day->format('Y-m-d')]))
                                            <br>
                                            <span class="ag"> {{$feiertage[$day->format('Y-m-d')]["name"]}}</span>
                                    @endif
                            </td>
                        @endforeach
                    </tr>
                    @foreach($users as $user)
                        <tr class="@if($loop->odd) even @else odd @endif">
                            <td class="first">
                                {{$user->name}}
                            </td>
                            @foreach($week as $numberOfDay => $day)
                                <td>
                                    @if($user->arbeitszeiten->where('tag', $day->format('Y-m-d'))->first())
                                        @if($user->arbeitszeiten->where('tag', $day->format('Y-m-d'))->first()->frei_urlaub_krank !== null)
                                            <span class="ac">
                                        @endif
                                            {{$user->arbeitszeiten->where('tag', $day->format('Y-m-d'))->first()->readable_future_time}}
                                        @if($user->arbeitszeiten->where('tag', $day->format('Y-m-d'))->first()->frei_urlaub_krank !== null)
                                            </span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>

                </table>
            </div>
        </div>
        Stand: {{now()->format("d.m.Y")}}

    @endforeach
</main>

</body>
</html>
