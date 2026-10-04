<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1a1a1a; padding: 26px 34px; }
.header-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
.header-table td { vertical-align: middle; }
.logo { height: 62px; }
.school-fr { font-size: 13px; font-weight: bold; }
.school-en { font-size: 10px; color: #444; margin-top: 2px; }
.doc-title { font-size: 15px; font-weight: bold; margin-top: 8px; letter-spacing: .5px; }
.republic { font-size: 9px; font-weight: bold; text-align: center; }
.motto { font-size: 8px; color: #777; font-style: italic; text-align: center; margin-top: 2px; }
.sep { border: none; border-top: 2px solid #1a1a1a; margin: 8px 0; }
.info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
.info-table td { padding: 3px 6px; font-size: 10px; }
.info-label { font-weight: bold; width: 22%; }
.info-value { border-bottom: 1px solid #bbb; }
.session-title { background: #065f46; color: #fff; padding: 5px 10px; font-size: 11px; font-weight: bold; margin-top: 12px; }
.ue-title { background: #e8f5e9; color: #1b5e20; padding: 3px 8px; font-size: 9.5px; font-weight: bold; border: 0.5px solid #c8e6c9; }
.notes-table { width: 100%; border-collapse: collapse; }
.notes-table th { background: #1a1a1a; color: white; padding: 4px 6px; font-size: 8.5px; text-align: center; }
.notes-table th.l { text-align: left; }
.notes-table td { padding: 4px 6px; font-size: 9px; border-bottom: 0.5px solid #e5e5e5; text-align: center; }
.notes-table td.l { text-align: left; }
.notes-table tr:nth-child(even) td { background: #f9f9f9; }
.ok { color: #1a6b1a; font-weight: bold; }
.ko { color: #b71c1c; font-weight: bold; }
.recap { width: 100%; border-collapse: collapse; margin-top: 14px; border: 1px solid #1a1a1a; }
.recap td { padding: 6px 12px; font-size: 10.5px; }
.recap .k { background: #f0f0f0; font-weight: bold; width: 60%; }
.recap .v { text-align: right; font-weight: bold; }
.footer { margin-top: 20px; border-top: 1px solid #ccc; padding-top: 8px; text-align: center; font-size: 8px; color: #888; }
.sig { width: 100%; margin-top: 26px; border-collapse: collapse; }
.sig td { text-align: center; font-size: 9px; width: 50%; }
.sig-line { border-top: 1px solid #999; margin: 26px 20px 4px; }
</style>
</head>
<body>

<table class="header-table">
    <tr>
        <td width="20%" style="text-align:center;"><img src="{{ public_path(config('etablissement.logo')) }}" class="logo" alt="{{ config('etablissement.sigle') }}"></td>
        <td width="60%" style="text-align:center;">
            <div class="school-fr">{{ config('etablissement.sigle') }}</div>
            <div class="school-en">{{ config('etablissement.nom') }}</div>
            <div class="doc-title">RELEVÉ DE NOTES / TRANSCRIPT</div>
        </td>
        <td width="20%">
            <div class="republic">REPUBLIQUE DU CAMEROUN</div>
            <div class="motto">Paix — Travail — Patrie</div>
            <div class="republic" style="margin-top:5px;">REPUBLIC OF CAMEROON</div>
            <div class="motto">Peace — Work — Fatherland</div>
        </td>
    </tr>
</table>

<hr class="sep">

<table class="info-table">
    <tr>
        <td class="info-label">Nom &amp; Prénom :</td>
        <td class="info-value"><strong>{{ strtoupper($user->name ?? '') }} {{ $user->lastname ?? '' }}</strong></td>
        <td class="info-label">Matricule :</td>
        <td class="info-value">{{ $user->matricule ?? '—' }}</td>
    </tr>
    <tr>
        <td class="info-label">Filière :</td>
        <td class="info-value">{{ $filiere ?? '—' }}</td>
        <td class="info-label">Cycle :</td>
        <td class="info-value">{{ $user->cycle?->name ?? '—' }}</td>
    </tr>
</table>

@forelse ($sessions as $session)
    <div class="session-title">{{ $session['titre'] }} — {{ $session['valides'] }}/{{ $session['credits'] }} crédits validés</div>
    @foreach ($session['ues'] as $ue)
        <div class="ue-title">{{ $ue['code'] ?: 'UE' }} — {{ $ue['name'] }}</div>
        <table class="notes-table">
            <thead>
                <tr>
                    <th class="l" style="width:38%;">Matière</th>
                    <th style="width:9%;">CC</th>
                    <th style="width:9%;">Examen</th>
                    <th style="width:9%;">Ratt.</th>
                    <th style="width:11%;">Moy./20</th>
                    <th style="width:8%;">Cote</th>
                    <th style="width:8%;">Crédits</th>
                    <th style="width:8%;">Déc.</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ue['lignes'] as $l)
                    <tr>
                        <td class="l">{{ $l['nom'] }}</td>
                        <td>{{ $l['cc'] !== null ? number_format($l['cc'], 2) : '—' }}</td>
                        <td>{{ $l['exam'] !== null ? number_format($l['exam'], 2) : '—' }}</td>
                        <td>{{ $l['ratt'] !== null ? number_format($l['ratt'], 2) : '—' }}</td>
                        <td class="{{ $l['moy'] !== null ? ($l['moy'] >= 10 ? 'ok' : 'ko') : '' }}">{{ $l['moy'] !== null ? number_format($l['moy'], 2) : '—' }}</td>
                        <td>{{ $l['cote'] }}</td>
                        <td>{{ $l['credit'] }}</td>
                        <td class="{{ $l['moy'] !== null ? ($l['valide'] ? 'ok' : 'ko') : '' }}">{{ $l['moy'] === null ? '—' : ($l['valide'] ? 'V' : 'NV') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
@empty
    <p style="text-align:center; color:#888; padding:30px 0;">Aucune note disponible.</p>
@endforelse

<table class="recap">
    <tr><td class="k">Moyenne générale</td><td class="v">{{ $globalStats['moy'] !== null ? number_format($globalStats['moy'], 2) . ' / 20' : '—' }}</td></tr>
    <tr><td class="k">Crédits validés / requis</td><td class="v">{{ $globalStats['valides'] }} / {{ $globalStats['credits'] }}</td></tr>
    <tr><td class="k">Nombre de matières notées</td><td class="v">{{ $globalStats['matieres'] }}</td></tr>
</table>

<table class="sig">
    <tr>
        <td>L'Étudiant<div class="sig-line"></div></td>
        <td>La Scolarité<div class="sig-line"></div></td>
    </tr>
</table>

<div class="footer">
    {{ config('etablissement.sigle') }} — {{ config('etablissement.nom') }} — {{ config('etablissement.adresse') }} &nbsp;|&nbsp; Document généré le {{ now()->format('d/m/Y') }} — V = Validé, NV = Non validé
</div>

</body>
</html>
