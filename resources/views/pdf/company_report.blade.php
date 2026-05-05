<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>E-Rep — Company Report</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            margin: 0;
            padding: 20px;
            background: #ffffff;
        }

        .header {
            border-bottom: 3px solid #1a5276;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }

        .logo {
            font-size: 22px;
            font-weight: bold;
            color: #1a5276;
            letter-spacing: 0.5px;
        }

        .gen-date {
            font-size: 10px;
            color: #555;
            margin-top: 6px;
        }

        h2 {
            font-size: 12px;
            color: #ffffff;
            background: #1a5276;
            padding: 8px 10px;
            margin: 18px 0 0 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 16px 0;
            font-size: 10px;
        }

        th {
            background: #e8f0f5;
            color: #1a5276;
            text-align: left;
            padding: 7px 8px;
            border: 1px solid #c5d9e8;
            font-weight: bold;
        }

        td {
            padding: 7px 8px;
            border: 1px solid #d0d0d0;
            vertical-align: top;
        }

        tr:nth-child(even) td {
            background: #fafafa;
        }

        .meta {
            margin-bottom: 16px;
        }

        .meta-row {
            margin-bottom: 4px;
        }

        .meta-label {
            font-weight: bold;
            color: #1a5276;
            display: inline-block;
            width: 120px;
        }

        .muted {
            color: #777;
            font-style: italic;
        }

        .footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            font-size: 9px;
            color: #888;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="logo">E-Rep</div>
        <div class="gen-date">Report generated: {{ $generated_at->format('F j, Y \a\t g:i A') }}</div>
    </div>

    <div class="meta">
        <div class="meta-row"><span class="meta-label">Company name</span> {{ $company->company_name }}</div>
        <div class="meta-row"><span class="meta-label">Email</span> {{ $company->email }}</div>
        <div class="meta-row"><span class="meta-label">Hotline</span> {{ $company->hotline ?? '—' }}</div>
    </div>

    <h2>Drugs</h2>
    <table>
        <thead>
            <tr>
                <th>Market name</th>
                <th>Category</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($drugs as $drug)
            <tr>
                <td>{{ $drug->market_name }}</td>
                <td>{{ $drug->category?->name ?? '—' }}</td>
                <td>{{ $drug->status }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="muted">No drugs.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Events</h2>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Date</th>
                <th>Requests</th>
            </tr>
        </thead>
        <tbody>
            @forelse($events as $event)
            <tr>
                <td>{{ $event->title }}</td>
                <td>{{ $event->event_date ? $event->event_date->format('Y-m-d H:i') : '—' }}</td>
                <td>{{ $event->event_requests_count ?? 0 }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="muted">No events.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Medical reps</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reps as $rep)
            <tr>
                <td>{{ $rep->full_name }}</td>
                <td>{{ $rep->phone ?? '—' }}</td>
                <td>{{ $rep->email }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="muted">No medical reps.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">E-Rep confidential report — for authorized use only.</p>
</body>

</html>