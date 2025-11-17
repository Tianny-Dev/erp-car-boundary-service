<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 16px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #777;
            padding: 8px;
        }

        th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        td.amount {
            text-align: right;
        }

        .grand-total {
            font-weight: bold;
            background: #e8e8e8;
        }
    </style>
</head>
<body>

<h2>{{ $title }}</h2>

<table>
    <thead>
        <tr>
            <th>{{ $headings[0] }}</th>
            <th>{{ $headings[1] }}</th>
            <th>{{ $headings[2] }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach($rows as $row)
            <tr class="{{ $loop->last ? 'grand-total' : '' }}">
                <td>
                    {{ $row['name'] }}
                </td>

                <td>
                    {{ $row['period'] }}
                </td>

                <td class="amount">
                    {{-- Format all rows as ₱1,234.56 --}}
                    ₱{{ number_format((float)$row['amount'], 2) }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
