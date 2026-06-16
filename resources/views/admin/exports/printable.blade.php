<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Tahoma, 'Segoe UI', sans-serif; color: #111827; margin: 30px; }
        h1 { margin: 0 0 8px; font-size: 28px; }
        p.meta { margin: 0 0 24px; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-size: 13px; vertical-align: top; }
        th { background: #f3f4f6; font-weight: 800; }
        .summary { display: flex; gap: 12px; flex-wrap: wrap; margin: 16px 0 8px; }
        .summary div { border: 1px solid #e5e7eb; border-radius: 12px; padding: 10px 14px; min-width: 160px; }
        .summary span { display: block; color: #6b7280; font-size: 12px; margin-bottom: 6px; }
    </style>
</head>
<body onload="window.print()">
    <h1>{{ $title }}</h1>
    <p class="meta">{{ $subtitle }}</p>

    @if(!empty($summary))
        <div class="summary">
            @foreach($summary as $label => $value)
                <div>
                    <span>{{ $label }}</span>
                    <strong>{{ $value }}</strong>
                </div>
            @endforeach
        </div>
    @endif

    <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}">لا توجد بيانات.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
