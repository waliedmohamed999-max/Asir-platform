<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; }
        h1 { margin: 0 0 6px; font-size: 24px; }
        p.meta { margin: 0 0 18px; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; vertical-align: top; }
        th { background: #f3f4f6; font-weight: 700; }
        .summary { width: 100%; margin: 14px 0 8px; }
        .summary td { border: 1px solid #e5e7eb; padding: 10px 12px; width: 25%; }
        .summary span { display: block; color: #6b7280; font-size: 10px; margin-bottom: 4px; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p class="meta">{{ $subtitle }}</p>

    @if(!empty($summary))
        <table class="summary">
            <tr>
                @foreach($summary as $label => $value)
                    <td>
                        <span>{{ $label }}</span>
                        <strong>{{ $value }}</strong>
                    </td>
                @endforeach
            </tr>
        </table>
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
