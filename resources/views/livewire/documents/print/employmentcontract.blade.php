        <!DOCTYPE html>
        <html lang="fr" dir="ltr" class="print-page">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Contrat de travail - {{ $company?->raison_sociale ?? 'Entreprise' }}</title>
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
                .signature-section {
                    margin-top: 50px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                }
            </style>
        </head>
        <body class="print-page">
            <div class="header">
                <h1>Contrat de travail</h1>
            </div>

            <div class="company-info">
                <p><strong>Entreprise :</strong> {{ $company?->raison_sociale ?? '' }}</p>
                <p><strong>SIREN (ICE) :</strong> {{ $company?->ice ?? '' }}</p>
                <p><strong>Adresse :</strong> {{ $company?->adresse ?? '' }}, {{ $company?->code_postal ?? '' }} {{ $company?->ville ?? '' }}</p>
            </div>

            <div class="document-title">Contrat de travail</div>

            <div class="section">
                <div class="section-title">Informations de l'employé</div>
                <div class="field-row">
                    <span class="field-label">Nom et prénom :</span>
                    <span class="field-value">{{ $data['employee_name'] }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Poste :</span>
                    <span class="field-value">{{ $data['employee_position'] }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Département :</span>
                    <span class="field-value">{{ $data['employee_department'] }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Date d'embauche :</span>
                    <span class="field-value">{{ date('d/m/Y', strtotime($data['employee_hire_date'])) }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Salaire :</span>
                    <span class="field-value">{{ number_format($data['employee_salary'], 2, ',', ' ') }} DH</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Adresse :</span>
                    <span class="field-value">{{ $data['employee_address'] }}</span>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Informations de l'employeur</div>
                <div class="field-row">
                    <span class="field-label">Nom de l'employeur :</span>
                    <span class="field-value">{{ $data['employer_name'] }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Adresse de l'employeur :</span>
                    <span class="field-value">{{ $data['employer_address'] }}</span>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Conditions du contrat</div>
                <div class="field-row">
                    <span class="field-label">Durée du contrat :</span>
                    <span class="field-value">{{ $data['contract_duration'] }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Date de début :</span>
                    <span class="field-value">{{ date('d/m/Y', strtotime($data['contract_start_date'])) }}</span>
                </div>
            </div>

            <div class="signature-section">
                <h3>Signature</h3>
                <p>L'employé signe ci-dessous :</p>
                <p style="margin: 20px 0; border-bottom: 1px solid #ddd; padding-bottom: 10px;"></p>
                <p>_________________________________</p>
                <p style="font-size: 9pt;">Nom et signature de l'employé</p>
                <p>Le : {{ date('d/m/Y', strtotime($data['signing_date'])) }}</p>
                <br>
                <p>L'employeur signe ci-dessous :</p>
                <p style="margin: 20px 0; border-bottom: 1px solid #ddd; padding-bottom: 10px;"></p>
                <p>_________________________________</p>
                <p style="font-size: 9pt;">Nom et signature de l'employeur</p>
            </div>
        </body>
        </html>