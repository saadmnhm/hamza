        <!DOCTYPE html>
        <html lang="fr" dir="ltr" class="print-page">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Fiche de paie - {{ $company?->raison_sociale ?? 'Entreprise' }}</title>
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
                    border-bottom: 2px solid #3498db;
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
                .table-row {
                    display: flex;
                    justify-content: space-between;
                    padding: 5px 0;
                    border-bottom: 1px solid #eee;
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
                <h1>Fiche de paie</h1>
            </div>

            <div class="company-info">
                <p><strong>Entreprise :</strong> {{ $company?->raison_sociale ?? '' }}</p>
                <p><strong>SIREN (ICE) :</strong> {{ $company?->ice ?? '' }}</p>
                <p><strong>Période :</strong> du {{ date('d/m/Y', strtotime($data['period_start'])) }} au {{ date('d/m/Y', strtotime($data['period_end'])) }}</p>
            </div>

            <div class="document-title">Fiche de paie</div>

            <div class="section">
                <div class="section-title">Informations de l'employé</div>
                <div class="field-row">
                    <span class="field-label">Employé :</span>
                    <span class="field-value">{{ $data['employee_name'] }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Matricule :</span>
                    <span class="field-value">{{ $data['employee_matricule'] ?? 'Non attribué' }}</span>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Détails de la rémunération</div>

                <div class="table-row">
                    <span><strong>Salaire de base</strong></span>
                    <span><strong>{{ number_format($data['basic_salary'], 2, ',', ' ') }} DH</strong></span>
                </div>

                @if ($data['allowances'] ?? null)
                <div class="table-row">
                    <span>Indemnités</span>
                    <span>{{ number_format($data['allowances'], 2, ',', ' ') }} DH</span>
                </div>
                @endif

                @if ($data['deductions'] ?? null)
                <div class="table-row">
                    <span>Déditions</span>
                    <span>{{ number_format($data['deductions'], 2, ',', ' ') }} DH</span>
                </div>
                @endif

                <div class="table-row">
                    <span><strong>Total net</strong></span>
                    <span><strong>{{ number_format($data['net_salary'], 2, ',', ' ') }} DH</strong></span>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Informations de paiement</div>
                <div class="field-row">
                    <span class="field-label">Moyen de paiement :</span>
                    <span class="field-value">{{ $data['payment_method'] }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Date de paiement :</span>
                    <span class="field-value">{{ date('d/m/Y', strtotime($data['payment_date'])) }}</span>
                </div>
            </div>

            <div class="signature-section">
                <h3>Signature</h3>
                <p>Certification : La présente fiche de paie certifie que les montants ci-dessus sont corrects.</p>
                <p style="margin: 20px 0;"></p>
                <p>_________________________________</p>
                <p style="font-size: 9pt;">Responsable RH - Signature</p>
                <p>Le : {{ date('d/m/Y', strtotime($data['payment_date'])) }}</p>
            </div>
        </body>
        </html>