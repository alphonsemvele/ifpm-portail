<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 11px;
    color: #1e293b;
    background: #fff;
    padding: 0;
}

/* ── Page ── */
.page {
    width: 100%;
    min-height: 100%;
    padding: 28px 32px;
}

/* ── Header ── */
.header {
    display: table;
    width: 100%;
    border-bottom: 3px solid #1e3a8a;
    padding-bottom: 14px;
    margin-bottom: 20px;
}
.header-left  { display: table-cell; width: 50%; vertical-align: middle; }
.header-right { display: table-cell; width: 50%; vertical-align: middle; text-align: right; }

.logo { height: 56px; width: auto; }
.logo-placeholder {
    display: inline-block;
    width: 56px; height: 56px;
    background: #1e3a8a;
    border-radius: 8px;
    text-align: center; line-height: 56px;
    color: white; font-size: 18px; font-weight: bold;
}

.school-name   { font-size: 13px; font-weight: bold; color: #1e3a8a; margin-bottom: 2px; }
.school-sub    { font-size: 10px; color: #64748b; }
.bulletin-title{ font-size: 16px; font-weight: bold; color: #1e3a8a; }
.bulletin-period{ font-size: 11px; color: #64748b; margin-top: 3px; }

/* ── Employé info ── */
.emp-section {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 18px;
}
.emp-grid { display: table; width: 100%; }
.emp-col  { display: table-cell; width: 50%; vertical-align: top; }
.emp-label { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin-bottom: 2px; }
.emp-value { font-size: 11px; font-weight: bold; color: #1e293b; }
.emp-sub   { font-size: 10px; color: #64748b; margin-top: 1px; }

/* ── Tableau salaire ── */
.sal-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 16px;
}
.sal-table th {
    background: #0f172a;
    color: white;
    font-size: 9px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 8px 10px;
    text-align: left;
}
.sal-table th.right { text-align: right; }
.sal-table td {
    padding: 7px 10px;
    font-size: 10px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
}
.sal-table td.right  { text-align: right; }
.sal-table tr:nth-child(even) td { background: #f8fafc; }
.sal-table tr:hover td { background: #f1f5f9; }

/* Type badge */
.badge {
    display: inline-block;
    font-size: 8px; font-weight: bold;
    padding: 1px 6px; border-radius: 10px;
    margin-left: 4px;
}
.badge-green { background: #dcfce7; color: #15803d; }
.badge-red   { background: #fee2e2; color: #dc2626; }

/* Montant coloré */
.amount-green { color: #15803d; font-weight: bold; }
.amount-red   { color: #dc2626; font-weight: bold; }
.amount-blue  { color: #1d4ed8; font-weight: bold; }

/* ── Récapitulatif ── */
.recap {
    display: table;
    width: 100%;
    margin-bottom: 18px;
    border-collapse: separate;
    border-spacing: 8px 0;
}
.recap-cell {
    display: table-cell;
    width: 25%;
    padding: 10px 12px;
    border-radius: 8px;
    text-align: center;
    vertical-align: middle;
}
.recap-label { font-size: 9px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 4px; }
.recap-value { font-size: 13px; font-weight: bold; }
.recap-unit  { font-size: 8px; color: #94a3b8; margin-top: 1px; }

.recap-base { background: #eff6ff; border: 1px solid #bfdbfe; }
.recap-base .recap-value { color: #1d4ed8; }
.recap-ind  { background: #f0fdf4; border: 1px solid #bbf7d0; }
.recap-ind .recap-value  { color: #15803d; }
.recap-ret  { background: #fff5f5; border: 1px solid #fecaca; }
.recap-ret .recap-value  { color: #dc2626; }
.recap-net  { background: #059669; }
.recap-net .recap-label  { color: #a7f3d0; }
.recap-net .recap-value  { color: white; font-size: 15px; }
.recap-net .recap-unit   { color: #6ee7b7; }

/* ── Séparateur section ── */
.section-title {
    font-size: 9px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 0.6px;
    padding: 5px 10px; margin-bottom: 8px;
    border-radius: 4px;
}
.section-green { background: #dcfce7; color: #15803d; }
.section-red   { background: #fee2e2; color: #dc2626; }

/* ── Net final ── */
.net-box {
    background: #059669;
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 18px;
    display: table;
    width: 100%;
}
.net-left  { display: table-cell; vertical-align: middle; }
.net-right { display: table-cell; vertical-align: middle; text-align: right; }
.net-label { font-size: 10px; font-weight: bold; color: #a7f3d0; margin-bottom: 4px; }
.net-amount{ font-size: 22px; font-weight: bold; color: white; }
.net-unit  { font-size: 10px; color: #6ee7b7; }
.net-info  { font-size: 10px; color: #a7f3d0; }
.net-date  { font-size: 11px; font-weight: bold; color: white; }

/* ── Statut badge ── */
.statut-badge {
    display: inline-block;
    font-size: 9px; font-weight: bold;
    padding: 3px 10px; border-radius: 20px;
}
.statut-paye     { background: #dcfce7; color: #15803d; }
.statut-valide   { background: #dbeafe; color: #1d4ed8; }
.statut-attente  { background: #fef9c3; color: #92400e; }

/* ── Note ── */
.note-box {
    background: #fef9c3;
    border: 1px solid #fde68a;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 10px;
    color: #92400e;
    margin-bottom: 14px;
}

/* ── Signatures ── */
.signatures {
    display: table;
    width: 100%;
    margin-top: 24px;
    border-top: 1px dashed #cbd5e1;
    padding-top: 16px;
}
.sig-cell { display: table-cell; width: 33%; text-align: center; padding: 0 8px; }
.sig-label { font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 28px; }
.sig-line  { border-top: 1px solid #cbd5e1; margin-top: 4px; }
.sig-name  { font-size: 9px; color: #94a3b8; margin-top: 4px; }

/* ── Footer ── */
.footer {
    margin-top: 20px;
    border-top: 1px solid #e2e8f0;
    padding-top: 10px;
    text-align: center;
    font-size: 8.5px;
    color: #94a3b8;
}
.footer strong { color: #64748b; }

/* Ligne de séparation */
.divider { border: none; border-top: 1px solid #e2e8f0; margin: 14px 0; }
</style>
</head>
<body>
<div class="page">

    {{-- ── En-tête ── --}}
    <div class="header">
        <div class="header-left">
            @if ($logoB64)
                <img src="{{ $logoB64 }}" class="logo" alt="Logo">
            @else
                <div class="logo-placeholder">ISM</div>
            @endif
        </div>
        <div class="header-right">
            <div class="bulletin-title">BULLETIN DE PAIE</div>
            <div class="bulletin-period">{{ $moisNom }} {{ $paiement->annee }}</div>
            <div style="margin-top:6px;">
                @php
                    $statut = $paiement->statut;
                    $cls = $statut === 'paye' ? 'statut-paye' : ($statut === 'valide' ? 'statut-valide' : 'statut-attente');
                    $lbl = $statut === 'paye' ? 'Payé' : ($statut === 'valide' ? 'Validé' : 'En attente');
                @endphp
                <span class="statut-badge {{ $cls }}">{{ $lbl }}</span>
            </div>
        </div>
    </div>

    {{-- ── Infos employé ── --}}
    <div class="emp-section">
        <div class="emp-grid">
            <div class="emp-col">
                <div class="emp-label">Employé</div>
                <div class="emp-value">{{ strtoupper($paiement->employe->name) }} {{ $paiement->employe->lastname }}</div>
                @if ($paiement->employe->matricule)
                    <div class="emp-sub">Matricule : {{ $paiement->employe->matricule }}</div>
                @endif
                @if ($paiement->employe->contact)
                    <div class="emp-sub">Tél : {{ $paiement->employe->contact }}</div>
                @endif
            </div>
            <div class="emp-col" style="text-align:right;">
                @if ($paiement->profil)
                    <div class="emp-label">Profil</div>
                    <div class="emp-value">{{ $paiement->profil->nom }}</div>
                @endif
                @if ($paiement->echelon)
                    <div class="emp-sub">Échelon {{ $paiement->echelon->numero }} — {{ $paiement->echelon->libelle }}</div>
                @endif
                <div class="emp-sub" style="margin-top:4px;">Période : {{ $moisNom }} {{ $paiement->annee }}</div>
            </div>
        </div>
    </div>

    {{-- ── Salaire de base ── --}}
    <table class="sal-table">
        <thead>
            <tr>
                <th style="width:60%;">Désignation</th>
                <th class="right" style="width:20%;">Base / Taux</th>
                <th class="right" style="width:20%;">Montant (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Salaire de base</strong></td>
                <td class="right">—</td>
                <td class="right amount-blue">{{ number_format($paiement->salaire_base, 0, ',', ' ') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ── Indemnités ── --}}
    @if (!empty($indemnites))
        <div class="section-title section-green">+ Indemnités</div>
        <table class="sal-table" style="margin-bottom:14px;">
            <thead>
                <tr>
                    <th style="width:50%;">Libellé</th>
                    <th class="right" style="width:25%;">Base / Taux</th>
                    <th class="right" style="width:25%;">Montant (FCFA)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($indemnites as $ind)
                    <tr>
                        <td>
                            {{ $ind['libelle'] }}
                            <span class="badge badge-green">{{ $ind['type'] === 'fixe' ? 'Fixe' : 'Pourcentage' }}</span>
                        </td>
                        <td class="right">{{ $ind['type'] === 'fixe' ? '—' : $ind['valeur'].'%' }}</td>
                        <td class="right amount-green">+{{ number_format($ind['montant'], 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
                <tr style="background:#f0fdf4;">
                    <td colspan="2" style="text-align:right; font-weight:bold; font-size:10px; color:#15803d;">
                        Total indemnités
                    </td>
                    <td class="right amount-green" style="font-weight:bold;">
                        +{{ number_format($paiement->total_indemnites, 0, ',', ' ') }}
                    </td>
                </tr>
            </tbody>
        </table>
    @endif

    {{-- ── Salaire brut ── --}}
    <table class="sal-table" style="margin-bottom:14px;">
        <tbody>
            <tr style="background:#f8fafc;">
                <td style="width:60%; font-weight:bold;">Salaire brut</td>
                <td class="right" style="width:20%;">—</td>
                <td class="right" style="width:20%; font-weight:bold; color:#1e293b;">
                    {{ number_format($paiement->salaire_base + $paiement->total_indemnites, 0, ',', ' ') }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- ── Retenues ── --}}
    @if (!empty($retenues))
        <div class="section-title section-red">− Retenues</div>
        <table class="sal-table" style="margin-bottom:14px;">
            <thead>
                <tr>
                    <th style="width:50%;">Libellé</th>
                    <th class="right" style="width:25%;">Base / Taux</th>
                    <th class="right" style="width:25%;">Montant (FCFA)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($retenues as $ret)
                    <tr>
                        <td>
                            {{ $ret['libelle'] }}
                            <span class="badge badge-red">{{ $ret['type'] === 'fixe' ? 'Fixe' : 'Pourcentage' }}</span>
                        </td>
                        <td class="right">{{ $ret['type'] === 'fixe' ? '—' : $ret['valeur'].'%' }}</td>
                        <td class="right amount-red">-{{ number_format($ret['montant'], 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
                <tr style="background:#fff5f5;">
                    <td colspan="2" style="text-align:right; font-weight:bold; font-size:10px; color:#dc2626;">
                        Total retenues
                    </td>
                    <td class="right amount-red" style="font-weight:bold;">
                        -{{ number_format($paiement->total_retenues, 0, ',', ' ') }}
                    </td>
                </tr>
            </tbody>
        </table>
    @endif

    {{-- ── Net à payer ── --}}
    <div class="net-box">
        <div class="net-left">
            <div class="net-label">NET À PAYER</div>
            <div class="net-amount">{{ number_format($paiement->salaire_net, 0, ',', ' ') }}</div>
            <div class="net-unit">Francs CFA (FCFA)</div>
        </div>
        <div class="net-right">
            @if ($paiement->paye_le)
                <div class="net-info">Payé le</div>
                <div class="net-date">{{ $paiement->paye_le->format('d/m/Y') }}</div>
                @if ($paiement->payePar)
                    <div class="net-info" style="margin-top:2px;">par {{ $paiement->payePar->name }}</div>
                @endif
            @elseif ($paiement->valide_le)
                <div class="net-info">Validé le</div>
                <div class="net-date">{{ $paiement->valide_le->format('d/m/Y') }}</div>
            @else
                <div class="net-info">En cours de traitement</div>
            @endif
        </div>
    </div>

    {{-- ── Récapitulatif ── --}}
    <table style="width:100%; border-collapse:separate; border-spacing:6px; margin-bottom:16px;">
        <tr>
            <td style="width:25%; background:#eff6ff; border:1px solid #bfdbfe; border-radius:6px; padding:8px 10px; text-align:center;">
                <div style="font-size:8px; color:#94a3b8; text-transform:uppercase; margin-bottom:3px;">Base</div>
                <div style="font-size:12px; font-weight:bold; color:#1d4ed8;">{{ number_format($paiement->salaire_base, 0, ',', ' ') }}</div>
                <div style="font-size:8px; color:#94a3b8;">FCFA</div>
            </td>
            <td style="width:25%; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; padding:8px 10px; text-align:center;">
                <div style="font-size:8px; color:#94a3b8; text-transform:uppercase; margin-bottom:3px;">+ Indemnités</div>
                <div style="font-size:12px; font-weight:bold; color:#15803d;">+{{ number_format($paiement->total_indemnites, 0, ',', ' ') }}</div>
                <div style="font-size:8px; color:#94a3b8;">FCFA</div>
            </td>
            <td style="width:25%; background:#fff5f5; border:1px solid #fecaca; border-radius:6px; padding:8px 10px; text-align:center;">
                <div style="font-size:8px; color:#94a3b8; text-transform:uppercase; margin-bottom:3px;">− Retenues</div>
                <div style="font-size:12px; font-weight:bold; color:#dc2626;">-{{ number_format($paiement->total_retenues, 0, ',', ' ') }}</div>
                <div style="font-size:8px; color:#94a3b8;">FCFA</div>
            </td>
            <td style="width:25%; background:#059669; border-radius:6px; padding:8px 10px; text-align:center;">
                <div style="font-size:8px; color:#a7f3d0; text-transform:uppercase; margin-bottom:3px;">Net</div>
                <div style="font-size:13px; font-weight:bold; color:white;">{{ number_format($paiement->salaire_net, 0, ',', ' ') }}</div>
                <div style="font-size:8px; color:#6ee7b7;">FCFA</div>
            </td>
        </tr>
    </table>

    {{-- ── Note ── --}}
    @if ($paiement->note)
        <div class="note-box">
            <strong>Note :</strong> {{ $paiement->note }}
        </div>
    @endif

    {{-- ── Signatures ── --}}
    <div class="signatures">
        <div class="sig-cell">
            <div class="sig-label">L'Employé</div>
            <div class="sig-line"></div>
            <div class="sig-name">{{ strtoupper($paiement->employe->name) }} {{ $paiement->employe->lastname }}</div>
        </div>
        <div class="sig-cell">
            <div class="sig-label">Validé par</div>
            <div class="sig-line"></div>
            <div class="sig-name">{{ $paiement->validePar?->name ?? '—' }}</div>
        </div>
        <div class="sig-cell">
            <div class="sig-label">Direction</div>
            <div class="sig-line"></div>
            <div class="sig-name">La Direction Générale</div>
        </div>
    </div>

    {{-- ── Footer ── --}}
    <div class="footer">
        <strong>Institut Supérieur La Majestueuse de Ndazoa</strong> —
        Ndazoa, 7 km de Mbankomo, route Yaoundé-Douala |
        Tél : +237 691 612 145 | info@ism-ndazoa.com
        <br>
        Document généré le {{ now()->format('d/m/Y à H:i') }} — Confidentiel
    </div>

</div>
</body>
</html>