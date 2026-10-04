@php abort_unless(isset($student), 404) @endphp
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Fiche d'Inscription - {{ config('etablissement.nom') }}</title>
    <style>
        @page {
            margin: 8mm;
            size: A4;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
        }

        .contact-info {
            font-size: 10px;
            color: #666;
            margin-bottom: 8px;
        }

        .logo-section {
            width: 100%;
            margin-bottom: 12px;
        }

        .logo {
            width: 55px;
            height: 55px;
            background-color: #1e40af;
            color: white;
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
            font-size: 20px;
            border-radius: 6px;
            line-height: 55px;
        }

        .matricule-box {
            border: 2px solid #1e40af;
            padding: 6px;
            width: 120px;
            text-align: center;
            font-size: 10px;
        }

        .title {
            color: #1e40af;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin: 6px 0;
        }

        .year-text {
            font-size: 11px;
            margin: 4px 0;
        }

        .school-name {
            color: #1e40af;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
        }

        .section {
            margin-bottom: 12px;
        }

        .section-title {
            background-color: #f0f4ff;
            padding: 5px 8px;
            font-weight: bold;
            color: #1e40af;
            border-left: 4px solid #1e40af;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .form-table td {
            padding: 4px 6px 4px 0;
            vertical-align: middle;
        }

        .label {
            font-weight: 500;
            white-space: nowrap;
        }

        .input-line {
            border-bottom: 1px solid #333;
            display: inline-block;
            min-width: 140px;
            height: 16px;
            margin-right: 12px;
        }

        .input-line-long {
            border-bottom: 1px solid #333;
            display: inline-block;
            min-width: 160px;
            height: 16px;
        }

        .checkbox {
            width: 12px;
            height: 12px;
            border: 1px solid #333;
            display: inline-block;
            margin-right: 4px;
            vertical-align: middle;
        }

        .checkbox-label {
            margin-right: 15px;
            font-size: 11px;
        }

        .montant-input {
            border-bottom: 1px solid #333;
            display: inline-block;
            width: 130px;
            height: 16px;
            margin-right: 15px;
        }

        .nature-input {
            border-bottom: 1px solid #333;
            display: inline-block;
            width: 100px;
            height: 16px;
        }

        .date-input {
            width: 25px;
            height: 16px;
            border-bottom: 1px solid #333;
            display: inline-block;
            text-align: center;
            margin: 0 2px;
        }

        .code-input {
            border-bottom: 1px solid #333;
            display: inline-block;
            width: 140px;
            height: 16px;
            margin-left: 15px;
        }

        .compagnie-input {
            border-bottom: 1px solid #333;
            display: inline-block;
            width: 180px;
            height: 16px;
            margin-left: 8px;
        }

        .signature-section {
            text-align: right;
            margin: 8px 0;
        }

        .signature-input {
            width: 140px;
            height: 16px;
            border-bottom: 1px solid #333;
            display: inline-block;
        }

        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 10px;
        }

        .payments-table th,
        .payments-table td {
            border: 1px solid #333;
            padding: 4px 3px;
            text-align: left;
            vertical-align: middle;
        }

        .payments-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
            font-size: 10px;
        }

        .amount-cell {
            width: 18%;
            text-align: center;
        }

        .date-cell {
            width: 12%;
            text-align: center;
        }

        .visa-cell {
            width: 6%;
            text-align: center;
        }

        .description-cell {
            width: 40%;
        }

        .penalties-cell {
            width: 24%;
            text-align: center;
        }

        .footer-note {
            font-size: 9px;
            color: #666;
            margin-top: 12px;
            text-align: justify;
            line-height: 1.2;
        }

        .red-text {
            color: #dc2626;
        }

        .blue-text {
            color: #1e40af;
        }

        .small-checkbox {
            width: 10px;
            height: 10px;
            border: 1px solid #333;
            display: inline-block;
        }

        .compact-row {
            display: inline-block;
            margin-right: 18px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="contact-info">
            Lieu : {{ config('etablissement.adresse') }} — Tél : {{ config('etablissement.telephone') }}@if(config('etablissement.email')) — Email : {{ config('etablissement.email') }}@endif
        </div>

        <!-- Logo conditionnel -->
        <div style="text-align: center; margin-bottom: 12px;">
            @if($student->formation_name == 'ISM')
                <img src="{{ $logoPath }}" alt="Logo ISM" style="width: 100px; height: 100px;">
            @else
                <img src="{{ $logo_ifpm }}" alt="Logo IFPM" style="width: 100px; height: 100px;">
            @endif
        </div>

        <!-- Boîte matricule à droite -->
        <div style="text-align: left; margin-top: -40px;">
            <div class="matricule-box">
                <div class="small-checkbox" style="margin-bottom: 2px;"></div>
                <div style="font-size: 10px;">Matricule Apprenant</div>
            </div>
        </div>

        <!-- Titre et année -->
        <div style="text-align: center;">
            <div class="title">FICHE D'INSCRIPTION</div>
            <div class="year-text">20........../20.............</div>
        </div>

        <div class="school-name">{{ config('etablissement.nom') }}</div>
    </div>

    <div class="section">
        <div class="section-title">I. IDENTITÉ DE L'ÉTUDIANT</div>

        <div style="margin-bottom: 6px;">
            <span class="label">Statut :</span>
            <span class="checkbox-label"><span class="checkbox"></span>Mme</span>
            <span class="checkbox-label"><span class="checkbox"></span>Mlle</span>
            <span class="checkbox-label"><span class="checkbox"></span>M</span>
        </div>

        <table class="form-table">
            <tr>
                <td style="width: 50%;">
                    <span class="label">Nom(s):</span>
                    <span class="input-line-long"> {{$student->name}}</span>
                </td>
                <td style="width: 50%;">
                    <span class="label">Prénom(s):</span>
                    <span class="input-line-long">{{$student->last_name}}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Né(e) le :</span>
                    <span class="input-line-long">  {{$student->birth}}</span>
                </td>
                <td>
                    <span class="label">Nationalité:</span>
                    <span class="input-line-long">CAMEROUNAIS</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Region d'origine :</span>
                    <span class="input-line-long">{{ $student->region?->name ?? "—" }}</span>
                </td>
                <td>
                    <span class="label">Département :</span>
                    <span class="input-line-long">{{ $student->departement?->name ?? "—" }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Externe :</span>
                    <span class="input-line-long"></span>
                </td>
                <td>
                    <span class="label">Interne :</span>
                    <span class="input-line-long"></span>
                </td>
            </tr>
            <tr>
                <td >
                    <span class="label">Tél :</span>
                    <span class="input-line-long">{{$student->contact}}</span>
                </td>
                 <td>
                    <span class="label">Email :</span>
                    <span class="input-line-long">{{$student->email}}</span>
                </td>
            </tr>
             <tr>
                <td >
                    <span class="label">Nom du père :</span>
                    <span class="input-line-long">{{$student->father}}</span>
                </td>
                 <td>
                    <span class="label">Nom de la mère :</span>
                    <span class="input-line-long">{{$student->mother}}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">II. ADMISSION</div>

        <div style="margin-bottom: 6px;">
            <span class="compact-row">
                <span class="label">Formation : {{$student->formation_name}} </span>
            </span>
            <span class="compact-row">
                <span class="label">Niveau : I</span>
            </span>

            @if($student->formation_name == 'ISM')
                <span class="compact-row">
                    <span class="label">Cycle : {{ $student->cycle?->name ?? "—" }}</span>
                </span>
            @endif
        </div>

        <div>
            <span class="label">Specialité :</span>
            <span class="montant-input">  {{ $student->specialite?->name ?? "—" }} </span>
            <span class="label">Filiere :</span>
            <span class="nature-input">{{ $student->filiere?->name ?? "—" }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">III. PRISE EN CHARGE</div>

        <div style="margin-bottom: 6px;">
            <span class="label">Assurance maladie ISM Oui</span>
            <span class="checkbox"></span>
            <span class="label">*, sinon précisez votre Compagnie</span>
            <span class="compagnie-input"></span>
        </div>

        <div>
            <span class="label">Date de la visite médicale :</span>
            <span class="date-input"></span>/<span class="date-input"></span>/<span class="date-input"></span>
            <span class="label">Code:</span>
            <span class="code-input"></span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">IV. VALIDATION DES PAIEMENTS</div>
        <div class="signature-section">
            <span class="label">Signature du Directeur</span>
            <span class="signature-input"></span>
        </div>

        <table class="payments-table">
            <thead>
                <tr>
                    <th class="description-cell">DESCRIPTION</th>
                    <th class="amount-cell">MONTANT</th>
                    <th class="date-cell">DATE</th>
                    <th class="visa-cell">VISA</th>
                    <th class="penalties-cell">PENALITÉS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="description-cell">Inscription <span class="red-text">(non remboursable)</span></td>
                    <td class="amount-cell">50 000 FCFA</td>
                    <td class="date-cell">__/__/20__</td>
                    <td class="visa-cell">□</td>
                    <td class="penalties-cell"><span class="blue-text">Inscription</span></td>
                </tr>
                <tr>
                    <td class="description-cell">Scolarité <span class="red-text">(non remboursable)</span></td>
                    <td class="amount-cell">{{number_format($student->specialite?->price ?? 0)}} FCFA</td>
                    <td class="date-cell">__/__/20__</td>
                    <td class="visa-cell">□</td>
                    <td class="penalties-cell"><span class="blue-text">Scolarité</span></td>
                </tr>
                <tr>
                    <td class="description-cell">Association des apprenantes</td>
                    <td class="amount-cell">85 000 FCFA</td>
                    <td class="date-cell">__/__/20__</td>
                    <td class="visa-cell">□</td>
                    <td class="penalties-cell"></td>
                </tr>
                <tr>
                    <td class="description-cell">Visite Médicale <span class="red-text">(obligatoire)</span></td>
                    <td class="amount-cell">84 500 FCFA</td>
                    <td class="date-cell">__/__/20__</td>
                    <td class="visa-cell">□</td>
                    <td class="penalties-cell"></td>
                </tr>
                <tr>
                    <td class="description-cell">1er versement</td>
                    <td class="amount-cell">____FCFA</td>
                    <td class="date-cell">__/__/20__</td>
                    <td class="visa-cell">□</td>
                    <td class="penalties-cell"><span class="blue-text">1ère tranche</span></td>
                </tr>
                <tr>
                    <td class="description-cell">2ème versement</td>
                    <td class="amount-cell">____FCFA</td>
                    <td class="date-cell">__/__/20__</td>
                    <td class="visa-cell">□</td>
                    <td class="penalties-cell"></td>
                </tr>
                <tr>
                    <td class="description-cell">3ème versement</td>
                    <td class="amount-cell">____FCFA</td>
                    <td class="date-cell">__/__/20__</td>
                    <td class="visa-cell">□</td>
                    <td class="penalties-cell"></td>
                </tr>
                <tr>
                    <td class="description-cell">4ème versement</td>
                    <td class="amount-cell">____FCFA</td>
                    <td class="date-cell">__/__/20__</td>
                    <td class="visa-cell">□</td>
                    <td class="penalties-cell"></td>
                </tr>
                <tr>
                    <td class="description-cell">5ème versement</td>
                    <td class="amount-cell">____FCFA</td>
                    <td class="date-cell">__/__/20__</td>
                    <td class="visa-cell">□</td>
                    <td class="penalties-cell"></td>
                </tr>
                <tr>
                    <td class="description-cell">6ème versement</td>
                    <td class="amount-cell">____FCFA</td>
                    <td class="date-cell">__/__/20__</td>
                    <td class="visa-cell">□</td>
                    <td class="penalties-cell"><span class="blue-text">2ème Tranche</span></td>
                </tr>
                <tr>
                    <td class="description-cell">7ème versement</td>
                    <td class="amount-cell">____FCFA</td>
                    <td class="date-cell">__/__/20__</td>
                    <td class="visa-cell">□</td>
                    <td class="penalties-cell"></td>
                </tr>
                <tr>
                    <td class="description-cell">8ème versement</td>
                    <td class="amount-cell">____FCFA</td>
                    <td class="date-cell">__/__/20__</td>
                    <td class="visa-cell">□</td>
                    <td class="penalties-cell"></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer-note">
        <strong>Fait à MBANKOMO</strong><br>
        <strong>NB:</strong> Après inscription le(s) candidat(s) ayant choisi l'enseignement décentralisé peut changer
        annuellement sa compagnie sur Demande après justification à la direction du Campus concerné. Les apprenants ayant
        opté pour un paiement mensuel ou trimestriel de leur scolarité s'il y a retard/défaut de paiement payent une
        pénalité de dix mille (10 000) francs CFA non remboursable pour permettre l'accès aux épreuves des Devoirs.
    </div>
</body>

</html>
