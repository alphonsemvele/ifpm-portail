<!DOCTYPE html>
<html class="no-js" lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Institut de Formation Professionnelle La Majestueuse</title>
    <meta name="author" content="themeholy">
    <meta name="description" content="L'Institut  de Formation Professionnelle La Majestueuse">
    <meta name="keywords"
        content="INSTITUT SUPERIEUR LA MAJESTUEUSE, Ndazoa, formation professionnelle, bilingue, Cameroun">
    <meta name="robots" content="INDEX,FOLLOW">
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('asset_vitrine/assets/img/logo.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('asset_vitrine/assets/img/logo.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('asset_vitrine/assets/img/logo.png') }}">
    <link rel="manifest" href="{{ asset('asset_vitrine/assets/img/favicons/manifest.json') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#1a73e8">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('asset_vitrine/assets/css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('asset_vitrine/assets/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css')}}">
    <link rel="stylesheet" href="{{ asset('asset_vitrine/assets/css/style.css') }}">

    <!-- Inline CSS for quick design enhancements -->
    <style>
        /* Nouveau Preloader Magnifique */
        .magnificent-preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #2f54a4, #101d3f, #bf1f2b, #4ecdc4);
            background-size: 400% 400%;
            animation: gradientShift 8s ease infinite;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            overflow: hidden;
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .preloader-logo-container {
            position: relative;
            margin-bottom: 40px;

        }

        .preloader-logo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background-color: white;
            padding: 20px;
            /* background: rgba(255, 255, 255, 0.15); */
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            animation: logoFloat 3s ease-in-out infinite, logoGlow 2s ease-in-out infinite alternate;
            box-shadow:
                0 25px 50px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.05),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .preloader-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.2));
        }

        @keyframes logoFloat {

            0%,
            100% {
                transform: translateY(0px) scale(1);
            }

            50% {
                transform: translateY(-20px) scale(1.05);
            }
        }

        @keyframes logoGlow {
            0% {
                box-shadow:
                    0 25px 50px rgba(0, 0, 0, 0.1),
                    0 0 30px rgba(26, 115, 232, 0.3),
                    0 0 60px rgba(26, 115, 232, 0.1);
            }

            100% {
                box-shadow:
                    0 25px 50px rgba(0, 0, 0, 0.1),
                    0 0 40px rgba(52, 199, 89, 0.4),
                    0 0 80px rgba(52, 199, 89, 0.2);
            }
        }

        .preloader-particles {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            animation: particleFloat 4s linear infinite;
        }

        @keyframes particleFloat {
            0% {
                opacity: 0;
                transform: translateY(100vh) scale(0);
            }

            10% {
                opacity: 1;
                transform: translateY(90vh) scale(1);
            }

            90% {
                opacity: 1;
                transform: translateY(-10vh) scale(1);
            }

            100% {
                opacity: 0;
                transform: translateY(-20vh) scale(0);
            }
        }

        .preloader-progress {
            width: 300px;
            height: 6px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }

        .preloader-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #fff, rgba(255, 255, 255, 0.8));
            border-radius: 10px;
            width: 0%;
            animation: progressLoad 3s ease-in-out forwards;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
        }

        @keyframes progressLoad {
            0% {
                width: 0%;
            }

            100% {
                width: 100%;
            }
        }

        .preloader-text {
            color: white;
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 300;
            text-align: center;
            margin-bottom: 20px;
            animation: textPulse 2s ease-in-out infinite;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        @keyframes textPulse {

            0%,
            100% {
                opacity: 0.7;
                transform: scale(1);
            }

            50% {
                opacity: 1;
                transform: scale(1.02);
            }
        }

        .preloader-spinner {
            width: 60px;
            height: 60px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-top: 20px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .preloader-fadeout {
            animation: fadeOut 1s ease-in-out forwards;
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
            }

            100% {
                opacity: 0;
                visibility: hidden;
            }
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .th-hero-wrapper {
            background: linear-gradient(135deg, #1a73e8, #34c759);
            color: #fff;
        }

        .th-btn {
            background: #1a73e8;
            color: #fff;
            padding: 12px 24px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .th-btn:hover {
            background: #1557b0;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .feature-card,
        .admission-card {
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .feature-card:hover,
        .admission-card:hover {
            transform: translateY(-5px);
        }

        .sec-title {
            font-weight: 700;
            color: #1a73e8;
        }

        .contact-form-wrap {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 30px;
        }

        /* Justifier les textes */
        .admission-card_text,
        .hero-text,
        .cta-text,
        .feature-card_text,
        .testi-list_text,
        .about-text,
        p,
        .student-text,
        .contact-feature_link,
        .sec-text,
        .info-box_text {
            text-align: justify;
        }

        /* Uniformiser la hauteur des blocs dans la section "Nos programmes" */
        #course-sec .row {
            display: flex;
            flex-wrap: wrap;
        }

        #course-sec .col-md-6 {
            display: flex;
            flex-direction: column;
        }

        .admission-card_content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            position: relative;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .modal-content h3 {
            margin-top: 0;
            color: #1a73e8;
            text-align: center;
        }

        .modal-content .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
            color: #333;
        }

        .modal-content .form-group {
            margin-bottom: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .modal-content .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            width: 100%;
            text-align: left;
        }

        .modal-content .form-group input,
        .modal-content .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            height: 50px;
        }

        .modal-content .form-group input[readonly] {
            background-color: #f1f1f1;
            cursor: not-allowed;
        }

        .modal-content .form-group {
            width: calc(50% - 7.5px);
        }

        .modal-content .th-btn {
            width: 100%;
            margin-top: 20px;
            padding: 14px;
            font-size: 16px;
        }

        /* Modification pour superposer la newsletter */
        .susbcribe-bg {
            position: relative;
            overflow: hidden;
        }

        .newsletter-overlay {
            position: absolute;
            top: -50px;
            /* Ajustez cette valeur pour déplacer le bloc vers le haut ou le bas */
            left: 0;
            width: 100%;
            background: linear-gradient(to right, #4285f4, #6ab7ff);
            /* Bleu ciel */
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
            /* Assure que le bloc est au-dessus */
            border-radius: 8px;
        }

        .newsletter-overlay h3 {
            color: #fff;
            margin: 0 0 10px 0;
            font-size: 1.5em;
        }

        .newsletter-overlay .newsletter-form {
            display: flex;
            align-items: center;
        }

        .newsletter-overlay .newsletter-form .form-group {
            margin-bottom: 0;
            margin-right: 10px;
        }

        .newsletter-overlay .newsletter-form .form-control.style2 {
            background: #fff;
            border: none;
            padding: 10px;
            border-radius: 4px;
        }

        .newsletter-overlay .newsletter-form .th-btn {
            padding: 10px 20px;
            font-size: 1em;
        }

        .newsletter-overlay .th-social a {
            color: #fff;
            margin-left: 10px;
            font-size: 1.2em;
        }
    </style>
</head>

<body>
    <div class="preloader">
        <button class="th-btn style3 preloaderCls">Annuler le préchargement</button>
        <div class="preloader-inner"><span class="loader"></span></div>
    </div>
    <div class="sidemenu-wrapper d-none d-lg-block">
        <div class="sidemenu-content">
            <button class="closeButton sideMenuCls"><i class="far fa-times"></i></button>
        </div>
    </div>
    <div class="popup-search-box d-none d-lg-block">
        <button class="searchClose"><i class="fal fa-times"></i></button>
        <form action="#">
            <input type="text" placeholder="Que cherchez-vous ?">
            <button type="submit"><i class="fal fa-search"></i></button>
        </form>
    </div>
    <div class="th-menu-wrapper">
        <div class="th-menu-area text-center">
            <button class="th-menu-toggle"><i class="fal fa-times"></i></button>
            <div class="mobile-logo">
                <a href="#"><img src="{{ asset('images/logo.png') }}" style="height: 120px; width: 120px"
                        alt="INSTITUT SUPERIEUR LA MAJESTUEUSE la Majestueuse"></a>
            </div>
            <div class="th-mobile-menu">
                <ul>
                    <li><a href="#">Accueil</a></li>
                    <li><a href="#about">À propos</a></li>
                    <li><a href="#formations">Formations</a></li>
                    <li><a href="#contact">Contactez-nous</a></li>
                </ul>
            </div>
        </div>
    </div>
    <header class="th-header header-layout14 onepage-nav">
        <div class="header-layout10">
            <div class="header-top">
                <div class="container">
                    <div class="row justify-content-center justify-content-lg-between align-items-center gy-2">
                        <div class="col-auto d-none d-lg-block">
                            <div class="header-links">
                                <ul>
                                    <li><i class="fas fa-envelope"></i><b>Contactez-nous :
                                        </b>info@ecoledemetiersndazoa.com</li>
                                    <li><i class="fas fa-phone"></i><b>Téléphone : </b>+237 691612145 | 695830031</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="header-links">
                                <ul>
                                    <li>
                                        <div class="header-social">
                                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                                            <a href="#"><i class="fab fa-twitter"></i></a>
                                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                            <a href="#"><i class="fab fa-instagram"></i></a>
                                            <a href="#"><i class="fab fa-youtube"></i></a>
                                        </div>
                                    </li>
                                    <li><i class="fas fa-user"></i><a href="/login">Connexion / Inscription</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="sticky-wrapper">
            <div class="sticky-active">
                <div class="menu-area">
                    <div class="container">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto">
                                <div class="header-logo">
                                    <a href="#"><img src="{{ asset('images/logo.png') }}"
                                            style="height: 120px; width: 120px"
                                            alt="INSTITUT SUPERIEUR LA MAJESTUEUSE la Majestueuse"></a>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <nav class="main-menu d-none d-lg-inline-block">
                                            <ul>
                                                <li><a href="#">Accueil</a></li>
                                                <li><a href="#about">À propos</a></li>
                                                <li><a href="#formations">Formations</a></li>

                                                <li><a href="#contact">Contactez-nous</a></li>
                                            </ul>
                                        </nav>
                                        <button type="button" class="th-menu-toggle d-inline-block d-lg-none"><i
                                                class="far fa-bars"></i></button>
                                    </div>
                                    <div class="col-auto d-none d-xxl-block">
                                        <div class="header-button">
                                            <button type="button" class="icon-btn searchBoxToggler"><i
                                                    class="far fa-search"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="logo-bg"></div>
                </div>
            </div>
        </div>
    </header>
    <div class="th-hero-wrapper hero-15 th-carousel" data-slide-show="1" data-md-slide-show="1" data-fade="true"
        id="hero">
        {{-- <div class="th-hero-slide" data-aos="fade-up" data-aos-duration="1000">
            <div class="th-hero-bg"
                data-bg-src="{{ asset('asset_vitrine/assets/img/update1/hero/hero_bg_10_3.jpg') }}">
            </div>
            <div class="container">
                <div class="hero-style15">
                    <span class="hero-name" data-aos="slide-down" data-aos-delay="500">Inscriptions OUVERTES POUR
                        2025 </span>
                    <h1 class="hero-title" data-aos="slide-down" data-aos-delay="300">Votre avenir commence ici</h1>
                    <div class="btn-group" data-aos="slide-up" data-aos-delay="300">
                        <a href="#" class="th-btn">A propos de nous<i
                                class="fas fa-long-arrow-right ms-2"></i></a>
                        <a href="#" class="th-btn style4"> Nos programmes de formations<i
                                class="fas fa-long-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
        </div> --}}

        <div class="th-hero-slide" data-aos="fade-up" data-aos-duration="1000">
            <div class="th-hero-bg"
                data-bg-src="{{ asset('asset_vitrine/assets/img/update1/hero/hero_bg_10_3.jpg') }}">
            </div>
            <div class="container">
                <div class="hero-style15">
                    <span class="hero-name" data-aos="slide-down" data-aos-delay="500">Début des cours en
                        Septembre</span>
                    <h1 class="hero-title" data-aos="slide-down" data-aos-delay="300">Formation d'excellence</h1>
                    <p class="hero-text" data-aos="slide-up" data-aos-delay="100">Nos formations pratiques et
                        bilingues vous préparent à des carrières réussies dans les métiers techniques et artisanaux.</p>
                    <div class="btn-group" data-aos="slide-up" data-aos-delay="300">
                        <a href="#" class="th-btn">A propos<i class="fas fa-long-arrow-right ms-2"></i></a>
                        <a href="#" class="th-btn style4">Nos programmes<i
                                class="fas fa-long-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="space" id="about" data-aos="fade-up" data-aos-duration="800">
        <div class="container">
            <div class="row flex-row-reverse align-items-center">
                <div class="col-xl-6" data-aos="fade-left" data-aos-delay="200">
                    <div class="img-box12">
                        <div class="img1"><img
                                src="{{ asset('asset_vitrine/assets/img/update1/normal/mockup_3_1.jpg') }}"
                                alt="Mockup"></div>
                        <div class="img2"><img
                                src="{{ asset('asset_vitrine/assets/img/update1/normal/mockup_3_2.jpg') }}"
                                alt="Mockup"></div>
                    </div>
                </div>
                <div class="col-xl-6" data-aos="fade-right" data-aos-delay="200">
                    <div class="title-area mb-35">
                        <span class="sub-title">INSCRIPTIONS OUVERTES POUR 2025</span>
                        <h2 class="sec-title fw-semibold">INSTITUT DE FORMATION PROFESSIONNELLE LA MAJESTUEUSE</h2>
                    </div>
                    <p class="mt-n2 mb-35">L’Institut de Formation Professionnelle La Majestueuse est un établissement
                        d’excellence dédié à la formation de futurs leaders. Grâce à des programmes innovants, un
                        encadrement par des experts chevronnés et des collaborations stratégiques avec des entreprises
                        de renom, nous préparons nos étudiants à réussir dans un monde professionnel en constante
                        mutation, en leur offrant des compétences pratiques et une vision globale pour une carrière
                        épanouissante.</p>
                    <div class="list-column2 mb-45">
                        <div class="checklist style4">
                            <ul>
                                <li>Formations professionnalisantes et bilingues</li>
                                <li>Suivi personnalisé des étudiants</li>
                            </ul>
                        </div>
                        <div class="checklist style4">
                            <ul>
                                <li>Certifications de haut niveau</li>
                                <li>Réseau d’entreprises partenaires</li>
                            </ul>
                        </div>
                    </div>
                    <a href="#" class="th-btn">En savoir plus<i class="fas fa-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
    </section>
    <section class="bg-smoke-half" data-aos="fade-up" data-aos-duration="800">
        <div class="container">
            <div class="row">
                <div class="col-xl-12 mb-30 mb-xl-0" data-aos="fade-right" data-aos-delay="100">
                    <div class="cta-box"
                        data-bg-src="{{ asset('asset_vitrine/assets/img/update1/bg/cta_bg_5.jpg') }}">
                        <h3 class="cta-title">Inscrivez-vous pour 2025</h3>
                        <p class="cta-text">Rejoignez nos programmes de formation pour acquérir des compétences
                            pratiques et démarrer une carrière prometteuse. Nos cours sont conçus pour répondre aux
                            besoins du marché local.</p>
                        <a href="#" class="th-btn">S'inscrire maintenant<i
                                class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="space bg-smoke" id="formations" data-aos="fade-up" data-aos-duration="800">
        <div class="container">
            <div class="title-area text-center">
                <span class="sub-title">Nos programmes</span>
                <h2 class="sec-title fw-semibold">Excellence dans la formation professionnelle à l'IFPM</h2>
            </div>
            <div class="row gy-4">
                <div class="col-12" data-aos="fade-up" data-aos-delay="100">
                    <div class="row gy-4" style="display: flex; flex-wrap: wrap; justify-content: space-between;">
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/elevage.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Agriculture et Élevage</h3>
                                    <p class="admission-card_text">Apprenez à produire des cultures et à élever des
                                        animaux pour une carrière durable.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Agriculture et Élevage">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/electrique.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Génie Électrique</h3>
                                    <p class="admission-card_text">Formez-vous en électricité et maintenance
                                        d'équipements industriels.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Génie Électrique">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/civil.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Génie Civil</h3>
                                    <p class="admission-card_text">Maîtrisez la construction et les travaux publics.
                                    </p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Génie Civil">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/petrolier.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Génie Géologique et Pétrolier</h3>
                                    <p class="admission-card_text">Explorez les mines et le secteur pétrolier.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Génie Géologique et Pétrolier">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/productique.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Génie Mécanique et Productique</h3>
                                    <p class="admission-card_text">Spécialisez-vous en mécanique et maintenance
                                        industrielle.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Génie Mécanique et Productique">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/vente.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Commerce et Vente</h3>
                                    <p class="admission-card_text">Développez des compétences en marketing et commerce
                                        international.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Commerce et Vente">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/gestion.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Gestion</h3>
                                    <p class="admission-card_text">Maîtrisez la finance, la comptabilité et la gestion
                                        des ressources humaines.</p>
                                    <a href="#" class="link-btn register-btn" data-filiere="Gestion">S'inscrire
                                        maintenant<i class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/hotellerie.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Tourisme et Restauration Hôtellerie</h3>
                                    <p class="admission-card_text">Formez-vous au service hôtelier et à la gestion
                                        touristique.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Tourisme et Restauration Hôtellerie">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/social.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Économie et Entrepreneuriat Social</h3>
                                    <p class="admission-card_text">Apprenez les bases de l'entrepreneuriat et des
                                        cosmétiques.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Économie et Entrepreneuriat Social">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/communication.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Information et Communication</h3>
                                    <p class="admission-card_text">Développez des compétences en journalisme et
                                        communication.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Information et Communication">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/sanitaire.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Étude Médico-Sanitaire</h3>
                                    <p class="admission-card_text">Formez-vous aux soins infirmiers et à la
                                        kinésithérapie.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Étude Médico-Sanitaire">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/biomedicales.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Sciences et Techniques Biomédicales</h3>
                                    <p class="admission-card_text">Spécialisez-vous en laboratoire et radiologie.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Sciences et Techniques Biomédicales">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/informatique.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Génie Informatique</h3>
                                    <p class="admission-card_text">Maîtrisez le développement logiciel et les réseaux.
                                    </p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Génie Informatique">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/telecommunications.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Réseaux et Télécommunications</h3>
                                    <p class="admission-card_text">Formez-vous à la gestion des réseaux et à la
                                        sécurité.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Réseaux et Télécommunications">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/biomedicales.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Sciences Biomédicales</h3>
                                    <p class="admission-card_text">Étudiez la biologie et les techniques médicales
                                        avancées.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Sciences Biomédicales">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/infirmieres.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Sciences Infirmières</h3>
                                    <p class="admission-card_text">Formez-vous aux soins de santé et à l'assistance
                                        médicale.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Sciences Infirmières">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/medicale.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Radiologie et Imagerie Médicale</h3>
                                    <p class="admission-card_text">Spécialisez-vous dans les techniques d'imagerie
                                        médicale.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Radiologie et Imagerie Médicale">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/kinesitherapie.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Kinésithérapie</h3>
                                    <p class="admission-card_text">Maîtrisez les techniques de rééducation physique.
                                    </p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Kinésithérapie">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/maieuticien.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Sage-Femme/Maïeuticien</h3>
                                    <p class="admission-card_text">Formez-vous aux soins prénatals et à l'accouchement.
                                    </p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Sage-Femme/Maïeuticien">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/developpement.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Génie Logiciel et Développement</h3>
                                    <p class="admission-card_text">Développez des solutions technologiques innovantes.
                                    </p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Génie Logiciel et Développement">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/artificielle.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Intelligence Artificielle</h3>
                                    <p class="admission-card_text">Explorez l'IA et ses applications modernes.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Intelligence Artificielle">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/reseaux.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Système d'Information et Réseaux</h3>
                                    <p class="admission-card_text">Maîtrisez la gestion des systèmes informatiques et
                                        Big Data.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Système d'Information et Réseaux">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/virologie.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Virologie Médicale</h3>
                                    <p class="admission-card_text">Étudiez les virus et leurs impacts sur la santé.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Virologie Médicale">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/reproduction.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Santé Mentale et Reproduction Médicale
                                    </h3>
                                    <p class="admission-card_text">Spécialisez-vous dans la santé mentale et la
                                        reproduction.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Santé Mentale et Reproduction Médicale">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/medicale.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Radiologie</h3>
                                    <p class="admission-card_text">Maîtrisez les techniques avancées d'imagerie
                                        médicale.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Radiologie">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/hematologie.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Hématologie Clinique</h3>
                                    <p class="admission-card_text">Étudiez les maladies du sang et leurs traitements.
                                    </p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Hématologie Clinique">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/cytopathologie.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Cytopathologie</h3>
                                    <p class="admission-card_text">Spécialisez-vous dans l'analyse cellulaire.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Cytopathologie">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/clinique.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Biologie Clinique</h3>
                                    <p class="admission-card_text">Formez-vous aux analyses biologiques avancées.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Biologie Clinique">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/bacteriologie.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Bactériologie Médicale</h3>
                                    <p class="admission-card_text">Étudiez les bactéries et leur impact médical.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Bactériologie Médicale">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/operatoire.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Assistant Bloc Opératoire</h3>
                                    <p class="admission-card_text">Assistez aux interventions chirurgicales avec
                                        expertise.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Assistant Bloc Opératoire">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/orl.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Assistant ORL</h3>
                                    <p class="admission-card_text">Spécialisez-vous dans les soins ORL
                                        (oto-rhino-laryngologie).</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Assistant ORL">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="admission-card">
                                <div class="admission-card_img"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/ophtamologie.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Assistant Ophtalmologie</h3>
                                    <p class="admission-card_text">Formez-vous aux soins des yeux et à l'optique.</p>
                                    <a href="#" class="link-btn register-btn"
                                        data-filiere="Assistant Ophtalmologie">S'inscrire maintenant<i
                                            class="fas fa-arrow-right"></i></a>
                                    <a href="#" class="th-btn style4 mt-2"
                                        style="width: 100%; background: #1a73e8; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Télécharger
                                        la brochure<i class="fas fa-download ms-2"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>





    <div class="overflow-hidden space" data-aos="fade-up" data-aos-duration="800">
        <div class="container">
            <div class="title-area text-center">
                <span class="sub-title">Vie à l'école</span>
                <h2 class="sec-title fw-semibold">Découvrez notre campus</h2>
            </div>
            <div class="row gy-4 masonary-active">
                <div class="col-md-6 col-xxl-auto filter-item" data-aos="zoom-in" data-aos-delay="100">
                    <div class="gallery-card">
                        <div class="gallery-img">
                            <img src="{{ asset('asset_vitrine/assets/img/update1/gallery/gallery_2_1.jpg') }}"
                                alt="gallery image">
                            <a href="#" class="gallery-btn popup-image"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xxl-auto filter-item" data-aos="zoom-in" data-aos-delay="200">
                    <div class="gallery-card">
                        <div class="gallery-img">
                            <img src="{{ asset('asset_vitrine/assets/img/update1/gallery/gallery_2_2.jpg') }}"
                                alt="gallery image">
                            <a href="#" class="gallery-btn popup-image"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xxl-auto filter-item" data-aos="zoom-in" data-aos-delay="300">
                    <div class="gallery-card">
                        <div class="gallery-img">
                            <img src="{{ asset('asset_vitrine/assets/img/update1/gallery/gallery_2_3.jpg') }}"
                                alt="gallery image">
                            <a href="#" class="gallery-btn popup-image"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xxl-auto filter-item" data-aos="zoom-in" data-aos-delay="400">
                    <div class="gallery-card">
                        <div class="gallery-img">
                            <img src="{{ asset('asset_vitrine/assets/img/update1/gallery/gallery_2_6.jpg') }}"
                                alt="gallery image">
                            <a href="#" class="gallery-btn popup-image"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xxl-auto filter-item" data-aos="zoom-in" data-aos-delay="500">
                    <div class="gallery-card">
                        <div class="gallery-img">
                            <img src="{{ asset('asset_vitrine/assets/img/update1/gallery/gallery_2_4.jpg') }}"
                                alt="gallery image">
                            <a href="#" class="gallery-btn popup-image"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xxl-auto filter-item" data-aos="zoom-in" data-aos-delay="600">
                    <div class="gallery-card">
                        <div class="gallery-img">
                            <img src="{{ asset('asset_vitrine/assets/img/update1/gallery/gallery_2_5.jpg') }}"
                                alt="gallery image">
                            <a href="#" class="gallery-btn popup-image"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="shape-mockup jump-reverse d-none d-sm-block" data-top="0%" data-left="0%">
            <img src="{{ asset('asset_vitrine/assets/img/update1/shape/dot_shape_5.png') }}" alt="shapes">
        </div>
        <div class="shape-mockup jump" data-bottom="10%" data-right="0%">
            <img src="{{ asset('asset_vitrine/assets/img/update1/shape/circle_8.png') }}" alt="shapes">
        </div>
        <div class="shape-mockup jump-reverse" data-bottom="6%" data-right="0%">
            <img src="{{ asset('asset_vitrine/assets/img/update1/shape/circle_9.png') }}" alt="shapes">
        </div>
    </div>
    <div class="video-area-1 overflow-hidden space">
        <div class="shape-mockup video-shape1 jump-reverse d-lg-block d-none" data-right="-35%" data-top="-40%"><img
                src="{{ asset('asset_vitrine/assets/img/normal/video-1_shape1.png') }}" alt="img"></div>
        <div class="container">
            <div class="row">
                <div class="col-lg-7 order-lg-2">
                    <div class="ms-lg-3 mb-lg-0 mb-5">
                        <div class="video-wrap mb-30"><img
                                src="{{ asset('asset_vitrine/assets/img/normal/video1.png') }}" alt="img"></div>
                        {{-- <h3 class="text-center">Video Class Interface</h3> --}}
                    </div>
                </div>
                <div class="col-lg-5 order-lg-1">
                    <div class="title-area mb-40">
                        <h2 class="sec-title">Espace et cadre propice dédié aux etudiants et encadreurs</h2>
                        <p>Nous offrons un environnement stimulant et favorable à l'apprentissage pour les étudiants et
                            leurs encadrants. Notre approche repose sur une intégration harmonieuse des ressources et
                            des méthodes pédagogiques. Voici nos engagements :</p>
                        <div class="checklist mt-35 mb-40">
                            <ul>
                                <li>Sécurité et confort. </li>
                                <li>Une réussite garantie grâce à un environnement adéquat.</li>
                                <li>Cadre convivial pour un soutien académique et social.</li>
                                <li>Un espace conçu spécifiquement pour les études, favorisant la concentration et la
                                    productivité.</li>

                            </ul>
                        </div>
                        <div class="btn-group"><a class="th-btn" href="course.html">Reserver dès maintenant <i
                                    class="fa fa-arrow-right me"></i></a></div>
                    </div>
                    <div class="student-count style2">

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="video-area-1 overflow-hidden ">
        {{-- <div class="shape-mockup video-shape1 jump-reverse d-lg-block d-none" data-right="-35%" ><img
                src="{{ asset('asset_vitrine/assets/img/normal/video-1_shape1.png') }}" alt="img"></div> --}}
        <div class="container">
            <div class="row">
                <div class="col-lg-5 order-lg-2">
                    <div class="title-area mb-40">
                        <h2 class="sec-title">Transport dédié pour les étudiants</h2>
                        <p>Nous mettons à disposition des bus et véhicules modernes pour faciliter les déplacements des
                            étudiants. Notre service de transport est conçu pour offrir commodité, sécurité et
                            ponctualité. Voici nos engagements :</p>
                        <div class="checklist mt-35 mb-40">
                            <ul>
                                <li>Véhicules confortables et sécurisés.</li>
                                <li>Horaires adaptés aux besoins académiques.</li>
                                <li>Service fiable pour une expérience sans stress.</li>
                                <li>Couverture des trajets vers les campus et lieux de stage.</li>
                            </ul>
                        </div>
                        <div class="btn-group"><a class="th-btn" href="transport.html">En savoir plus sur le
                                transport <i class="fa fa-arrow-right me"></i></a></div>
                    </div>
                    <div class="student-count style2">
                    </div>
                </div>
                <div class="col-lg-7 order-lg-1">
                    <div class="ms-lg-3 mb-lg-0 mb-5">
                        <div class="video-wrap mb-30"><img
                                src="{{ asset('asset_vitrine/assets/img/normal/video2.png') }}" alt="img"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <footer id="contact" class="footer-wrapper footer-layout9"
        data-bg-src="{{ asset('asset_vitrine/assets/img/update1/bg/footer_bg_4.png') }}" data-aos="fade-up"
        data-aos-duration="800">
        <div class="widget-area">
            <div class="container">
                <div class="row justify-content-between">
                    <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="100">
                        <div class="widget footer-widget style2">
                            <div class="th-widget-about">
                                <div class="about-logo">
                                    <a href="#"><img src="{{ asset('images/logo_white.png') }}"
                                            alt="INSTITUT SUPERIEUR LA MAJESTUEUSE la Majestueuse"></a>
                                </div>

                                <div class="th-social">
                                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fab fa-instagram"></i></a>
                                    <a href="#"><i class="fab fa-youtube"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="200">
                        <div class="widget widget_nav_menu footer-widget">
                            <h3 class="widget_title">Liens rapides</h3>
                            <div class="menu-all-pages-container">
                                <ul class="menu">
                                    <li><a href="#about">À propos de nous</a></li>
                                    <li><a href="#formations">Nos formations</a></li>

                                    <li><a href="#contact">Contactez-nous</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="300">
                        <div class="widget footer-widget">
                            <h3 class="widget_title">Programmes</h3>
                            <div class="menu-all-pages-container">
                                <ul class="menu">
                                    <li><a href="#">Mécanique</a></li>
                                    <li><a href="#">Couture</a></li>
                                    <li><a href="#">Informatique</a></li>
                                    <li><a href="#">Électricité</a></li>
                                    <li><a href="#">Plomberie</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="400">
                        <div class="widget footer-widget">
                            <h3 class="widget_title">Contactez-nous</h3>
                            <div class="th-widget-contact">
                                <div class="info-box">
                                    <div class="info-box_icon"><i class="fas fa-location-dot"></i></div>
                                    <div class="info-box_content">
                                        <p class="info-box_text">Ndazoa, près de Mbankomo, Yaoundé, Cameroun</p>
                                    </div>
                                </div>
                                <div class="info-box">
                                    <div class="info-box_icon"><i class="fas fa-phone"></i></div>
                                    <div class="info-box_content">
                                        <p class="info-box_text"><a href="tel:+237691612145">+237 691612145</a></p>
                                        <p class="info-box_text"><a href="tel:+237695830031">+237 695830031</a></p>
                                    </div>
                                </div>
                                <div class="info-box">
                                    <div class="info-box_icon"><i class="fas fa-envelope"></i></div>
                                    <div class="info-box_content">
                                        <p class="info-box_text"><a
                                                href="mailto:info@ecoledemetiersndazoa.com">info@ecoledemetiersndazoa.com</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="copyright">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="copyright-text">© 2025 <a href="#">INSTITUT SUPERIEUR LA MAJESTUEUSE</a>. Tous
                            droits réservés.</p>
                    </div>
                    <div class="col-md-6">
                        <div class="footer-links">
                            <ul>
                                <li><a href="#">Politique de confidentialité</a></li>
                                <li><a href="#">Conditions d'utilisation</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>


    <!-- JS Vendor -->
    <script src="{{ asset('asset_vitrine/assets/js/vendor/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('asset_vitrine/assets/js/vendor/bootstrap.min.js') }}"></script>
    <script src="{{ asset('asset_vitrine/assets/js/vendor/slick.min.js') }}"></script>
    <script src="{{ asset('asset_vitrine/assets/js/vendor/imagesloaded.pkgd.min.js') }}"></script>
    <script src="{{ asset('asset_vitrine/assets/js/vendor/waypoints.min.js') }}"></script>
    <script src="{{ asset('asset_vitrine/assets/js/vendor/jquery.counterup.min.js') }}"></script>
    <script src="{{ asset('asset_vitrine/assets/js/vendor/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('asset_vitrine/assets/js/vendor/isotope.pkgd.min.js') }}"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js')}}"></script>

    <!-- JS Core -->
    <script src="{{ asset('asset_vitrine/assets/js/main.js') }}"></script>

    <!-- JavaScript pour le modal d'inscription -->
    <script>
        // Gestion de l'ouverture du modal
        document.querySelectorAll('.register-btn').forEach(button => {
            button.addEventListener('click', function() {
                const filiere = this.getAttribute('data-filiere');
                document.getElementById('filiere').value = filiere;
                document.getElementById('registerModal').style.display = 'flex';
            });
        });

        // Fermeture du modal
        document.querySelector('.close').addEventListener('click', function() {
            document.getElementById('registerModal').style.display = 'none';
            document.getElementById('formMessage').style.display = 'none';
            document.getElementById('registerForm').reset();
        });

        // Fermeture du modal en cliquant en dehors
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('registerModal');
            if (event.target === modal) {
                modal.style.display = 'none';
                document.getElementById('formMessage').style.display = 'none';
                document.getElementById('registerForm').reset();
            }
        });

        // Soumission du formulaire
        document.getElementById('registerForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            fetch('/submit-registration', { // Remplacez par votre endpoint backend
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('formMessage').textContent = 'Inscription réussie !';
                        document.getElementById('formMessage').style.color = 'green';
                        document.getElementById('formMessage').style.display = 'block';
                        setTimeout(() => {
                            document.getElementById('registerModal').style.display = 'none';
                            document.getElementById('formMessage').style.display = 'none';
                            document.getElementById('registerForm').reset();
                        }, 2000);
                    } else {
                        document.getElementById('formMessage').textContent = data.message ||
                            'Erreur lors de l\'inscription.';
                        document.getElementById('formMessage').style.display = 'block';
                    }
                })
                .catch(error => {
                    document.getElementById('formMessage').textContent =
                        'Une erreur s\'est produite. Veuillez réessayer.';
                    document.getElementById('formMessage').style.display = 'block';
                });
        });

        // Initialisation AOS
        AOS.init({
            duration: 1000,
            once: true
        });



        window.addEventListener('load', function() {
            const preloader = document.getElementById('magnificentPreloader');
            preloader.classList.add('preloader-fadeout');
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 1000); // Temps correspondant à la durée de l'animation fadeOut
        });
    </script>
</body>

</html>
