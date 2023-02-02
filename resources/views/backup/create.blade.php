<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        td, th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #dddddd;
        }
        td:nth-child(even), th:nth-child(even) {
            background-color: #D6EEEE;
        }
    </style>
</head>
<body class="antialiased">
<div class="container">
    @if($message['enable'])
    <div class="row">
        <div style="background-color: darkslategrey; text-align: center;">
            <form action="{{ route('backup.store') }}" method="POST" style="padding: 20px !important;">
                @csrf
                <label style="color: #dedede; font-weight: bolder; font-size: 24px">
                    Enter Your Name:
                    <input style="font-size: 22px" type="text" name="backed_by" class="form-control bg-white" required>
                </label>
                <button class="btn btn-primary"
                        style="background-color: darkslategrey; font-size: 24px; color: #dedede; border: 2px solid #dedede; border-radius: 5px">
                    Backup Now
                </button>
            </form>
        </div>
    </div>
    @endif
    <div style="padding: 20px !important;">
        <marquee behavior="alternate" style="font-size: 22px; font-weight: bold; color:firebrick">{{ $message['content'] }}</marquee>
    </div>

    <h1 style="text-align: center; text-decoration: underline">Backup Process</h1>
    @if($backups && count($backups) > 0)
        @foreach($backups as $key=>$backup)
            <table>
                <tr>
                    <th colspan="3" style="border: unset"><h3 style="text-align: center; text-decoration: underline">{{ 'Date: '.$key }}</h3></th>
                </tr>
                <tbody>
                <tr>
                    @foreach($backup as $key1=>$data)
                        <td style="border: 3px solid darkslategrey">
                            <span style="font-weight: bold">Time : {{ $key1 }}</span>
                            <ul>
                                <li><span
                                        style="font-weight: bold">Backed Up By :</span> {{ $data['backup_done_by'] }}
                                </li>
                                <li><span
                                        style="font-weight: bold">IP Address :</span> {{ $data['backup_done_ip'] }}
                                </li>
                            </ul>
                        </td>
                    @endforeach
                </tr>
                </tbody>
            </table>
        @endforeach
    @endif
</div>
</body>
</html>
