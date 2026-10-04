<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>

@page {
    size: {{ $largeurMm }}mm 210mm;
    margin: 6mm 7mm 5mm 7mm;
}

thead    { display: table-header-group; }
tfoot    { display: table-footer-group; }
tbody tr { page-break-inside: avoid; page-break-after: auto; }
table    { page-break-inside: auto; }

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 7pt;
    color: #111827;
    background: #fff;
}

.header {
    background: #1e1b4b;
    padding: 6px 10px 5px 10px;
    margin-bottom: 4px;
    page-break-after: avoid;
    page-break-inside: avoid;
}
.header-inner { display: table; width: 100%; }
.header-l     { display: table-cell; vertical-align: middle; }
.header-r     { display: table-cell; vertical-align: middle; text-align: right; white-space: nowrap; padding-left: 10px; }
.header h1    { font-size: 10.5pt; font-weight: bold; color: #ffffff; letter-spacing: 0.3px; }
.header .sub  { font-size: 6.5pt; color: #a5b4fc; margin-top: 2px; }
.header .tag  { display: inline; background: #fbbf24; color: #78350f; font-size: 6.5pt; font-weight: bold; padding: 1px 5px; }
.header .gen  { font-size: 7pt; color: #fbbf24; font-weight: bold; }

/* Bandeau mode rattrapage */
.mode-banner {
    background: #fff7ed;
    border: 1px solid #fed7aa;
    color: #c2410c;
    padding: 3px 8px;
    margin-bottom: 4px;
    font-size: 7pt;
    font-weight: bold;
    page-break-inside: avoid;
    page-break-after: avoid;
}
.r-badge {
    color: #dc2626;
    font-weight: bold;
    font-size: 6pt;
    vertical-align: super;
    line-height: 0;
}

.stats {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 4px;
    page-break-inside: avoid;
    page-break-after: avoid;
}
.stats td {
    padding: 3px 5px;
    text-align: center;
    font-size: 6.5pt;
    border: 1px solid #c7d2fe;
    white-space: nowrap;
    vertical-align: middle;
}
.stats .lbl   { display: block; font-weight: normal; font-size: 5.5pt; color: inherit; opacity: 0.8; margin-bottom: 1px; }
.stats .val   { font-weight: bold; font-size: 8.5pt; }
.st-eff  { background: #eef2ff; color: #312e81; }
.st-adm  { background: #dcfce7; color: #166534; }
.st-adj  { background: #fee2e2; color: #991b1b; }
.st-taux { background: #ede9fe; color: #5b21b6; }
.st-avg  { background: #dbeafe; color: #1e40af; }
.st-max  { background: #fef9c3; color: #854d0e; }
.st-min  { background: #fff7ed; color: #9a3412; }
.st-leg  { background: #f8fafc; color: #475569; font-size: 5.5pt; text-align: left; padding: 3px 8px; }

table.main {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}
table.main th,
table.main td {
    border: 1px solid #c7d2fe;
    text-align: center;
    vertical-align: middle;
    padding: 2px 3px;
    word-wrap: break-word;
    overflow: hidden;
}

.col-nom { width: 44mm; }
.th-nom {
    background: #111827;
    color: #fff;
    font-size: 7pt;
    font-weight: bold;
    text-align: left;
    padding: 3px 6px;
    border-color: #374151;
}
.td-nom {
    text-align: left;
    padding: 2px 5px;
    background: #fff;
}
.row-odd .td-nom { background: #f5f3ff; }
.td-n  { font-weight: bold; font-size: 7pt; color: #111827; }
.td-m  { font-size: 5.5pt; color: #9ca3af; margin-top: 1px; }

.col-cr   { width: 9mm;  }
.col-cc   { width: 12mm; }
.col-ex   { width: 12mm; }
.col-moy  { width: 12mm; }
.col-mue  { width: 14mm; }
.col-mg   { width: 16mm; }
.col-rg   { width: 12mm; }

.th-ue { font-size: 7pt; font-weight: bold; color: #fff; padding: 3px 4px; border-color: #4338ca; }
.ue-0 { background: #1e1b4b; }
.ue-1 { background: #312e81; }
.ue-2 { background: #1e1b4b; }
.ue-3 { background: #312e81; }
.ue-4 { background: #1e1b4b; }

.th-cours { font-size: 6pt; color: #fff; padding: 2px 3px; border-color: #4338ca; }
.cs-0 { background: #3730a3; }
.cs-1 { background: #4338ca; }
.cs-2 { background: #3730a3; }
.cs-3 { background: #4338ca; }

.th-crc { font-size: 6pt; font-weight: bold; padding: 2px 3px; border-color: #4338ca; }
.cr-0 { background: #4338ca; color: #fde68a; }
.cr-1 { background: #3730a3; color: #fde68a; }

.th-lbl { font-size: 5.5pt; text-transform: uppercase; font-weight: bold; padding: 2px 2px; border-color: #4338ca; }
.sl-0  { background: #4338ca; color: #c7d2fe; }
.sl-1  { background: #3730a3; color: #c7d2fe; }
.slm-0 { background: #4338ca; color: #fde68a; }
.slm-1 { background: #3730a3; color: #fde68a; }

.th-mue { font-size: 6.5pt; font-weight: bold; color: #fde68a; padding: 2px 3px; border-width: 1px; border-style: solid; border-color: #818cf8; }
.mue-0 { background: #1e1b4b; }
.mue-1 { background: #312e81; }

.th-fin { background: #111827; color: #fff; font-size: 7pt; font-weight: bold; padding: 2px 3px; border-color: #374151; }

.row-even { background: #ffffff; }
.row-odd  { background: #f5f3ff; }

.td-cr  { font-size: 6.5pt; font-weight: bold; color: #4338ca; background: #eef2ff; }
.td-num { font-size: 7pt; color: #374151; }

/* Cellule rattrapée en mode après */
.td-ratt {
    font-size: 7pt;
    color: #dc2626;
    font-weight: bold;
    background: #fff1f2;
}

.n-tb   { background: #dcfce7; color: #166534; font-weight: bold; font-size: 7pt; }
.n-bien { background: #dbeafe; color: #1e40af; font-weight: bold; font-size: 7pt; }
.n-pass { background: #fef9c3; color: #92400e; font-weight: bold; font-size: 7pt; }
.n-fail { background: #fee2e2; color: #991b1b; font-weight: bold; font-size: 7pt; }
.n-null { color: #9ca3af; font-size: 6.5pt; }

.td-mue { font-weight: bold; font-size: 8pt; border-width: 2px; border-style: solid; border-color: #818cf8; }
.td-mg  { font-weight: bold; font-size: 9pt; border-width: 2px; border-style: solid; border-color: #4338ca; }
.td-rg  { font-size: 7pt; font-weight: bold; color: #374151; }

</style>
</head>
<body>

{{-- ══ HEADER ══ --}}
<div class="header">
    <div class="header-inner">
        <div class="header-l">
            <h1>BARBILLARD &nbsp;·&nbsp; RELEVÉ DE NOTES</h1>
            <div class="sub">
                <span class="tag">{{ strtoupper($specialite?->name ?? '—') }}</span>
                &nbsp;&nbsp;
                <span class="tag">{{ strtoupper($examen?->titre ?? '—') }}</span>
                &nbsp;&nbsp;
                @if (($modeApres ?? false))
                    <span style="background:#dc2626;color:#fff;font-size:6.5pt;font-weight:bold;padding:1px 5px;">APRÈS RATTRAPAGE</span>
                @else
                    Formule : Moy = 0.3 × CC &nbsp;+&nbsp; 0.7 × Exam &nbsp;·&nbsp; Pondération par crédits UE
                @endif
            </div>
        </div>
        <div class="header-r">
            <div class="gen">Généré le {{ now()->format('d/m/Y à H:i') }}</div>
        </div>
    </div>
</div>

{{-- ══ BANDEAU MODE RATTRAPAGE ══ --}}
@if ($modeApres ?? false)
<div class="mode-banner">
    ⚠ Mode APRÈS RATTRAPAGE actif —
    <span class="r-badge">R</span> = note de rattrapage appliquée sur l'Exam uniquement (CC inchangé)
    &nbsp;|&nbsp; Si pas de rattrapage : valeur Exam normale conservée
</div>
@endif

{{-- ══ STATISTIQUES ══ --}}
@if (!empty($stats))
<table class="stats">
    <tr>
        <td class="st-eff">
            <span class="lbl">Effectif total</span>
            <span class="val">{{ $stats['total'] ?? 0 }}</span>
        </td>
        <td class="st-adm">
            <span class="lbl">Admis (≥ 10)</span>
            <span class="val">{{ $stats['admis'] ?? 0 }}</span>
        </td>
        <td class="st-adj">
            <span class="lbl">Ajournés (&lt; 10)</span>
            <span class="val">{{ $stats['ajournes'] ?? 0 }}</span>
        </td>
        <td class="st-taux">
            <span class="lbl">Taux de réussite</span>
            <span class="val">{{ $stats['taux'] ?? 0 }} %</span>
        </td>
        <td class="st-avg">
            <span class="lbl">Moyenne promotion</span>
            <span class="val">{{ $stats['avg'] ?? '—' }} / 20</span>
        </td>
        <td class="st-max">
            <span class="lbl">Meilleure note</span>
            <span class="val">{{ $stats['max'] ?? '—' }} / 20</span>
        </td>
        <td class="st-min">
            <span class="lbl">Note la plus basse</span>
            <span class="val">{{ $stats['min'] ?? '—' }} / 20</span>
        </td>
        <td class="st-leg">
            <strong>Légende :</strong><br>
            <span style="color:#166534;">■ ≥ 16 Très Bien</span> &nbsp;
            <span style="color:#1e40af;">■ ≥ 12 Bien / AB</span> &nbsp;
            <span style="color:#92400e;">■ ≥ 10 Passable</span> &nbsp;
            <span style="color:#991b1b;">■ &lt; 10 Insuffisant</span>
            @if ($modeApres ?? false)
                &nbsp; <span style="color:#dc2626;font-weight:bold;">■ <sup>R</sup> Rattrapé</span>
            @endif
        </td>
    </tr>
</table>
@endif

{{-- ══ TABLEAU ══ --}}
<table class="main">
    <thead>

        <tr>
            <th rowspan="4" class="th-nom col-nom">
                Nom &amp; Prénom
                <br><span style="font-weight:normal;font-size:5.5pt;color:#9ca3af;">Matricule</span>
            </th>

            @foreach ($ues as $uei => $ue)
                @php $ui = $uei % 5; @endphp
                <th colspan="{{ $ue->cours->count() * 4 + 1 }}" class="th-ue ue-{{ $ui }}">
                    {{ $ue->code }}@if($ue->name) — {{ $ue->name }}@endif
                    &nbsp;<span style="color:#fbbf24;font-size:6.5pt;">[ ★ {{ $ue->credits ?? '?' }} cr. ]</span>
                </th>
            @endforeach

            <th rowspan="4" class="th-fin col-mg">Moy.<br>Générale</th>
            <th rowspan="4" class="th-fin col-rg">Rang</th>
        </tr>

        <tr>
            @foreach ($ues as $uei => $ue)
                @php $ci = $uei % 4; @endphp
                @foreach ($ue->cours as $cours)
                    <th colspan="4" class="th-cours cs-{{ $ci }}">{{ $cours->name }}</th>
                @endforeach
                <th rowspan="3" class="th-mue mue-{{ $uei % 2 }}">Moy<br>UE</th>
            @endforeach
        </tr>

        <tr>
            @foreach ($ues as $uei => $ue)
                @php $ri = $uei % 2; @endphp
                @foreach ($ue->cours as $cours)
                    <th colspan="4" class="th-crc cr-{{ $ri }}">{{ $cours->credit ?? '?' }} cr.</th>
                @endforeach
            @endforeach
        </tr>

        <tr>
            @foreach ($ues as $uei => $ue)
                @php $li = $uei % 2; $lmi = $uei % 2; @endphp
                @foreach ($ue->cours as $cours)
                    <th class="th-lbl col-cr  slm-{{ $lmi }}">Cr.</th>
                    <th class="th-lbl col-cc  sl-{{ $li  }}">CC</th>
                    <th class="th-lbl col-ex  sl-{{ $li  }}">Exam</th>
                    <th class="th-lbl col-moy slm-{{ $lmi }}">Moy</th>
                @endforeach
            @endforeach
        </tr>

    </thead>

    <tbody>
        @foreach ($etudiants as $i => $etudiant)
            @php
                $mg    = $moyGen[$etudiant->id] ?? null;
                $rowBg = $i % 2 === 0 ? 'row-even' : 'row-odd';

                $nc = function($n) {
                    if ($n === null) return 'n-null';
                    if ($n >= 16)   return 'n-tb';
                    if ($n >= 12)   return 'n-bien';
                    if ($n >= 10)   return 'n-pass';
                    return 'n-fail';
                };
            @endphp

            <tr class="{{ $rowBg }}">

                {{-- Nom --}}
                <td class="td-nom col-nom">
                    <div class="td-n">{{ strtoupper($etudiant->name) }}, {{ $etudiant->lastname }}</div>
                    @if ($etudiant->matricule)
                        <div class="td-m">{{ $etudiant->matricule }}</div>
                    @endif
                </td>

                {{-- Notes --}}
                @foreach ($ues as $ue)
                    @foreach ($ue->cours as $cours)
                        @php
                            $n         = $notes[$etudiant->id][$cours->id]
                                         ?? ['cc' => null, 'exam' => null, 'moy' => null, 'mode_apres' => false];
                            $moy       = $n['moy'];
                            $estRatt   = ($modeApres ?? false) && ($n['mode_apres'] ?? false);
                        @endphp

                        {{-- Crédit --}}
                        <td class="td-cr col-cr">{{ $cours->credit ?? '?' }}</td>

                        {{-- CC — jamais modifié --}}
                        <td class="td-num col-cc">
                            {{ $n['cc'] !== null ? number_format($n['cc'], 2) : '—' }}
                        </td>

                        {{-- Exam --}}
                        <td class="{{ $estRatt ? 'td-ratt' : 'td-num' }} col-ex">
                            @if ($n['exam'] !== null)
                                {{ number_format($n['exam'], 2) }}@if($estRatt)<span class="r-badge">R</span>@endif
                            @else
                                —
                            @endif
                        </td>

                        {{-- Moy cours --}}
                        <td class="{{ $nc($moy) }} col-moy">
                            @if ($moy !== null)
                                {{ number_format($moy, 2) }}@if($estRatt)<span class="r-badge">R</span>@endif
                            @else
                                —
                            @endif
                        </td>

                    @endforeach

                    @php $mUE = $moyUE[$etudiant->id][$ue->id] ?? null; @endphp
                    <td class="td-mue col-mue {{ $nc($mUE) }}">
                        {{ $mUE !== null ? number_format($mUE, 2) : '—' }}
                    </td>
                @endforeach

                {{-- Moy Générale --}}
                <td class="td-mg col-mg {{ $nc($mg) }}">
                    {{ $mg !== null ? number_format($mg, 2) : '—' }}
                </td>

                {{-- Rang --}}
                <td class="td-rg col-rg">{{ $rangs[$etudiant->id] ?? '—' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>