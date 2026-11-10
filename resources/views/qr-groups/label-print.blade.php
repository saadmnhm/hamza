<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QR code — {{ $title }}</title>
    <style>
        @page {
            size: A4;
            margin: 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #111827;
            font-family: "Segoe UI", Tahoma, Arial, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .controls {
            position: fixed;
            top: 12px;
            right: 12px;
            display: flex;
            gap: 8px;
            z-index: 10;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #d4d4d8;
            border-radius: 8px;
            background: #fff;
            color: #111827;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .08);
        }

        .btn-primary {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }

        .page {
            display: flex;
            min-height: 273mm;
            align-items: center;
            justify-content: center;
        }

        .label {
            display: flex;
            align-items: center;
            gap: 10mm;
            width: 150mm;
            max-width: 100%;
            border: 1px dashed #9ca3af;
            border-radius: 4mm;
            padding: 10mm;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .label svg {
            display: block;
            width: 52mm;
            height: 52mm;
            flex-shrink: 0;
        }

        .label-body {
            min-width: 0;
        }

        .badge {
            display: inline-block;
            border: 1px solid #d1d5db;
            border-radius: 999px;
            padding: .5mm 2.5mm;
            margin-bottom: 3mm;
            font-size: 8pt;
            font-weight: 600;
            color: #374151;
        }

        h1 {
            margin: 0 0 2mm;
            font-size: 14pt;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        .meta {
            margin: 0 0 3mm;
            font-size: 9pt;
            line-height: 1.5;
            color: #4b5563;
        }

        .url {
            margin: 0;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 7pt;
            line-height: 1.4;
            color: #9ca3af;
            overflow-wrap: anywhere;
        }

        @media print {
            .controls {
                display: none !important;
            }

            .page {
                min-height: 0;
                height: 100vh;
            }

            .label {
                border-color: #d1d5db;
            }
        }
    </style>
</head>
<body>
    <div class="controls">
        <button type="button" class="btn btn-primary" onclick="window.print()">Imprimer</button>
        <button type="button" class="btn" onclick="window.close()">Fermer</button>
    </div>

    <div class="page">
        <div class="label">
            {!! \App\Support\QrCode::svg($url, 220) !!}
            <div class="label-body">
                <span class="badge">{{ $subtitle }}</span>
                <h1>{{ $title }}</h1>
                @if (count($meta) > 0)
                    <p class="meta">
                        @foreach ($meta as $line)
                            {{ $line }}@if (! $loop->last)<br>@endif
                        @endforeach
                    </p>
                @endif
                <p class="url">{{ $url }}</p>
            </div>
        </div>
    </div>
</body>
</html>
