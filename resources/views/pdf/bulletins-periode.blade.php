<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10.5px; color: #1a1a1a; background: white; }
.page { padding: 28px 36px; }
.page-break { page-break-after: always; }
.header-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
.header-table td { vertical-align: middle; padding: 0; }
.header-left { width: 20%; text-align: center; }
.header-mid { width: 60%; text-align: center; }
.header-right { width: 20%; text-align: center; }
.logo { height: 70px; width: auto; }
.school-name-fr { font-size: 13px; font-weight: bold; color: #1a1a1a; }
.school-name-en { font-size: 11px; color: #444; margin-top: 2px; }
.doc-title { font-size: 15px; font-weight: bold; color: #1a1a1a; margin-top: 8px; letter-spacing: 0.5px; }
.doc-subtitle { font-size: 10px; color: #555; margin-top: 2px; }
.republic-fr { font-size: 10px; font-weight: bold; text-align: center; }
.motto { font-size: 9px; color: #777; text-align: center; margin-top: 2px; font-style: italic; }
.sep { border: none; border-top: 2px solid #1a1a1a; margin: 10px 0; }
.sep-thin { border: none; border-top: 1px solid #ccc; margin: 8px 0; }
.info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
.info-table td { padding: 3px 6px; font-size: 10.5px; vertical-align: top; }
.info-label { font-weight: bold; width: 30%; white-space: nowrap; }
.info-sep { width: 2%; }
.info-value { border-bottom: 1px solid #bbb; }
.main-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; font-size: 10px; }
.main-table th { background: #1a1a1a; color: white; padding: 6px 8px; text-align: left; font-size: 9.5px; font-weight: bold; }
.main-table th.center, .main-table td.center { text-align: center; }
.main-table th.right, .main-table td.right { text-align: right; }
.main-table td { padding: 5px 8px; border-bottom: 1px solid #e8e8e8; vertical-align: middle; }
.main-table tr:nth-child(even) td { background: #f9f9f9; }
.section-row td { background: #f0f0f0; font-weight: bold; font-size: 9.5px; padding: 4px 8px; border-top: 1px solid #ccc; border-bottom: 1px solid #ccc; text-transform: uppercase; }
.section-row-green td { background: #e8f5e9; color: #2e7d32; }
.section-row-red td { background: #fde8e8; color: #c62828; }
.total-row td { font-weight: bold; font-size: 10.5px; padding: 6px 8px; border-top: 2px solid #1a1a1a; background: #f5f5f5; }
.signatures { width: 100%; border-collapse: collapse; margin-top: 24px; }
.signatures td { padding: 0 8px; text-align: center; vertical-align: top; width: 33%; }
.sig-title { font-size: 9.5px; font-weight: bold; text-transform: uppercase; margin-bottom: 4px; }
.sig-name { font-size: 9px; color: #555; margin-top: 4px; }
.sig-line { border-top: 1px solid #999; margin-top: 28px; }
.footer { margin-top: 18px; border-top: 1px solid #ccc; padding-top: 8px; text-align: center; font-size: 8.5px; color: #888; }
.badge-fixe { font-size: 8px; color: #555; font-style: italic; }
.amount-pos { color: #1a6b1a; font-weight: bold; }
.amount-neg { color: #b71c1c; font-weight: bold; }
.amount-base { color: #1a237e; font-weight: bold; }
</style>
</head>
<body>
@foreach ($bulletins as $index => $b)
    @php
        $paiement   = $b['paiement'];
        $indemnites = $b['indemnites'];
        $retenues   = $b['retenues'];
        $moisNom    = $b['moisNom'];
    @endphp
    <div class="page {{ !$loop->last ? 'page-break' : '' }}">

        <table class="header-table">
            <tr>
                <td class="header-left"><img src="{{ public_path(config('etablissement.logo')) }}" class="logo" alt="{{ config('etablissement.sigle') }}"></td>
                <td class="header-mid">
                    <div class="school-name-fr">{{ config('etablissement.sigle') }}</div>
                    <div class="school-name-en">{{ config('etablissement.nom') }}</div>
                    <div class="doc-title">BULLETIN DE PAIE / PAY SLIP</div>
                    <div class="doc-subtitle">{{ $moisNom }} {{ $paiement->annee }}</div>
                </td>
                <td class="header-right">
                    <div class="republic-fr">REPUBLIQUE DU CAMEROUN</div>
                    <div class="motto">Paix — Travail — Patrie</div>
                    <div style="margin-top:6px;">
                        <div class="republic-fr">REPUBLIC OF CAMEROON</div>
                        <div class="motto">Peace — Work — Fatherland</div>
                    </div>
                </td>
            </tr>
        </table>

        <hr class="sep">

        <table class="info-table">
            <tr>
                <td class="info-label">Nom et Prénom(s) / Name :</td><td class="info-sep"></td>
                <td class="info-value"><strong>{{ strtoupper($paiement->employe->name) }} {{ $paiement->employe->lastname }}</strong></td>
                <td width="10%"></td>
                <td class="info-label">Matricule / Registration N° :</td><td class="info-sep"></td>
                <td class="info-value">{{ $paiement->employe->matricule ?? '—' }}</td>
            </tr>
            <tr>
                <td class="info-label">Profil / Position :</td><td class="info-sep"></td>
                <td class="info-value">{{ $paiement->profil?->nom ?? '—' }}</td>
                <td width="10%"></td>
                <td class="info-label">Échelon / Grade :</td><td class="info-sep"></td>
                <td class="info-value">@if ($paiement->echelon) Éch. {{ $paiement->echelon->numero }} — {{ $paiement->echelon->libelle }} @else — @endif</td>
            </tr>
            <tr>
                <td class="info-label">Période / Period :</td><td class="info-sep"></td>
                <td class="info-value">{{ $moisNom }} {{ $paiement->annee }}</td>
                <td width="10%"></td>
                <td class="info-label">Statut / Status :</td><td class="info-sep"></td>
                <td class="info-value">@if ($paiement->statut === 'paye') Payé / Paid @elseif ($paiement->statut === 'valide') Validé / Validated @else En attente / Pending @endif</td>
            </tr>
        </table>

        <hr class="sep-thin">

        <table class="main-table">
            <thead>
                <tr>
                    <th style="width:50%;">Désignation / Description</th>
                    <th class="center" style="width:15%;">Type</th>
                    <th class="right" style="width:20%;">Base / Rate</th>
                    <th class="right" style="width:15%;">Montant (FCFA)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Salaire de base / Base Salary</strong></td>
                    <td class="center">—</td><td class="right">—</td>
                    <td class="right amount-base">{{ number_format($paiement->salaire_base, 0, ',', ' ') }}</td>
                </tr>
                @if (!empty($indemnites))
                    <tr class="section-row section-row-green"><td colspan="4">+ INDEMNITÉS / ALLOWANCES</td></tr>
                    @foreach ($indemnites as $ind)
                        <tr>
                            <td>{{ $ind['libelle'] }}</td>
                            <td class="center"><span class="badge-fixe">{{ in_array($ind['type'], ['ajustement','ajustement_pct']) ? 'Ajustement' : ($ind['type'] === 'fixe' ? 'Fixe' : 'Pourcentage') }}</span></td>
                            <td class="right">{{ in_array($ind['type'], ['fixe','ajustement']) ? '—' : $ind['valeur'].'%' }}</td>
                            <td class="right amount-pos">+{{ number_format($ind['montant'], 0, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3" style="text-align:right; font-style:italic; font-size:9.5px; color:#2e7d32;">Sous-total indemnités</td>
                        <td class="right amount-pos">+{{ number_format($paiement->total_indemnites, 0, ',', ' ') }}</td>
                    </tr>
                @endif
                <tr style="background:#f0f0f0;">
                    <td colspan="3" style="font-weight:bold; font-size:10.5px;">Salaire brut / Gross Salary</td>
                    <td class="right" style="font-weight:bold;">{{ number_format($paiement->salaire_base + $paiement->total_indemnites, 0, ',', ' ') }}</td>
                </tr>
                @if (!empty($retenues))
                    <tr class="section-row section-row-red"><td colspan="4">— RETENUES / DEDUCTIONS</td></tr>
                    @foreach ($retenues as $ret)
                        <tr>
                            <td>{{ $ret['libelle'] }}</td>
                            <td class="center"><span class="badge-fixe">{{ in_array($ret['type'], ['ajustement','ajustement_pct']) ? 'Ajustement' : ($ret['type'] === 'fixe' ? 'Fixe' : 'Pourcentage') }}</span></td>
                            <td class="right">{{ in_array($ret['type'], ['fixe','ajustement']) ? '—' : $ret['valeur'].'%' }}</td>
                            <td class="right amount-neg">-{{ number_format($ret['montant'], 0, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3" style="text-align:right; font-style:italic; font-size:9.5px; color:#c62828;">Sous-total retenues</td>
                        <td class="right amount-neg">-{{ number_format($paiement->total_retenues, 0, ',', ' ') }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td colspan="3" style="text-align:right;">NET À PAYER / NET PAY</td>
                    <td class="right" style="font-size:13px;">{{ number_format($paiement->salaire_net, 0, ',', ' ') }}</td>
                </tr>
            </tbody>
        </table>

        <table class="signatures">
            <tr>
                <td><div class="sig-title">L'Employé</div><div class="sig-line"></div><div class="sig-name">{{ strtoupper($paiement->employe->name) }} {{ $paiement->employe->lastname }}</div></td>
                <td><div class="sig-title">Validé par</div><div class="sig-line"></div><div class="sig-name">{{ $paiement->validePar?->name ?? '—' }}</div></td>
                <td><div class="sig-title">La Direction</div><div class="sig-line"></div><div class="sig-name">Direction Générale</div></td>
            </tr>
        </table>

        <div class="footer">
            {{ config('etablissement.sigle') }} — {{ config('etablissement.nom') }} — {{ config('etablissement.adresse') }} &nbsp;|&nbsp; Bulletin {{ $moisNom }} {{ $paiement->annee }}
        </div>
    </div>
@endforeach
</body>
</html>
