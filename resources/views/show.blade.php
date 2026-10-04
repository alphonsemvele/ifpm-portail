<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $article->title }} - IFPM Ndazoa</title>
    <meta name="description" content="{{ Str::limit(strip_tags($article->content), 160) }}">

    <!-- Open Graph (partage réseaux sociaux) -->
    <meta property="og:title" content="{{ $article->title }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($article->content), 200) }}">
    @if($article->images->count() > 0)
        <meta property="og:image" content="{{ $article->images->first()->path }}">
    @endif
    <meta property="og:type" content="article">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Configuration Tailwind avec couleurs IFPM -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ifpm: {
                            primary: '#bf1f2b',
                            'primary-dark': '#8b1620',
                            secondary: '#f59e0b',
                            'secondary-dark': '#d97706',
                            dark: '#1a1a2e',
                            light: '#fef3c7',
                        }
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        /* Dégradé IFPM */
        .ifpm-gradient {
            background: linear-gradient(135deg, #bf1f2b 0%, #1a1a2e 100%);
        }

        .ifpm-gradient-warm {
            background: linear-gradient(135deg, #bf1f2b 0%, #f59e0b 100%);
        }

        /* Pattern décoratif sur le header */
        .ifpm-header-pattern {
            background-image:
                linear-gradient(135deg, rgba(191, 31, 43, 0.95) 0%, rgba(26, 26, 46, 0.95) 100%),
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        /* Article content typographie */
        .article-content {
            line-height: 1.85;
            font-size: 1.0625rem;
            color: #374151;
            white-space: pre-line; /* Préserve les sauts de ligne du texte saisi */
        }

        /* Drop cap (première lettre) */
        .drop-cap::first-letter {
            font-size: 4.5rem;
            font-weight: 800;
            color: #bf1f2b;
            float: left;
            line-height: 1;
            margin: 0.1rem 0.75rem 0 0;
            font-family: 'Poppins', sans-serif;
        }

        .share-btn {
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
        }

        .share-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        /* Galerie style Facebook */
        .fb-image-grid {
            display: grid;
            gap: 4px;
            margin: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        }

        .fb-image-grid.grid-1 { grid-template-columns: 1fr; }
        .fb-image-grid.grid-1 .fb-image-item img { max-height: 600px; }

        .fb-image-grid.grid-2 { grid-template-columns: repeat(2, 1fr); }
        .fb-image-grid.grid-2 .fb-image-item img { height: 400px; }

        .fb-image-grid.grid-3 {
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
        }
        .fb-image-grid.grid-3 .fb-image-item:first-child { grid-row: span 2; }
        .fb-image-grid.grid-3 .fb-image-item:first-child img { height: 100%; min-height: 400px; }
        .fb-image-grid.grid-3 .fb-image-item:not(:first-child) img { height: 198px; }

        .fb-image-grid.grid-4 { grid-template-columns: repeat(2, 1fr); }
        .fb-image-grid.grid-4 .fb-image-item img { height: 300px; }

        .fb-image-grid.grid-5-plus {
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
        }
        .fb-image-grid.grid-5-plus .fb-image-item:first-child { grid-column: span 2; }
        .fb-image-grid.grid-5-plus .fb-image-item:first-child img { height: 400px; }
        .fb-image-grid.grid-5-plus .fb-image-item:not(:first-child) img { height: 200px; }

        .fb-image-item {
            position: relative;
            overflow: hidden;
            background: #f0f2f5;
            cursor: pointer;
        }

        .fb-image-item img {
            width: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.5s ease;
        }

        .fb-image-item:hover img {
            transform: scale(1.06);
        }

        .fb-image-item::after {
            content: '\f00e';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            color: white;
            font-size: 2rem;
            background: rgba(191, 31, 43, 0.85);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .fb-image-item:hover::after {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }

        /* Overlay "+X photos" */
        .fb-more-overlay {
            position: absolute;
            inset: 0;
            background: rgba(26, 26, 46, 0.7);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            pointer-events: none;
        }

        /* Lightbox */
        .lightbox {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background-color: rgba(0, 0, 0, 0.95);
            justify-content: center;
            align-items: center;
            padding: 60px 20px;
        }

        .lightbox.active {
            display: flex;
        }

        .lightbox-content {
            max-width: 90%;
            max-height: 85vh;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            z-index: 10000;
            background: rgba(191, 31, 43, 0.8);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border: none;
        }

        .lightbox-close:hover {
            background: #bf1f2b;
            transform: rotate(90deg);
        }

        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: none;
            width: 56px;
            height: 56px;
            cursor: pointer;
            font-size: 1.4rem;
            border-radius: 50%;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .lightbox-nav:hover {
            background: #bf1f2b;
        }

        .lightbox-prev { left: 30px; }
        .lightbox-next { right: 30px; }

        .lightbox-counter {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            background: rgba(0, 0, 0, 0.6);
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        /* Document card */
        .doc-card {
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
        }

        .doc-card:hover {
            border-color: #bf1f2b;
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(191, 31, 43, 0.12);
        }

        /* Boutons flottants */
        .floating-btn {
            position: fixed;
            right: 20px;
            width: 55px;
            height: 55px;
            border-radius: 50%;
            color: white !important;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            z-index: 998;
            transition: transform 0.3s;
            text-decoration: none !important;
            font-size: 1.5rem;
        }

        .floating-btn:hover {
            transform: scale(1.1);
        }

        .whatsapp-btn { background: #25D366; bottom: 20px; }
        .messenger-btn { background: #0084FF; bottom: 85px; }

        /* Bouton "remonter en haut" */
        .scroll-top {
            position: fixed;
            bottom: 150px;
            right: 20px;
            width: 50px;
            height: 50px;
            background: #bf1f2b;
            color: white;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            z-index: 997;
            border: none;
            transition: all 0.3s ease;
        }

        .scroll-top.visible {
            display: flex;
        }

        .scroll-top:hover {
            background: #8b1620;
            transform: translateY(-3px);
        }

        @media (max-width: 768px) {
            .fb-image-grid.grid-2 .fb-image-item img,
            .fb-image-grid.grid-4 .fb-image-item img {
                height: 200px;
            }
            .fb-image-grid.grid-3 .fb-image-item:first-child img {
                min-height: 200px;
            }
            .fb-image-grid.grid-5-plus .fb-image-item:first-child img {
                height: 250px;
            }
            .lightbox-prev { left: 10px; }
            .lightbox-next { right: 10px; }
            .lightbox-nav { width: 44px; height: 44px; }
            .drop-cap::first-letter {
                font-size: 3.5rem;
            }
        }
    </style>
</head>

<body class="bg-gray-50">

    <!-- HEADER avec retour -->
    <div class="ifpm-header-pattern py-12 mb-10 relative overflow-hidden">
        <div class="container mx-auto px-4 relative z-10">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <a href="/" class="inline-flex items-center gap-3 group">
                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                        <span class="text-ifpm-primary font-bold text-sm">IFPM</span>
                    </div>
                    <div class="text-white">
                        <p class="font-bold text-lg leading-tight">IFPM Ndazoa</p>
                        <p class="text-xs text-white/80">Institut de Formation Professionnelle</p>
                    </div>
                </a>

                <a href="/#actualites" class="inline-flex items-center text-white hover:text-ifpm-secondary transition-colors group">
                    <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                    <span class="font-medium">Retour aux actualités</span>
                </a>
            </div>
        </div>
    </div>

    <!-- CONTENU PRINCIPAL -->
    <article class="container mx-auto px-4 sm:px-6 lg:px-8 pb-20">
        <div class="max-w-4xl mx-auto">

            <!-- Badge catégorie -->
            <div class="mb-6" data-aos="fade-down">
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-ifpm-primary/10 text-ifpm-primary rounded-full text-sm font-semibold">
                    <i class="fas fa-newspaper"></i> Actualités IFPM
                </span>
            </div>

            <!-- Titre -->
            <h1 class="text-3xl md:text-5xl font-bold text-ifpm-dark mb-6 leading-tight" data-aos="fade-up">
                {{ $article->title }}
            </h1>

            <!-- Métadonnées -->
            <div class="flex flex-wrap items-center gap-6 mb-10 text-gray-600 pb-6 border-b-2 border-gray-100" data-aos="fade-up" data-aos-delay="100">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 bg-ifpm-primary/10 rounded-full flex items-center justify-center">
                        <i class="far fa-calendar text-ifpm-primary text-sm"></i>
                    </div>
                    <span class="text-sm font-medium">{{ \Carbon\Carbon::parse($article->published_at)->format('d M Y') }}</span>
                </div>
                @if($article->images->count() > 0)
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 bg-ifpm-primary/10 rounded-full flex items-center justify-center">
                            <i class="far fa-images text-ifpm-primary text-sm"></i>
                        </div>
                        <span class="text-sm font-medium">{{ $article->images->count() }} {{ $article->images->count() > 1 ? 'photos' : 'photo' }}</span>
                    </div>
                @endif
                @if($article->documents->count() > 0)
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 bg-ifpm-primary/10 rounded-full flex items-center justify-center">
                            <i class="far fa-file-pdf text-ifpm-primary text-sm"></i>
                        </div>
                        <span class="text-sm font-medium">{{ $article->documents->count() }} {{ $article->documents->count() > 1 ? 'documents' : 'document' }}</span>
                    </div>
                @endif
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 bg-ifpm-primary/10 rounded-full flex items-center justify-center">
                        <i class="far fa-user text-ifpm-primary text-sm"></i>
                    </div>
                    <span class="text-sm font-medium">IFPM Ndazoa</span>
                </div>
            </div>

            <!-- GALERIE D'IMAGES (style Facebook) -->
            @if($article->images->count() > 0)
                <div class="mb-10" data-aos="zoom-in" data-aos-delay="200">
                    @php
                        $imageCount = $article->images->count();
                        $gridClass = 'grid-1';
                        if ($imageCount == 2) $gridClass = 'grid-2';
                        elseif ($imageCount == 3) $gridClass = 'grid-3';
                        elseif ($imageCount == 4) $gridClass = 'grid-4';
                        elseif ($imageCount >= 5) $gridClass = 'grid-5-plus';
                    @endphp

                    <div class="fb-image-grid {{ $gridClass }}">
                        @foreach($article->images as $index => $image)
                            @if($imageCount >= 5 && $index >= 4)
                                @if($index == 4)
                                    <div class="fb-image-item" onclick="openLightbox({{ $index }})">
                                        <img src="{{ $image->path }}" alt="Image {{ $index + 1 }}">
                                        @if($imageCount > 5)
                                            <div class="fb-more-overlay">+{{ $imageCount - 4 }}</div>
                                        @endif
                                    </div>
                                @endif
                            @else
                                <div class="fb-image-item" onclick="openLightbox({{ $index }})">
                                    <img src="{{ $image->path }}" alt="Image {{ $index + 1 }}">
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <p class="text-center text-sm text-gray-500 mt-3">
                        <i class="far fa-hand-pointer mr-1"></i> Cliquez sur une image pour l'agrandir
                    </p>
                </div>
            @endif

            <!-- BARRE DE PARTAGE -->
            <div class="bg-white rounded-2xl shadow-md p-6 mb-10 border border-gray-100" data-aos="fade-up" data-aos-delay="300">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-ifpm-dark font-semibold flex items-center gap-2">
                            <i class="fas fa-share-nodes text-ifpm-primary"></i> Partager cet article
                        </p>
                        <p class="text-sm text-gray-500 mt-1">Faites découvrir l'IFPM à votre entourage</p>
                    </div>
                    <div class="flex gap-2 flex-wrap">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank"
                           class="share-btn bg-[#1877F2] text-white rounded-lg" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($article->title) }}" target="_blank"
                           class="share-btn bg-black text-white rounded-lg" title="X (Twitter)">
                            <i class="fab fa-x-twitter"></i>
                        </a>
                        <a href="https://wa.me/?text={{ urlencode($article->title . ' ' . url()->current()) }}" target="_blank"
                           class="share-btn bg-[#25D366] text-white rounded-lg" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(url()->current()) }}&title={{ urlencode($article->title) }}" target="_blank"
                           class="share-btn bg-[#0A66C2] text-white rounded-lg" title="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="mailto:?subject={{ urlencode($article->title) }}&body={{ urlencode(url()->current()) }}"
                           class="share-btn bg-gray-700 text-white rounded-lg" title="Email">
                            <i class="fas fa-envelope"></i>
                        </a>
                        <button onclick="copyArticleLink()"
                                class="share-btn bg-ifpm-primary text-white rounded-lg" title="Copier le lien" id="copyBtn">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- CONTENU DE L'ARTICLE -->
            <div class="bg-white rounded-2xl shadow-md p-6 md:p-12 mb-10 border border-gray-100" data-aos="fade-up" data-aos-delay="400">
                <div class="article-content drop-cap">{{ $article->content }}</div>
            </div>

            <!-- DOCUMENTS À TÉLÉCHARGER -->
            @if($article->documents->count() > 0)
                <div class="bg-white rounded-2xl shadow-md p-6 md:p-10 mb-10 border border-gray-100" data-aos="fade-up" data-aos-delay="500">
                    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
                        <h3 class="text-xl md:text-2xl font-bold text-ifpm-dark flex items-center gap-3">
                            <div class="w-10 h-10 bg-ifpm-primary rounded-lg flex items-center justify-center">
                                <i class="fas fa-file-download text-white"></i>
                            </div>
                            {{ $article->documents->count() > 1 ? 'Documents joints' : 'Document joint' }}
                        </h3>
                        @if($article->documents->count() > 1)
                            <button onclick="downloadAllDocuments()"
                                    class="bg-ifpm-primary hover:bg-ifpm-primary-dark text-white px-5 py-2.5 rounded-lg font-medium transition-all flex items-center gap-2 shadow-md">
                                <i class="fas fa-download"></i> Tout télécharger
                            </button>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($article->documents as $document)
                            <div class="doc-card rounded-xl p-4 bg-gray-50">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-file-pdf text-red-600 text-xl"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-ifpm-dark truncate" title="{{ basename($document->path) }}">
                                                {{ basename($document->path) }}
                                            </p>
                                            <p class="text-sm text-gray-500">Document {{ $loop->iteration }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ $document->path }}" target="_blank" download
                                       class="bg-ifpm-primary hover:bg-ifpm-primary-dark text-white px-4 py-2.5 rounded-lg transition-colors flex items-center gap-2 flex-shrink-0">
                                        <i class="fas fa-download"></i>
                                        <span class="hidden sm:inline text-sm font-medium">Télécharger</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- CALL TO ACTION -->
            <div class="ifpm-gradient rounded-2xl p-8 md:p-12 text-center text-white relative overflow-hidden" data-aos="fade-up" data-aos-delay="600">
                <!-- Décoration -->
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-ifpm-secondary/20 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-ifpm-secondary/20 rounded-full blur-3xl"></div>

                <div class="relative z-10">
                    <div class="inline-block w-16 h-16 bg-white/10 backdrop-blur rounded-2xl flex items-center justify-center mb-4">
                        <i class="fas fa-graduation-cap text-3xl text-ifpm-secondary"></i>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-bold mb-3">Prêt à apprendre un métier ?</h2>
                    <p class="text-base md:text-lg mb-8 text-white/90 max-w-2xl mx-auto">
                        Rejoignez l'IFPM Ndazoa et formez-vous aux métiers porteurs : couture, maçonnerie, plomberie, coiffure, soudure, informatique...
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center flex-wrap">
                        <a href="/#admission" class="inline-flex items-center justify-center px-7 py-3.5 bg-ifpm-secondary hover:bg-ifpm-secondary-dark text-white rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            <i class="fas fa-user-plus mr-2"></i> Je m'inscris
                        </a>
                        <a href="/#metiers" class="inline-flex items-center justify-center px-7 py-3.5 bg-white text-ifpm-primary rounded-xl font-semibold hover:bg-gray-100 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            <i class="fas fa-tools mr-2"></i> Voir les métiers
                        </a>
                        <a href="/#actualites" class="inline-flex items-center justify-center px-7 py-3.5 bg-white/10 backdrop-blur border-2 border-white/30 text-white rounded-xl font-semibold hover:bg-white/20 transition-all">
                            <i class="fas fa-newspaper mr-2"></i> Autres actualités
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </article>

    <!-- LIGHTBOX -->
    <div class="lightbox" id="lightbox">
        <button class="lightbox-close" onclick="closeLightbox()">
            <i class="fas fa-times"></i>
        </button>
        @if($article->images->count() > 1)
            <button class="lightbox-nav lightbox-prev" onclick="changeImage(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="lightbox-nav lightbox-next" onclick="changeImage(1)">
                <i class="fas fa-chevron-right"></i>
            </button>
            <div class="lightbox-counter" id="lightboxCounter">1 / {{ $article->images->count() }}</div>
        @endif
        <img class="lightbox-content" id="lightbox-img" alt="">
    </div>

    <!-- BOUTONS FLOTTANTS -->
    <a href="https://wa.me/+237691612145?text=Bonjour%2C%20je%20souhaite%20avoir%20plus%20d%27informations%20sur%20l%27IFPM%20Ndazoa." class="floating-btn whatsapp-btn" target="_blank" title="WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>
    <a href="https://www.facebook.com/profile.php?id=61577186635321" class="floating-btn messenger-btn" target="_blank" title="Messenger">
        <i class="fab fa-facebook-messenger"></i>
    </a>
    <button class="scroll-top" id="scrollTop" onclick="scrollToTop()" title="Remonter en haut">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- FOOTER -->
    <footer class="ifpm-gradient text-white py-10">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <div>
                    <h4 class="font-bold text-lg mb-3 text-ifpm-secondary">IFPM Ndazoa</h4>
                    <p class="text-sm text-white/85 leading-relaxed">
                        Institut de Formation Professionnelle La Majestueuse de Ndazoa. Apprenez un métier, construisez votre avenir.
                    </p>
                </div>
                <div>
                    <h4 class="font-bold text-lg mb-3 text-ifpm-secondary">Contact</h4>
                    <ul class="space-y-2 text-sm text-white/85">
                        <li><i class="fas fa-map-marker-alt mr-2 text-ifpm-secondary"></i> Ndazoa, 7km de Mbankomo</li>
                        <li><i class="fas fa-phone mr-2 text-ifpm-secondary"></i> +237 691 612 145</li>
                        <li><i class="fas fa-envelope mr-2 text-ifpm-secondary"></i> info@ifpm-ndazoa.com</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-lg mb-3 text-ifpm-secondary">Suivez-nous</h4>
                    <div class="flex gap-3">
                        <a href="#" class="w-10 h-10 bg-white/10 hover:bg-ifpm-primary rounded-full flex items-center justify-center transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-white/10 hover:bg-ifpm-primary rounded-full flex items-center justify-center transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-white/10 hover:bg-ifpm-primary rounded-full flex items-center justify-center transition-colors">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-white/10 hover:bg-ifpm-primary rounded-full flex items-center justify-center transition-colors">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-white/20 pt-6 text-center">
                <p class="text-sm text-white/80">© 2025 <strong>IFPM Ndazoa</strong> - Institut de Formation Professionnelle La Majestueuse. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- Notification copie -->
    <div id="copyNotification" class="fixed top-6 left-1/2 transform -translate-x-1/2 bg-ifpm-dark text-white px-6 py-3 rounded-lg shadow-2xl opacity-0 transition-opacity z-[10001] flex items-center gap-2">
        <i class="fas fa-check-circle text-ifpm-secondary"></i>
        <span>Lien copié dans le presse-papier !</span>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        const images = @json($article->images->pluck('path'));
        const documents = @json($article->documents->pluck('path'));
        let currentImageIndex = 0;

        // === LIGHTBOX ===
        function openLightbox(index) {
            currentImageIndex = index;
            document.getElementById('lightbox').classList.add('active');
            document.getElementById('lightbox-img').src = images[currentImageIndex];
            updateCounter();
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            document.getElementById('lightbox').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function changeImage(direction) {
            currentImageIndex += direction;
            if (currentImageIndex < 0) currentImageIndex = images.length - 1;
            if (currentImageIndex >= images.length) currentImageIndex = 0;
            document.getElementById('lightbox-img').src = images[currentImageIndex];
            updateCounter();
        }

        function updateCounter() {
            const counter = document.getElementById('lightboxCounter');
            if (counter) {
                counter.textContent = `${currentImageIndex + 1} / ${images.length}`;
            }
        }

        // Raccourcis clavier
        document.addEventListener('keydown', function(event) {
            if (!document.getElementById('lightbox').classList.contains('active')) return;
            if (event.key === 'Escape') closeLightbox();
            if (event.key === 'ArrowLeft') changeImage(-1);
            if (event.key === 'ArrowRight') changeImage(1);
        });

        // Empêcher la propagation du clic sur l'image
        document.getElementById('lightbox-img').addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Fermer en cliquant sur le fond
        document.getElementById('lightbox').addEventListener('click', function(e) {
            if (e.target === this) closeLightbox();
        });

        // === TÉLÉCHARGEMENT GROUPÉ ===
        function downloadAllDocuments() {
            documents.forEach((url, idx) => {
                setTimeout(() => {
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = url.split('/').pop();
                    a.target = '_blank';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }, idx * 700);
            });
        }

        // === COPIE DU LIEN ===
        function copyArticleLink() {
            const url = window.location.href;
            const notification = document.getElementById('copyNotification');
            const btn = document.getElementById('copyBtn');

            const showNotif = () => {
                notification.style.opacity = '1';
                btn.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(() => {
                    notification.style.opacity = '0';
                    btn.innerHTML = '<i class="fas fa-link"></i>';
                }, 2500);
            };

            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(showNotif).catch(() => fallbackCopy(url, showNotif));
            } else {
                fallbackCopy(url, showNotif);
            }
        }

        function fallbackCopy(text, callback) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                callback();
            } catch (err) {
                console.error('Copie échouée', err);
            }
            document.body.removeChild(textArea);
        }

        // === SCROLL TO TOP ===
        const scrollTopBtn = document.getElementById('scrollTop');
        window.addEventListener('scroll', function() {
            if (window.scrollY > 400) {
                scrollTopBtn.classList.add('visible');
            } else {
                scrollTopBtn.classList.remove('visible');
            }
        });

        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
</body>
</html>