@php
    use Illuminate\Support\Str;

    $utilisateur = auth()->user();
    $avecNavigation = $utilisateur && ! in_array($header ?? true, [false, 'false'], true);

    // Espace principal (sous-titre, accueil) et espaces dont le menu est assemblé :
    // un employé qui cumule plusieurs rôles voit les liens de chacun.
    $espace = $utilisateur?->espace();
    $espacesMenu = $utilisateur ? $utilisateur->espacesMenu() : [];

    $prefixes = $utilisateur ? $utilisateur->allowedPrefixes() : [];
    $autorise = function (string $lien) use ($prefixes): bool {
        $chemin = ltrim((string) parse_url($lien, PHP_URL_PATH), '/');
        foreach ($prefixes as $prefixe) {
            if ($prefixe === '*' || Str::is($prefixe, $chemin)) {
                return true;
            }
        }
        return false;
    };
    $libelle = fn (string $titre) => str_starts_with($titre, 'libelles.') ? config('etablissement.'.$titre, $titre) : $titre;

    // Construit le menu de l'espace : liens autorises, groupes non vides, lien actif.
    $cheminCourant = '/'.trim(request()->path(), '/');
    $menu = [];
    $actif = null;
    $meilleur = -1;
    $dejaVus = [];
    foreach ($espacesMenu as $espaceMenu) {
    foreach (config('navigation.'.$espaceMenu, []) as $entree) {
        if (isset($entree['module']) && ! config('etablissement.modules.'.$entree['module'])) {
            continue;
        }
        $liens = isset($entree['liens']) ? $entree['liens'] : [$entree];
        $gardes = [];
        foreach ($liens as $lien) {
            // Un lien externe (le portail) sort de l'application : les prefixes
            // locaux ne le concernent pas.
            $externe = ! empty($lien['externe']);
            if ((! $externe && ! $autorise($lien['lien'])) || isset($dejaVus[$lien['lien']])) {
                continue;
            }
            $dejaVus[$lien['lien']] = true;
            $lien['titre'] = $libelle($lien['titre']);
            $chemin = '/'.trim((string) parse_url($lien['lien'], PHP_URL_PATH), '/');
            parse_str((string) parse_url($lien['lien'], PHP_URL_QUERY), $requete);
            $correspond = ($cheminCourant === $chemin || ($chemin !== '/'.$espace && $chemin !== '/admin' && $chemin !== '/dashboard' && $chemin !== '/personnel' && $chemin !== '/finance' && $chemin !== '/specialite' && str_starts_with($cheminCourant.'/', $chemin.'/')))
                && collect($requete)->every(fn ($v, $k) => request()->query($k, $k === 'type' ? 'inscription' : null) === $v);
            $score = $correspond && ! $externe ? strlen($chemin) + count($requete) : -1;
            $lien['groupe'] = $entree['groupe'] ?? null;
            if ($score > $meilleur) {
                $meilleur = $score;
                $actif = $lien;
            }
            $gardes[] = $lien;
        }
        if ($gardes) {
            $menu[] = isset($entree['liens']) ? ['groupe' => $entree['groupe'], 'liens' => $gardes] : $gardes[0];
        }
    }
    }

    $icones = [
        'accueil' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        'eleves' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        'ajouter' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
        'reinscrire' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
        'importer' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12',
        'classe' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        'niveaux' => 'M4 6h16M4 10h16M4 14h16M4 18h16',
        'sections' => 'M3 5a2 2 0 012-2h4a2 2 0 012 2v4a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm10 0a2 2 0 012-2h4a2 2 0 012 2v4a2 2 0 01-2 2h-4a2 2 0 01-2-2V5zM3 15a2 2 0 012-2h4a2 2 0 012 2v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4zm10 0a2 2 0 012-2h4a2 2 0 012 2v4a2 2 0 01-2 2h-4a2 2 0 01-2-2v-4z',
        'cycle' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
        'domaines' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
        'matieres' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        'evaluation' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
        'notes' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        'barbillard' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'salle' => 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z',
        'personnel' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'argent' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'document' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'profil' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'etiquette' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
        'plus' => 'M12 6v6m0 0v6m0-6h6m-6 0H6',
        'moins' => 'M20 12H4',
        'cantine' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
        'livre' => 'M8 14v3m4-3v3m4-3v3M3 21h18M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16',
        'support' => 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z',
        'annonce' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
        'cloche' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
    ];
    $icone = fn (string $nom, string $classe = 'w-5 h-5') => '<svg class="'.$classe.' shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="'.($icones[$nom] ?? $icones['document']).'"/></svg>';

    $titrePage = $title ?? ($actif['titre'] ?? null);
    $accueil = $utilisateur?->homePath() ?? '/';
    $initiales = $utilisateur ? Str::upper(Str::substr($utilisateur->name ?? '', 0, 1).Str::substr($utilisateur->lastname ?? '', 0, 1)) : '';
    $roles = ['admin' => 'Administrateur', 'enseignant' => 'Enseignant', 'student' => 'Étudiant', 'personnel' => 'Personnel', 'concierge' => 'Concierge', 'bibliothecaire' => 'Bibliothécaire', 'coordonnateur' => 'Coordonnateur'];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titrePage ? $titrePage.' · ' : '' }}{{ config('etablissement.sigle') }}</title>
    <link rel="icon" href="{{ asset(config('etablissement.logo')) }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <style>
        [x-cloak] { display: none !important; }
        #nav-laterale { background: linear-gradient(180deg, #14265c 0%, #1e3a8a 55%, #1d4ed8 100%); color: #fff; }
        #nav-laterale .nav-titre { color: #fff; }
        #nav-laterale .nav-sous-titre { color: #fdba74; }
        #nav-filtre { background: rgba(255,255,255,.12); color: #fff; }
        #nav-filtre::placeholder { color: #bfdbfe; }

        /* ---------- Tableaux : un seul style, celui de la liste des inscriptions ---------- */
        #gabarit-contenu table { width: 100%; border-collapse: collapse !important; border-spacing: 0 !important; font-size: .875rem; }
        #gabarit-contenu table thead tr { background: #f8fafc !important; }
        #gabarit-contenu table th { padding: .75rem !important; text-align: left; font-size: .8125rem !important; font-weight: 600 !important;
            color: #475569 !important; text-transform: none !important; letter-spacing: normal !important; background: #f8fafc !important;
            border-bottom: 1px solid #e2e8f0; border-radius: 0 !important; white-space: nowrap; }
        #gabarit-contenu table td { padding: .75rem !important; color: #1f2937; vertical-align: middle; background: transparent !important;
            border-bottom: 1px solid #f1f5f9; border-radius: 0 !important; }
        #gabarit-contenu table tbody tr { background: #fff !important; box-shadow: none !important; border-radius: 0 !important; transform: none !important; }
        #gabarit-contenu table tbody tr:hover { background: #f8fafc !important; }
        #gabarit-contenu table tbody tr:last-child td { border-bottom: 0; }
        #gabarit-contenu table[data-style-compact] th, #gabarit-contenu table[data-style-compact] td { padding: .45rem .5rem !important; }
        #gabarit-contenu .overflow-x-auto:has(> table) { border: 1px solid #eef2f7; border-radius: .75rem; background: #fff; }

        /* Boutons d'action en icônes */
        .bouton-icone { display: inline-flex; align-items: center; justify-content: center; width: 2rem; height: 2rem;
            border-radius: .5rem; transition: background-color .15s; vertical-align: middle; }
        .bouton-icone:focus-visible { outline: 2px solid #2563eb; outline-offset: 1px; }

        /* Barre d'export au-dessus de chaque tableau */
        [data-barre-export] { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: .4rem; margin: 0 0 .5rem; font-size: .75rem; }
        [data-barre-export] button { display: inline-flex; align-items: center; gap: .3rem; border: 1px solid #e2e8f0; background: #fff; color: #334155;
            border-radius: .5rem; padding: .3rem .6rem; font-weight: 500; }
        [data-barre-export] button:hover { background: #f8fafc; border-color: #cbd5e1; }
        .nav-lien { display:flex; align-items:center; gap:.75rem; padding:.5rem .75rem; border-radius:.5rem; font-size:.875rem; color:#dbeafe; }
        .nav-lien:hover { background:rgba(255,255,255,.08); color:#fff; }
        .nav-lien.actif { background:#fff; color:#1e3a8a; font-weight:600; box-shadow: inset 3px 0 0 #f2622a; }
        .nav-groupe { padding:1rem .75rem .35rem; font-size:.68rem; letter-spacing:.08em; text-transform:uppercase; color:#bfdbfe; font-weight:600; }
        #gabarit-contenu > div > .max-w-7xl, #gabarit-contenu > div > .max-w-5xl { padding-top: 1.5rem; }
    </style>
    @livewireStyles
</head>

<body class="bg-slate-50 text-gray-800">

@if ($avecNavigation)
    {{-- Fond du menu mobile --}}
    <div id="nav-fond" class="fixed inset-0 z-30 bg-black/40 hidden lg:hidden" onclick="basculerMenu(false)"></div>

    {{-- ==================== Menu latéral ==================== --}}
    <aside id="nav-laterale" class="fixed inset-y-0 left-0 z-40 w-64 text-white flex flex-col -translate-x-full lg:translate-x-0 transition-transform duration-200">
        <a href="{{ $accueil }}" class="flex items-center gap-3 px-4 py-4 border-b border-white/10">
            <span class="bg-white rounded-full p-1 shrink-0"><img src="{{ asset(config('etablissement.logo')) }}" alt="" class="h-10 w-10 object-contain"></span>
            <span class="leading-tight">
                <span class="block font-bold nav-titre">{{ config('etablissement.sigle') }}</span>
                <span class="block text-xs nav-sous-titre">{{ ['admin' => 'Administration', 'finance' => 'Finance & RH', 'eleve' => 'Espace étudiant', 'personnel' => 'Espace personnel'][$espace] ?? '' }}</span>
            </span>
        </a>

        <div class="px-3 pt-3">
            <input id="nav-filtre" type="search" placeholder="Rechercher un menu…" autocomplete="off"
                class="w-full rounded-lg border-0 bg-white/10 text-sm text-white placeholder-blue-200 focus:ring-2 focus:ring-white/40 px-3 py-2">
        </div>

        <nav class="flex-1 overflow-y-auto px-3 pb-6" aria-label="Menu principal">
            @foreach ($menu as $entree)
                @if (isset($entree['groupe']))
                    <div class="nav-bloc">
                        <p class="nav-groupe">{{ $entree['groupe'] }}</p>
                        @foreach ($entree['liens'] as $lien)
                            <a href="{{ $lien['lien'] }}" class="nav-lien {{ $actif && $actif['lien'] === $lien['lien'] ? 'actif' : '' }}" @if($actif && $actif['lien'] === $lien['lien']) aria-current="page" @endif @if(! empty($lien['externe'])) target="_blank" rel="noopener" @endif>
                                {!! $icone($lien['icone']) !!}<span>{{ $lien['titre'] }}</span>
                                @if (! empty($lien['externe']))
                                    <svg class="ml-auto h-3.5 w-3.5 opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/></svg>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @else
                    <a href="{{ $entree['lien'] }}" class="nav-lien mt-2 {{ $actif && $actif['lien'] === $entree['lien'] ? 'actif' : '' }}">
                        {!! $icone($entree['icone']) !!}<span>{{ $entree['titre'] }}</span>
                    </a>
                @endif
            @endforeach
        </nav>
        {{-- Le menu garde sa position d'une page à l'autre, et le lien actif reste visible. --}}
        <script>
            (function () {
                const menu = document.currentScript.previousElementSibling;
                const cle = 'defilement-menu-lateral';
                let enregistree = null;
                try { enregistree = sessionStorage.getItem(cle); } catch (e) {}
                // La hauteur du menu n'est definitive qu'une fois la page chargee : on replace aussi a ce moment.
                const replacer = () => {
                    if (enregistree !== null) menu.scrollTop = Number(enregistree);
                    const actif = menu.querySelector('.nav-lien.actif');
                    if (!actif) return;
                    const zone = menu.getBoundingClientRect(), lien = actif.getBoundingClientRect();
                    if (lien.top < zone.top) menu.scrollTop -= zone.top - lien.top + 8;
                    else if (lien.bottom > zone.bottom) menu.scrollTop += lien.bottom - zone.bottom + 8;
                };
                replacer();
                document.addEventListener('DOMContentLoaded', replacer);
                window.addEventListener('load', () => { replacer(); enregistree = String(menu.scrollTop); });
                const memoriser = () => { try { sessionStorage.setItem(cle, String(menu.scrollTop)); } catch (e) {} };
                menu.addEventListener('scroll', memoriser, { passive: true });
                menu.addEventListener('click', memoriser);
            })();
        </script>


        @if (config('portail.url'))
            <a href="{{ config('portail.url') }}" class="flex items-center gap-2 px-5 py-3 text-sm text-blue-100 hover:text-white border-t border-white/10">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Retour au portail
            </a>
        @endif
    </aside>

    <div class="lg:pl-64 min-h-screen flex flex-col">
        {{-- ==================== Barre du haut ==================== --}}
        <header class="sticky top-0 z-20 bg-white/95 backdrop-blur border-b border-gray-200">
            <div class="flex items-center gap-3 px-4 sm:px-6 h-16">
                <button type="button" class="lg:hidden p-2 -ml-2 rounded-lg hover:bg-gray-100" onclick="basculerMenu(true)" aria-label="Ouvrir le menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <nav class="min-w-0 flex-1 text-sm" aria-label="Fil d'Ariane">
                    <ol class="flex items-center gap-2 text-gray-500 truncate">
                        <li><a href="{{ $accueil }}" class="hover:text-blue-800">Accueil</a></li>
                        @if ($actif && $actif['groupe'])
                            <li class="text-gray-300">/</li><li class="hidden sm:block">{{ $actif['groupe'] }}</li>
                        @endif
                        @if ($titrePage && (! $actif || $actif['lien'] !== $accueil))
                            <li class="text-gray-300">/</li><li class="font-semibold text-gray-800 truncate">{{ $titrePage }}</li>
                        @endif
                    </ol>
                </nav>

                {{-- Export des tableaux de la page --}}
                <div class="relative" id="export-zone" hidden>
                    <button type="button" id="export-bouton" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Exporter</span><span id="export-compte" class="rounded-full bg-blue-100 text-blue-800 text-xs px-1.5"></span>
                    </button>
                    <div id="export-menu" class="hidden absolute right-0 mt-2 w-80 max-h-[70vh] overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl p-2 text-sm"></div>
                </div>

                <div class="relative">
                    <button type="button" id="profil-bouton" class="flex items-center gap-2 rounded-full hover:bg-gray-100 p-1 pr-2">
                        <span class="w-9 h-9 rounded-full bg-blue-800 text-white flex items-center justify-center text-sm font-semibold">{{ $initiales ?: '?' }}</span>
                        <span class="hidden md:block text-left leading-tight">
                            <span class="block text-sm font-medium text-gray-800">{{ trim(($utilisateur->name ?? '').' '.($utilisateur->lastname ?? '')) }}</span>
                            <span class="block text-xs text-gray-500">{{ $utilisateur->libellesRoles() }}</span>
                        </span>
                    </button>
                    <div id="profil-menu" class="hidden absolute right-0 mt-2 w-56 rounded-xl border border-gray-200 bg-white shadow-xl py-2 text-sm">
                        <p class="px-4 pb-2 text-xs text-gray-500 truncate border-b">{{ $utilisateur->email }}</p>
                        <a href="{{ $accueil }}" class="block px-4 py-2 hover:bg-gray-50">Mon espace</a>
                        @if (config('portail.url'))<a href="{{ config('portail.url') }}" class="block px-4 py-2 hover:bg-gray-50">Retour au portail</a>@endif
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50">Se déconnecter</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main id="gabarit-contenu" class="flex-1">
            {{ $slot }}
        </main>
    </div>
@else
    <main>{{ $slot }}</main>
@endif

    @livewireScripts
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

@if ($avecNavigation)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" defer></script>
    <script>
    /* ---------- Dégradés Tailwind ----------
     * La feuille Flowbite 3 (Tailwind 4) déclare --tw-gradient-from comme une couleur :
     * les classes from-* / via-* / to-* du Tailwind 3 ne s'appliquent plus et les cartes
     * en dégradé deviennent transparentes. On recompose le dégradé à partir des classes.
     */
    const PALETTE = {
        slate: { 50: '#f8fafc', 100: '#f1f5f9', 500: '#64748b', 600: '#475569', 700: '#334155', 800: '#1e293b', 900: '#0f172a' },
        gray: { 50: '#f9fafb', 100: '#f3f4f6', 500: '#6b7280', 600: '#4b5563', 700: '#374151', 800: '#1f2937', 900: '#111827' },
        red: { 50: '#fef2f2', 100: '#fee2e2', 400: '#f87171', 500: '#ef4444', 600: '#dc2626', 700: '#b91c1c' },
        orange: { 50: '#fff7ed', 100: '#ffedd5', 400: '#fb923c', 500: '#f97316', 600: '#ea580c', 700: '#c2410c' },
        amber: { 50: '#fffbeb', 100: '#fef3c7', 400: '#fbbf24', 500: '#f59e0b', 600: '#d97706', 700: '#b45309' },
        yellow: { 50: '#fefce8', 100: '#fef9c3', 400: '#facc15', 500: '#eab308', 600: '#ca8a04', 700: '#a16207' },
        green: { 50: '#f0fdf4', 100: '#dcfce7', 400: '#4ade80', 500: '#22c55e', 600: '#16a34a', 700: '#15803d' },
        emerald: { 50: '#ecfdf5', 100: '#d1fae5', 400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b' },
        teal: { 50: '#f0fdfa', 100: '#ccfbf1', 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e' },
        cyan: { 50: '#ecfeff', 100: '#cffafe', 500: '#06b6d4', 600: '#0891b2', 700: '#0e7490' },
        sky: { 50: '#f0f9ff', 100: '#e0f2fe', 500: '#0ea5e9', 600: '#0284c7', 700: '#0369a1' },
        blue: { 50: '#eff6ff', 100: '#dbeafe', 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a', 950: '#172554' },
        indigo: { 50: '#eef2ff', 100: '#e0e7ff', 400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca', 800: '#3730a3' },
        violet: { 50: '#f5f3ff', 100: '#ede9fe', 500: '#8b5cf6', 600: '#7c3aed', 700: '#6d28d9' },
        purple: { 50: '#faf5ff', 100: '#f3e8ff', 400: '#c084fc', 500: '#a855f7', 600: '#9333ea', 700: '#7e22ce' },
        pink: { 50: '#fdf2f8', 100: '#fce7f3', 500: '#ec4899', 600: '#db2777', 700: '#be185d' },
        rose: { 50: '#fff1f2', 100: '#ffe4e6', 500: '#f43f5e', 600: '#e11d48', 700: '#be123c' },
        white: { '': '#ffffff' }, black: { '': '#000000' },
    };
    const DIRECTIONS = { t: 'to top', tr: 'to top right', r: 'to right', br: 'to bottom right', b: 'to bottom', bl: 'to bottom left', l: 'to left', tl: 'to top left' };
    function couleurTailwind(nom) {
        const m = nom.match(/^([a-z]+)(?:-(\d{2,3}))?(?:\/(\d{1,3}))?$/); if (!m) return null;
        const hex = PALETTE[m[1]]?.[m[2] ?? '']; if (!hex) return null;
        if (!m[3]) return hex;
        const n = parseInt(hex.slice(1), 16);
        return `rgba(${n >> 16}, ${(n >> 8) & 255}, ${n & 255}, ${Number(m[3]) / 100})`;
    }
    function retablirDegrades(racine = document) {
        racine.querySelectorAll('[class*="bg-gradient-to-"]').forEach(el => {
            const classes = [...el.classList];
            const dir = classes.map(c => c.match(/^bg-gradient-to-(t|tr|r|br|b|bl|l|tl)$/)?.[1]).find(Boolean);
            const etape = p => { const c = classes.find(x => x.startsWith(p + '-')); return c ? couleurTailwind(c.slice(p.length + 1)) : null; };
            const de = etape('from'), via = etape('via'), a = etape('to');
            if (!dir || !de) return;
            const stops = [de, via, a ?? 'transparent'].filter(Boolean).join(', ');
            const valeur = `linear-gradient(${DIRECTIONS[dir]}, ${stops})`;
            if (el.style.backgroundImage !== valeur) el.style.backgroundImage = valeur;
        });
    }
    document.addEventListener('DOMContentLoaded', () => retablirDegrades());
    new MutationObserver(() => retablirDegrades()).observe(document.body, { childList: true, subtree: true });

    /* ---------- Navigation ---------- */
    function basculerMenu(ouvrir) {
        document.getElementById('nav-laterale').classList.toggle('-translate-x-full', !ouvrir);
        document.getElementById('nav-fond').classList.toggle('hidden', !ouvrir);
    }

    document.getElementById('nav-filtre')?.addEventListener('input', function () {
        const terme = this.value.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
        const simplifier = t => t.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
        document.querySelectorAll('#nav-laterale .nav-lien').forEach(function (lien) {
            // Un lien reste visible si son titre, ou le nom de son groupe, contient le terme.
            const groupe = lien.closest('.nav-bloc')?.querySelector('.nav-groupe')?.textContent || '';
            lien.hidden = terme !== '' && !simplifier(lien.textContent + ' ' + groupe).includes(terme);
        });
        document.querySelectorAll('#nav-laterale .nav-bloc').forEach(function (bloc) {
            bloc.hidden = [...bloc.querySelectorAll('.nav-lien')].every(l => l.hidden);
        });
    });

    function menuDeroulant(boutonId, menuId) {
        const bouton = document.getElementById(boutonId), menu = document.getElementById(menuId);
        if (!bouton || !menu) return;
        bouton.addEventListener('click', e => { e.stopPropagation(); menu.classList.toggle('hidden'); });
        document.addEventListener('click', e => { if (!menu.contains(e.target)) menu.classList.add('hidden'); });
    }
    menuDeroulant('profil-bouton', 'profil-menu');
    menuDeroulant('export-bouton', 'export-menu');

    /* ---------- Export des tableaux ----------
     * Chaque tableau visible de la page peut être exporté en Excel, CSV ou PDF.
     * La colonne « Actions » et les boutons sont ignorés. Un tableau portant
     * data-export-url est exporté par le serveur (toutes les pages, pas seulement
     * celle affichée).
     */
    const ETABLISSEMENT = @json(['sigle' => config('etablissement.sigle'), 'nom' => config('etablissement.nom'), 'logo' => asset(config('etablissement.logo'))]);

    function nettoyer(texte) { return (texte || '').replace(/\s+/g, ' ').trim(); }

    function titreDuTableau(table) {
        if (table.dataset.exportTitre) return table.dataset.exportTitre;
        const titres = [...document.querySelectorAll('#gabarit-contenu h1, #gabarit-contenu h2, #gabarit-contenu h3')]
            .filter(h => h.offsetParent !== null && (h.compareDocumentPosition(table) & Node.DOCUMENT_POSITION_FOLLOWING));
        const proche = titres.pop();
        return nettoyer(proche ? proche.textContent : document.title.split('·')[0]) || 'Tableau';
    }

    function lireTableau(table) {
        const lignesEntete = [...table.querySelectorAll('thead tr')];
        let entetes = lignesEntete.length ? [...lignesEntete[lignesEntete.length - 1].children].map(c => nettoyer(c.innerText)) : [];
        const corps = [...table.querySelectorAll('tbody tr')].filter(tr => tr.offsetParent !== null);
        if (!entetes.length && corps.length) { entetes = [...corps.shift().children].map(c => nettoyer(c.innerText)); }

        const ignorer = new Set();
        entetes.forEach((e, i) => { if (/^actions?$/i.test(e) || e === '') ignorer.add(i); });

        const lignes = corps
            .filter(tr => !(tr.children.length === 1 && tr.children[0].colSpan > 1))
            .map(tr => [...tr.children].map((cellule, i) => {
                const copie = cellule.cloneNode(true);
                copie.querySelectorAll('svg, script, style, .sr-only').forEach(n => n.remove());
                // Un statut affiché comme bouton (« Actif ») garde son texte ; hors
                // colonne titrée, les boutons ne sont que des actions.
                copie.querySelectorAll('button').forEach(bouton => {
                    if (entetes[i]) bouton.replaceWith(document.createTextNode(' ' + nettoyer(bouton.innerText) + ' '));
                    else bouton.remove();
                });
                copie.querySelectorAll('select').forEach(s => s.replaceWith(document.createTextNode(s.selectedOptions[0]?.text || '')));
                copie.querySelectorAll('input').forEach(i => i.replaceWith(document.createTextNode(i.type === 'checkbox' ? (i.checked ? 'Oui' : 'Non') : i.value)));
                return nettoyer(copie.innerText || copie.textContent);
            }));

        // Une colonne sans en-tête mais avec du contenu est gardée.
        ignorer.forEach(i => { if (entetes[i] === '' && lignes.some(l => (l[i] || '') !== '')) ignorer.delete(i); });
        const garder = (_, i) => !ignorer.has(i);
        return { entetes: entetes.filter(garder), lignes: lignes.map(l => l.filter(garder)) };
    }

    function nomDeFichier(titre, extension) {
        const base = (ETABLISSEMENT.sigle + ' ' + titre).normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^A-Za-z0-9]+/g, '-').replace(/^-|-$/g, '').toLowerCase();
        return base + '-' + new Date().toISOString().slice(0, 10) + '.' + extension;
    }

    function enNombre(valeur, entete) {
        if (/t[ée]l|phone|contact|matricule|code|ann[ée]e|date/i.test(entete)) return valeur;
        const brut = valeur.replace(/\s|FCFA|XAF|F CFA/gi, '');
        return /^-?\d+([.,]\d+)?$/.test(brut) && brut.length < 15 ? Number(brut.replace(',', '.')) : valeur;
    }

    function exporterExcel(table) {
        if (!window.XLSX) { alert('Le module Excel se charge encore, réessayez dans un instant.'); return; }
        const { entetes, lignes } = lireTableau(table), titre = titreDuTableau(table);
        const donnees = [[ETABLISSEMENT.nom + ' — ' + titre], ['Exporté le ' + new Date().toLocaleString('fr-FR')], [], entetes,
            ...lignes.map(l => l.map((v, i) => enNombre(v, entetes[i] || '')))];
        const feuille = XLSX.utils.aoa_to_sheet(donnees);
        feuille['!cols'] = entetes.map((e, i) => ({ wch: Math.min(45, Math.max(e.length, ...lignes.map(l => String(l[i] || '').length)) + 2) }));
        feuille['!autofilter'] = { ref: XLSX.utils.encode_range({ s: { r: 3, c: 0 }, e: { r: 3 + lignes.length, c: Math.max(0, entetes.length - 1) } }) };
        const classeur = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(classeur, feuille, titre.replace(/[\\\/?*\[\]:]/g, ' ').slice(0, 31) || 'Export');
        XLSX.writeFile(classeur, nomDeFichier(titre, 'xlsx'));
    }

    function exporterCsv(table) {
        const { entetes, lignes } = lireTableau(table);
        const cellule = v => '"' + String(v).replace(/"/g, '""') + '"';
        const csv = [entetes, ...lignes].map(l => l.map(cellule).join(';')).join('\r\n');
        const lien = document.createElement('a');
        lien.href = URL.createObjectURL(new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8' }));
        lien.download = nomDeFichier(titreDuTableau(table), 'csv');
        document.body.appendChild(lien); lien.click(); lien.remove();
    }

    function exporterPdf(table) {
        const { entetes, lignes } = lireTableau(table), titre = titreDuTableau(table);
        const echapper = v => String(v).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
        const fenetre = window.open('', '_blank');
        if (!fenetre) { alert("Autorisez l'ouverture de fenêtres pour exporter en PDF."); return; }
        fenetre.document.write(`<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>${echapper(titre)}</title>
            <style>
                @page { size: ${entetes.length > 6 ? 'A4 landscape' : 'A4'}; margin: 12mm; }
                body { font-family: Arial, sans-serif; color: #111; font-size: 11px; }
                header { display: flex; align-items: center; gap: 12px; border-bottom: 2px solid #065f46; padding-bottom: 8px; margin-bottom: 12px; }
                header img { height: 48px; } h1 { font-size: 16px; margin: 0; color: #065f46; } p { margin: 2px 0; color: #555; }
                table { width: 100%; border-collapse: collapse; } th { background: #065f46; color: #fff; text-align: left; }
                th, td { border: 1px solid #cbd5e1; padding: 4px 6px; vertical-align: top; } tr:nth-child(even) td { background: #f1f5f9; }
                footer { margin-top: 10px; color: #777; font-size: 10px; }
            </style></head><body>
            <header><img src="${ETABLISSEMENT.logo}" alt=""><div><h1>${echapper(titre)}</h1><p>${echapper(ETABLISSEMENT.nom)}</p><p>Édité le ${new Date().toLocaleString('fr-FR')} · ${lignes.length} ligne(s)</p></div></header>
            <table><thead><tr>${entetes.map(e => `<th>${echapper(e)}</th>`).join('')}</tr></thead>
            <tbody>${lignes.map(l => `<tr>${l.map(v => `<td>${echapper(v)}</td>`).join('')}</tr>`).join('')}</tbody></table>
            <footer>${echapper(ETABLISSEMENT.sigle)}</footer>
            <script>window.onload = () => { window.print(); };<\/script></body></html>`);
        fenetre.document.close();
    }

    function tableauxExportables() {
        // Les grilles de calendrier (FullCalendar) sont faites de tableaux : ce ne sont pas des données.
        return [...document.querySelectorAll('#gabarit-contenu table')].filter(t =>
            t.offsetParent !== null && !t.closest('[data-sans-export], .fc') && t.querySelector('tbody tr, tr'));
    }

    function construireMenuExport() {
        const zone = document.getElementById('export-zone'), menu = document.getElementById('export-menu');
        if (!zone) return;
        const tableaux = tableauxExportables();
        zone.hidden = tableaux.length === 0;
        document.getElementById('export-compte').textContent = tableaux.length > 1 ? tableaux.length : '';
        menu.innerHTML = '';
        tableaux.forEach((table, index) => {
            const { lignes } = lireTableau(table), url = table.dataset.exportUrl;
            const bloc = document.createElement('div');
            bloc.className = 'rounded-lg p-2 hover:bg-slate-50';
            bloc.innerHTML = `<p class="font-medium text-gray-800 truncate"></p>
                <p class="text-xs text-gray-500 mb-2"></p>
                <div class="flex gap-2">
                    <button type="button" data-format="xlsx" class="flex-1 rounded-md bg-green-700 text-white py-1.5 text-xs font-medium hover:bg-green-800">Excel</button>
                    <button type="button" data-format="csv" class="flex-1 rounded-md border border-gray-300 py-1.5 text-xs font-medium hover:bg-white">CSV</button>
                    <button type="button" data-format="pdf" class="flex-1 rounded-md bg-red-700 text-white py-1.5 text-xs font-medium hover:bg-red-800">PDF</button>
                </div>`;
            bloc.querySelector('p').textContent = (tableaux.length > 1 ? (index + 1) + '. ' : '') + titreDuTableau(table);
            bloc.querySelectorAll('p')[1].textContent = url ? 'Excel et CSV : toutes les pages, avec les filtres' : lignes.length + ' ligne(s) affichée(s)';
            bloc.addEventListener('mouseenter', () => table.style.outline = '2px solid #2563eb');
            bloc.addEventListener('mouseleave', () => table.style.outline = '');
            bloc.querySelectorAll('button').forEach(b => b.addEventListener('click', () => {
                exporterTableau(table, b.dataset.format);
                menu.classList.add('hidden');
                table.style.outline = '';
            }));
            menu.appendChild(bloc);
        });
        barresExport(tableaux);
    }

    function exporterTableau(table, format) {
        const url = table.dataset.exportUrl;
        if (url && format !== 'pdf') { window.location.href = url + (url.includes('?') ? '&' : '?') + 'format=' + format; }
        else if (format === 'xlsx') exporterExcel(table);
        else if (format === 'csv') exporterCsv(table);
        else exporterPdf(table);
    }

    /* Une petite barre Excel / CSV / PDF au-dessus de chaque tableau. Livewire peut la
       retirer en redessinant la page : l'observateur la remet aussitôt. */
    function barresExport(tableaux) {
        tableaux.forEach(table => {
            const bloc = table.closest('.overflow-x-auto') || table;
            if (bloc.previousElementSibling?.hasAttribute('data-barre-export')) return;
            const barre = document.createElement('div');
            barre.setAttribute('data-barre-export', '');
            barre.innerHTML = '<span class="text-gray-500 mr-1">Exporter</span>'
                + '<button type="button" data-format="xlsx"><span class="w-2 h-2 rounded-full bg-green-600"></span>Excel</button>'
                + '<button type="button" data-format="csv"><span class="w-2 h-2 rounded-full bg-slate-400"></span>CSV</button>'
                + '<button type="button" data-format="pdf"><span class="w-2 h-2 rounded-full bg-red-600"></span>PDF</button>';
            barre.querySelectorAll('button').forEach(b => b.addEventListener('click', () => {
                const suivant = barre.nextElementSibling;
                const cible = suivant?.matches('table') ? suivant : suivant?.querySelector('table');
                if (cible) exporterTableau(cible, b.dataset.format);
            }));
            bloc.parentNode.insertBefore(barre, bloc);
        });
    }

    document.getElementById('export-bouton')?.addEventListener('click', construireMenuExport, true);
    let attenteExport;
    new MutationObserver(() => { clearTimeout(attenteExport); attenteExport = setTimeout(construireMenuExport, 250); })
        .observe(document.getElementById('gabarit-contenu'), { childList: true, subtree: true });
    document.addEventListener('DOMContentLoaded', construireMenuExport);
    </script>
@endif
</body>

</html>
