<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Übersicht - {{$month}}-{{$year}}</title>
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


    @foreach($users as $user)

        @php
            $summe = 0;
            $dt = now();
            $urlaubstage = 0;
            $user_vorgabe = ( $user->isMinijob() ? $user->minijob_vorgabe->hours ?? $vorgabe : $vorgabe ) * 60;
            $urlaub = ( $user->isMinijob() ? $user->minijob_vorgabe->away ?? 8 : 8 ) * 60;
        @endphp

        <div @if(!$loop->last) style="page-break-after: always" @endif>

            <h3 style="text-align: center">
                Monatsübersicht {{$month_labels[$month - 1]}} {{$year}} für {{$user->name}}
            </h3>

            <table>

                <thead>
                <tr>
                    <th class="first">Datum</th>
                    <th>Von - Bis</th>
                    <th>Pause</th>
                    <th>Stunden</th>
                </tr>
                </thead>

                <tbody>
                @foreach($days as $day)
                    <tr class="@if($loop->even) even @else odd @endif">
                        <td class="first">
                            {{$day->format('d.m.Y')}}
                        </td>

                        @php
                            $arbeitszeit = $user->arbeitszeiten->where('tag', $day->format('Y-m-d'))->first();
                        @endphp

                        <td>
                            @if($arbeitszeit)
                                @if($arbeitszeit->frei_urlaub_krank !== null)
                                    <span class="ac">{{$arbeitszeit->readable_future_time}}</span>
                                @else
                                    {{$arbeitszeit->readable_future_time}}
                                @endif
                                @php if ($arbeitszeit->frei_urlaub_krank === 'urlaub') { $urlaubstage++; } @endphp
                            @else
                                -
                            @endif
                        </td>

                        <td>
                            @if($arbeitszeit)
                                {{ $arbeitszeit->pausenzeit }}
                            @else
                                -
                            @endif
                        </td>

                        <td>
                            @if($arbeitszeit)
                                @if(($arbeitszeit->frei_urlaub_krank === 'urlaub' || $arbeitszeit->frei_urlaub_krank === 'krank' || $arbeitszeit->frei_urlaub_krank === 'schule') && $arbeitszeit->day->isWeekday())
                                    @php $summe += $urlaub; @endphp
                                    <x-time-display time="{{$urlaub}}" />
                                @else
                                    {{ $arbeitszeit->readable_arbeitszeit }}
                                    @php $summe += $arbeitszeit->arbeitszeit_in_minutes; @endphp
                                @endif
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>

                <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right">Summe:</td>
                    <td>
                        <x-time-display time="{{$summe}}" />
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: right">Normalstunden:</td>
                    <td>
                        <x-time-display time="{{$user_vorgabe}}" />
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: right">+/- Stunden:</td>
                    @php $diff = $summe - $user_vorgabe; @endphp

                    <td>
                        @if($diff < 0)
                            -<x-time-display time="{{abs($diff)}}" />
                        @else
                            +<x-time-display time="{{$diff}}" />
                        @endif
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: right">Urlaubstage:</td>
                    <td>{{ $urlaubstage }}</td>
                </tr>
                </tfoot>
            </table>

        </div>
    @endforeach


</main>

</body>
</html>
