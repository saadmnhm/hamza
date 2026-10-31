        <!DOCTYPE html>
        <html lang="fr" dir="ltr" class="print-page">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Facture - {{ $company?->raison_sociale ?? 'Entreprise' }}</title>
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
                    font-size: 10pt;
                }
                .invoice-table th {
                    background-color: #e74c3c;
                    color: white;
                    font-weight: bold;
                    text-align: left;
                }
                .invoice-table .total-row {
                    font-weight: bold;
                    background-color: #f8f9fa;
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
                <h1>Facture</h1>
            </div>

            <div class="company-info">
                <p><strong>Entreprise :</strong> {{ $company?->raison_sociale ?? '' }}</p>
                <p><strong>SIREN (ICE) :</strong> {{ $company?->ice ?? '' }}</p>
                <p><strong>Téléphone :</strong> {{ $company?->telephone ?? '' }}</p>
            </div>

            <div class="document-title">Facture</div>

            <div class="section">
                <div class="section-title">Coordonnées du client</div>
                <div class="field-row">
                    <span class="field-label">Nom du client :</span>
                    <span class="field-value">{{ $data['client_name'] }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Adresse :</span>
                    <span class="field-value">{{ $data['client_address'] }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">N° TVA :</span>
                    <span class="field-value">{{ $data['client_vat_number'] ?? 'Non applicable' }}</span>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Détails de la facture</div>

                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Quantité</th>
                            <th>Prix unitaire</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $data['items_description'] }}</td>
                            <td>{{ $data['items_quantity'] }}</td>
                            <td>{{ number_format($data['items_unit_price'], 2, ',', ' ') }} DH</td>
                            <td>{{ number_format($data['items_total'], 2, ',', ' ') }} DH</td>
                        </tr>
                    </tbody>
                </table>

                <div class="field-row total-row">
                    <span></span>
                    <span></span>
                    <span><strong>Sous-total</strong></span>
                    <span><strong>{{ number_format($data['subtotal'], 2, ',', ' ') }} DH</strong></span>
                </div>
                <div class="field-row total-row">
                    <span></span>
                    <span></span>
                    <span><strong>TVA ({{ number_format($data['tax_rate'], 0, ',', ' ') }}%)</strong></span>
                    <span><strong>{{ number_format($data['tax_amount'], 2, ',', ' ') }} DH</strong></span>
                </div>
                <div class="field-row total-row">
                    <span></span>
                    <span></span>
                    <span><strong>Total TTC</strong></span>
                    <span><strong>{{ number_format($data['total'], 2, ',', ' ') }} DH</strong></span>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Conditions de paiement</div>
                <div class="field-row">
                    <span class="field-label">Moyens de paiement :</span>
                    <span class="field-value">{{ $data['payment_terms'] }}</span>
                </div>
                @if ($data['notes'])
                <div class="field-row">
                    <span class="field-label">Notes :</span>
                    <span class="field-value" style="white-space: pre-wrap;">{{ $data['notes'] }}</span>
                </div>
                @endif
            </div>

            <div class="signature-section">
                <h3>Signature</h3>
                <p>La présente facture est établie sous notre responsabilité.</p>
                <p style="margin: 20px 0;"></p>
                <p>_________________________________</p>
                <p style="font-size: 9pt;">Gestionnaire - Signature</p>
                <p>Le : {{ date('d/m/Y', strtotime($data['invoice_date'])) }}</p>
            </div>
        </body>
        </html>