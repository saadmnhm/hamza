<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QR codes — {{ $group->name }}</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
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
            max-width: 190mm;
            margin: 0 auto;
            padding: 8mm 0 4mm;
        }

        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8mm;
            border-bottom: 2px solid #111827;
            padding-bottom: 5mm;
            margin-bottom: 6mm;
        }

        .header h1 {
            margin: 0 0 2mm;
            font-size: 18pt;
            line-height: 1.2;
        }

        .header p {
            margin: 0 0 1.5mm;
            font-size: 9pt;
            color: #4b5563;
        }

        .group-qr {
            flex-shrink: 0;
            text-align: center;
            font-size: 7.5pt;
            color: #4b5563;
        }

        .group-qr svg {
            display: block;
            width: 32mm;
            height: 32mm;
            margin: 0 auto 1.5mm;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 3mm;
            margin: 7mm 0 3mm;
            font-size: 11pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #111827;
        }

        .section-title::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #d1d5db;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 4mm;
        }

        .label {
            display: flex;
            align-items: center;
            gap: 4mm;
            border: 1px dashed #9ca3af;
            border-radius: 3mm;
            padding: 3.5mm;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .label svg {
            display: block;
            width: 28mm;
            height: 28mm;
            flex-shrink: 0;
        }

        .label-body {
            min-width: 0;
        }

        .badge {
            display: inline-block;
            border: 1px solid #d1d5db;
            border-radius: 999px;
            padding: .4mm 2mm;
            margin-bottom: 1.5mm;
            font-size: 7pt;
            font-weight: 600;
            color: #374151;
        }

        .label-title {
            margin: 0 0 1mm;
            font-size: 9.5pt;
            font-weight: 700;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        .label-meta {
            margin: 0;
            font-size: 7.5pt;
            line-height: 1.4;
            color: #4b5563;
        }

        .label-token {
            margin-top: 1.5mm;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 6.5pt;
            color: #9ca3af;
            overflow-wrap: anywhere;
        }

        .empty {
            margin: 4mm 0;
            font-size: 9pt;
            color: #6b7280;
            font-style: italic;
        }

        @media print {
            .controls {
                display: none !important;
            }

            .page {
                max-width: none;
                padding: 0;
            }

            .label {
                border-color: #d1d5db;
            }
        }

        @media (max-width: 700px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
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
        <header class="header">
            <div>
                <h1>{{ $group->name }}</h1>
                @if ($group->description)
                    <p>{{ $group->description }}</p>
                @endif
                <p>
                    {{ $group->items->count() }} élément(s) ·
                    {{ $documents->count() }} document(s) ·
                    {{ $clients->count() }} client(s)
                </p>
                <p>
                    Créé par {{ $group->user?->name ?? 'utilisateur supprimé' }}
                    le {{ $group->created_at?->format('d/m/Y à H:i') }}
                </p>
            </div>
            <div class="group-qr">
                {!! \App\Support\QrCode::svg($groupUrl, 140) !!}
                <div>QR du groupe</div>
            </div>
        </header>

        @if ($documents->isNotEmpty())
            <div class="section-title">Documents ({{ $documents->count() }})</div>
            <div class="grid">
                @foreach ($documents as $item)
                    <div class="label">
                        {!! \App\Support\QrCode::svg($item->url(), 110) !!}
                        <div class="label-body">
                            <span class="badge">{{ $item->typeLabel() }}</span>
                            <p class="label-title">{{ $item->label }}</p>
                            <p class="label-meta">
                                @if (data_get($item->meta, 'number'))
                                    N° {{ data_get($item->meta, 'number') }}<br>
                                @endif
                                @if (data_get($item->meta, 'client'))
                                    {{ data_get($item->meta, 'client') }}<br>
                                @endif
                                @if (data_get($item->meta, 'date'))
                                    {{ data_get($item->meta, 'date') }}
                                @endif
                            </p>
                            <div class="label-token">{{ $item->public_token }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($clients->isNotEmpty())
            <div class="section-title">Clients ({{ $clients->count() }})</div>
            <div class="grid">
                @foreach ($clients as $item)
                    <div class="label">
                        {!! \App\Support\QrCode::svg($item->url(), 110) !!}
                        <div class="label-body">
                            <span class="badge">{{ $item->typeLabel() }}</span>
                            <p class="label-title">{{ $item->label }}</p>
                            <p class="label-meta">
                                @if (data_get($item->meta, 'email'))
                                    {{ data_get($item->meta, 'email') }}<br>
                                @endif
                                @if (data_get($item->meta, 'phone'))
                                    {{ data_get($item->meta, 'phone') }}<br>
                                @endif
                                @if (data_get($item->meta, 'city'))
                                    {{ data_get($item->meta, 'city') }}
                                @endif
                            </p>
                            <div class="label-token">{{ $item->public_token }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($documents->isEmpty() && $clients->isEmpty())
            <p class="empty">Ce groupe ne contient aucun élément.</p>
        @endif
    </div>
</body>
</html>
