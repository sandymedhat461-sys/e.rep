<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>E-Rep — Medical Report</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 11px; color: #1a1a1a; margin: 0; padding: 20px; background: #ffffff; }
        .header { border-bottom: 3px solid #1a5276; padding-bottom: 14px; margin-bottom: 20px; }
        .logo { font-size: 22px; font-weight: bold; color: #1a5276; letter-spacing: 0.5px; }
        .gen-date { font-size: 10px; color: #555; margin-top: 6px; }
        h2 { font-size: 12px; color: #ffffff; background: #1a5276; padding: 8px 10px; margin: 18px 0 0 0; }
        table { width: 100%; border-collapse: collapse; margin: 0 0 16px 0; font-size: 10px; }
        th { background: #e8f0f5; color: #1a5276; text-align: left; padding: 7px 8px; border: 1px solid #c5d9e8; font-weight: bold; }
        td { padding: 7px 8px; border: 1px solid #d0d0d0; vertical-align: top; }
        tr:nth-child(even) td { background: #fafafa; }
        .meta { margin-bottom: 16px; }
        .meta-row { margin-bottom: 4px; }
        .meta-label { font-weight: bold; color: #1a5276; display: inline-block; width: 120px; }
        .points-wrap { margin: 14px 0 18px 0; padding: 14px 18px; border: 2px solid #1a5276; background: #f0f6fa; text-align: center; }
        .points-label { font-size: 10px; color: #555; text-transform: uppercase; letter-spacing: 1px; }
        .points-value { font-size: 28px; font-weight: bold; color: #1a5276; margin-top: 4px; }
        .muted { color: #777; font-style: italic; }
        .footer { margin-top: 24px; padding-top: 10px; border-top: 1px solid #ccc; font-size: 9px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">E-Rep</div>
        <div class="gen-date">Report generated: {{ $generated_at->format('F j, Y \a\t g:i A') }}</div>
    </div>

    <div class="meta">
        <div class="meta-row"><span class="meta-label">Doctor name</span> {{ $doctor->full_name }}</div>
        <div class="meta-row"><span class="meta-label">Specialty</span> {{ $doctor->specialization }}</div>
        <div class="meta-row"><span class="meta-label">Email</span> {{ $doctor->email }}</div>
        <div class="meta-row"><span class="meta-label">Syndicate ID</span> {{ $doctor->syndicate_id ?? '—' }}</div>
    </div>

    <div class="points-wrap">
        <div class="points-label">Total points earned</div>
        <div class="points-value">{{ (int) $total_points }}</div>
    </div>

    <h2>Meetings</h2>
    <table>
        <thead>
            <tr>
                <th>Rep name</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($meetings as $m)
                <tr>
                    <td>{{ $m->rep?->full_name ?? '—' }}</td>
                    <td>{{ $m->scheduled_at ? $m->scheduled_at->format('Y-m-d H:i') : '—' }}</td>
                    <td>{{ $m->status }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">No meetings.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Drug samples</h2>
    <table>
        <thead>
            <tr>
                <th>Drug name</th>
                <th>Quantity</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($samples as $s)
                <tr>
                    <td>{{ $s->drug?->name ?? $s->drug?->market_name ?? '—' }}</td>
                    <td>{{ $s->quantity }}</td>
                    <td>{{ $s->status }}</td>
                    <td>{{ $s->requested_at ? $s->requested_at->format('Y-m-d') : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No sample requests.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Events attended</h2>
    <table>
        <thead>
            <tr>
                <th>Event title</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($events as $er)
                <tr>
                    <td>{{ $er->event?->title ?? '—' }}</td>
                    <td>{{ $er->event?->event_date ? $er->event->event_date->format('Y-m-d H:i') : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="muted">No approved events.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Rewards redeemed</h2>
    <table>
        <thead>
            <tr>
                <th>Reward name</th>
                <th>Points used</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($redemptions as $r)
                <tr>
                    <td>{{ $r->reward?->name ?? $r->reward?->title ?? '—' }}</td>
                    <td>{{ $r->points_spent }}</td>
                    <td>{{ $r->redeemed_at ? $r->redeemed_at->format('Y-m-d') : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">No redemptions.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">E-Rep confidential medical summary — for authorized use only.</p>
</body>
</html>
