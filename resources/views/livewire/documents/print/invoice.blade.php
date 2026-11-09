@php
    $client = $data['client'] ?? null;
    $items = $data['items'] ?? null;
    $isCompany = ($client['type'] ?? 'company') === 'company';

    $clientName = $client['name'] ?? ($data['client_name'] ?? '');
    $clientAddress = $client['address'] ?? ($data['client_address'] ?? '');
    $clientCityLine = trim(($client['postal_code'] ?? '').' '.($client['city'] ?? ''));
    $clientPhone = $client['phone'] ?? '';
    $clientEmail = $client['email'] ?? '';
    $clientVat = $client['ice'] ?? ($data['client_vat_number'] ?? '');

    $subtotal = $data['subtotal'] ?? 0;
    $tvaTotal = $data['tva_total'] ?? ($data['tax_amount'] ?? 0);
    $total = $data['total'] ?? 0;
@endphp
        <!DOCTYPE html>
        <html lang="fr" dir="ltr" class="print-page">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{{ $data['document_title'] ?? 'Facture' }} - {{ $company?->raison_sociale ?? 'Entreprise' }}</title>
            <style>
                @page {
                    size: A4;
                    margin: 2cm;
                }
                @media print {
                    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                }
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    font-size: 11pt;
                    line-height: 1.5;
                    color: #333;
                    max-width: 210mm;
                    margin: 0 auto;
                }
                h1, h2, h3 {
                    color: #2c3e50;
                    margin-top: 0;
                }
                .header {
                    border-bottom: 2px solid #e74c3c;
                    padding-bottom: 10px;
                    margin-bottom: 20px;
                }
                .footer {
                    border-top: 1px solid #ddd;
                    padding-top: 10px;
                    margin-top: 40px;
                    font-size: 9pt;
                    color: #666;
                    text-align: center;
                }
                .company-info {
                    margin-bottom: 30px;
                }
                .company-info p {
                    margin: 5px 0;
                }
                .document-title {
                    text-align: center;
                    font-size: 24pt;
                    font-weight: bold;
                    color: #2c3e50;
                    margin: 40px 0 20px;
                    padding-bottom: 20px;
                    border-bottom: 1px solid #bdc3c7;
                }
                .section {
                    margin-bottom: 25px;
                }
                .section-title {
                    font-size: 13pt;
                    font-weight: 600;
                    color: #2c3e50;
                    border-bottom: 1px solid #bdc3c7;
                    padding-bottom: 5px;
                    margin-bottom: 15px;
                }
                .field-row {
                    margin-bottom: 10px;
                }
                .field-label {
                    font-weight: 600;
                    display: inline-block;
                    width: 40%;
                }
                .field-value {
                    display: inline-block;
                    width: 60%;
                }
                .invoice-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }
                .invoice-table th, .invoice-table td {
                    border: 1px solid #ddd;
                    padding: 8px 5px;
                    font-size: 9pt;
                    vertical-align: top;
                }
                .invoice-table th {
                    background-color: #e74c3c;
                    color: white;
                    font-weight: bold;
                    text-align: left;
                }
                .invoice-table .num {
                    text-align: right;
                    white-space: nowrap;
                }
                .invoice-table .total-row {
                    font-weight: bold;
                    background-color: #f8f9fa;
                }
                .totals {
                    width: 45%;
                    margin-left: auto;
                    margin-top: 10px;
                }
                .totals .field-row {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 6px;
                }
                .totals .field-row strong {
                    font-weight: 600;
                }
                .signature-section {
                    margin-top: 50px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                }
            </style>
        </head>
        <body class="print-page">
            <div class="header">
                <h1>{{ $data['document_title'] ?? 'Facture' }}</h1>
            </div>

            <div class="company-info">
                <p><strong>Entreprise :</strong> {{ $company?->raison_sociale ?? '' }}</p>
                <p><strong>ICE :</strong> {{ $company?->ice ?? '' }}</p>
                <p><strong>Adresse :</strong> {{ $company?->fullAddress() ?? '' }}</p>
                <p><strong>Téléphone :</strong> {{ $company?->telephone ?? '' }}</p>
            </div>

            <div class="document-title">{{ $data['document_title'] ?? 'Facture' }}</div>

            <div class="section">
                <div class="section-title">Détails du {{ strtolower($data['document_title'] ?? 'document') }}</div>
                <div class="field-row">
                    <span class="field-label">N° de facture :</span>
                    <span class="field-value">{{ $data['invoice_number'] ?? '' }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Date :</span>
                    <span class="field-value">
                        {{ ! empty($data['invoice_date']) ? date('d/m/Y', strtotime($data['invoice_date'])) : '' }}
                    </span>
                </div>
                @if (! empty($data['due_date']))
                    <div class="field-row">
                        <span class="field-label">Échéance :</span>
                        <span class="field-value">{{ date('d/m/Y', strtotime($data['due_date'])) }}</span>
                    </div>
                @endif
            </div>

            <div class="section">
                <div class="section-title">Coordonnées du client</div>
                <div class="field-row">
                    <span class="field-label">Nom du client :</span>
                    <span class="field-value">{{ $clientName }}</span>
                </div>

                @if ($client !== null)
                    @if ($isCompany)
                        <div class="field-row">
                            <span class="field-label">ICE :</span>
                            <span class="field-value">{{ ($client['ice'] ?? null) ?: 'Non applicable' }}</span>
                        </div>
                        <div class="field-row">
                            <span class="field-label">IF :</span>
                            <span class="field-value">{{ ($client['if_number'] ?? null) ?: 'Non applicable' }}</span>
                        </div>
                        <div class="field-row">
                            <span class="field-label">RC :</span>
                            <span class="field-value">{{ ($client['rc'] ?? null) ?: 'Non applicable' }}</span>
                        </div>
                        <div class="field-row">
                            <span class="field-label">CNSS :</span>
                            <span class="field-value">{{ ($client['cnss'] ?? null) ?: 'Non applicable' }}</span>
                        </div>
                    @else
                        <div class="field-row">
                            <span class="field-label">CIN :</span>
                            <span class="field-value">{{ ($client['cin'] ?? null) ?: 'Non applicable' }}</span>
                        </div>
                    @endif
                @else
                    <div class="field-row">
                        <span class="field-label">N° TVA :</span>
                        <span class="field-value">{{ $clientVat ?: 'Non applicable' }}</span>
                    </div>
                @endif

                <div class="field-row">
                    <span class="field-label">Adresse :</span>
                    <span class="field-value">{{ trim($clientAddress.' '.$clientCityLine) ?: 'Non renseignée' }}</span>
                </div>
                @if ($clientPhone)
                    <div class="field-row">
                        <span class="field-label">Téléphone :</span>
                        <span class="field-value">{{ $clientPhone }}</span>
                    </div>
                @endif
                @if ($clientEmail)
                    <div class="field-row">
                        <span class="field-label">E-mail :</span>
                        <span class="field-value">{{ $clientEmail }}</span>
                    </div>
                @endif
            </div>

            <div class="section">
                <div class="section-title">Produits & services</div>

                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>Produit / service</th>
                            <th>Description</th>
                            <th class="num">Qté</th>
                            <th class="num">PU HT</th>
                            <th class="num">TVA</th>
                            <th class="num">TVA (DH)</th>
                            <th class="num">Total HT</th>
                            <th class="num">Total TTC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (is_array($items) && $items !== [])
                            @foreach ($items as $item)
                                <tr>
                                    <td>{{ $item['product'] ?? '' }}</td>
                                    <td>{{ $item['description'] ?? '' }}</td>
                                    <td class="num">{{ rtrim(rtrim(number_format((float) ($item['quantity'] ?? 0), 3, ',', ' '), '0'), ',') }}</td>
                                    <td class="num">{{ number_format((float) ($item['unit_price'] ?? 0), 2, ',', ' ') }} DH</td>
                                    <td class="num">{{ number_format((float) ($item['tva_rate'] ?? 0), 0, ',', ' ') }} %</td>
                                    <td class="num">{{ number_format((float) ($item['tva_amount'] ?? 0), 2, ',', ' ') }} DH</td>
                                    <td class="num">{{ number_format((float) ($item['total_ht'] ?? 0), 2, ',', ' ') }} DH</td>
                                    <td class="num">{{ number_format((float) ($item['total_ttc'] ?? 0), 2, ',', ' ') }} DH</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td>{{ $data['items_description'] ?? '' }}</td>
                                <td>—</td>
                                <td class="num">{{ $data['items_quantity'] ?? '' }}</td>
                                <td class="num">{{ number_format((float) ($data['items_unit_price'] ?? 0), 2, ',', ' ') }} DH</td>
                                <td class="num">{{ number_format((float) ($data['tax_rate'] ?? 0), 0, ',', ' ') }} %</td>
                                <td class="num">{{ number_format((float) ($data['tax_amount'] ?? 0), 2, ',', ' ') }} DH</td>
                                <td class="num">{{ number_format((float) ($data['items_total'] ?? 0), 2, ',', ' ') }} DH</td>
                                <td class="num">{{ number_format((float) ($data['total'] ?? 0), 2, ',', ' ') }} DH</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <div class="totals">
                    <div class="field-row">
                        <span>Total HT</span>
                        <strong>{{ number_format((float) $subtotal, 2, ',', ' ') }} DH</strong>
                    </div>
                    <div class="field-row">
                        <span>Total TVA</span>
                        <strong>{{ number_format((float) $tvaTotal, 2, ',', ' ') }} DH</strong>
                    </div>
                    <div class="field-row">
                        <span>Total TTC</span>
                        <strong>{{ number_format((float) $total, 2, ',', ' ') }} DH</strong>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Conditions de paiement</div>
                <div class="field-row">
                    <span class="field-label">Moyens de paiement :</span>
                    <span class="field-value">{{ $data['payment_terms'] ?? '' }}</span>
                </div>
                @if (! empty($data['notes']))
                <div class="field-row">
                    <span class="field-label">Notes :</span>
                    <span class="field-value" style="white-space: pre-wrap;">{{ $data['notes'] }}</span>
                </div>
                @endif
            </div>

            <div class="signature-section">
                <h3>Signature</h3>
                <p>Le présent document est établi sous notre responsabilité.</p>
                <p style="margin: 20px 0;"></p>
                <p>_________________________________</p>
                <p style="font-size: 9pt;">Gestionnaire - Signature</p>
                <p>Le : {{ ! empty($data['invoice_date']) ? date('d/m/Y', strtotime($data['invoice_date'])) : date('d/m/Y') }}</p>
            </div>

            @if (! empty($data['qr_url']))
                <div style="margin-top: 30px; text-align: center;">
                    <p style="font-size: 9pt; color: #666; margin-bottom: 8px;">Scannez pour accéder à l'archive</p>
                    {!! \App\Support\QrCode::svg($data['qr_url'], 90) !!}
                </div>
            @endif
        </body>
        </html>
