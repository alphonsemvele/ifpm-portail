<!DOCTYPE html>
<html class="no-js" lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Institut Supérieur La Majestueuse de Ndazoa</title>
    <meta name="author" content="themeholy">
    <meta name="description" content="L'Institut Supérieur La Majestueuse de NDAZOA">
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

        .whatsapp-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #25D366;
            color: #fff;
            border-radius: 50px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        /* Bouton Facebook Messenger */
        .facebook-btn {
            position: fixed;
            bottom: 80px;
            /* Décalé vers le haut pour éviter le chevauchement avec WhatsApp */
            right: 20px;
            background-color: #0084FF;
            color: #fff;
            border-radius: 50px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .whatsapp-btn i,
        .facebook-btn i {
            font-size: 24px;
        }

        .whatsapp-btn:hover,
        .facebook-btn:hover {
            transform: scale(1.1);
        }

        .whatsapp-btn:hover {
            background-color: #20b354;
        }

        .facebook-btn:hover {
            background-color: #0066CC;
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
    <!-- Nouveau Preloader Magnifique -->
    <div class="magnificent-preloader" id="magnificentPreloader">
        <div class="preloader-particles" id="particles"></div>

        <div class="preloader-logo-container">
            <div class="preloader-logo">
                <img src="images/logo.png" alt="INSTITUT SUPERIEUR LA MAJESTUEUSE">
            </div>
        </div>

        <div class="preloader-text">
            Institut Supérieur La Majestueuse
        </div>

        <div class="preloader-progress">
            <div class="preloader-progress-bar"></div>
        </div>

        <div class="preloader-text" style="font-size: 14px; opacity: 0.8;">
            Chargement en cours...
        </div>

        <div class="preloader-spinner"></div>
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
                    <li><a href="#actualites">Actualités</a></li>
                    <li><a href="/ifpm">IFPM</a></li>
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
                                        </b>info@ism-ndazoa.com</li>
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
                                                <li><a href="#actualites">Actualités</a></li>
                                                <li><a href="/ifpm">IFPM</a></li>
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

    <a href="https://wa.me/+237655341939?text=Bonjour%2C%20je%20souhaite%20avoir%20plus%20d%27informations%20sur%20les%20formations%20de%20l%27Institut%20Supérieur%20La%20Majestueuse."
        class="whatsapp-btn" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>
    <a href="https://www.facebook.com/profile.php?id=61577186635321" class="facebook-btn" target="_blank">
        <i class="fab fa-facebook-messenger"></i>
    </a>
    <div class="th-hero-wrapper hero-15 th-carousel" data-slide-show="1" data-md-slide-show="1" data-fade="true"
        id="hero">
        <div class="th-hero-slide" data-aos="fade-up" data-aos-duration="1000">
            <div class="th-hero-bg" data-bg-src="{{ asset('asset_vitrine/assets/img/update1/hero/hero_bg_10_1.jpg') }}">
            </div>
            <div class="container">
                <div class="hero-style15">
                    <h1 class="hero-title" data-aos="slide-down" data-aos-delay="300">Votre avenir commence ici</h1>
                    <p class="hero-text" data-aos="fade-up" data-aos-delay="400"
                        style="max-width: 700px; margin: 0 auto 30px;">
                        Dans quelques jours, ce sera la rentrée académique à l'Institut Supérieur La Majestueuse ! Notre
                        équipe est mobilisée : techniciens de maintenance, chefs de département, coordonnateurs,
                        sécurité et médecins sont à pied d'œuvre pour accueillir notre première cuvée d'étudiants.
                        Inscriptions, chambres équipées de la cité universitaire... Tout est prêt pour votre réussite !
                    </p>
                    <div class="btn-group" data-aos="slide-up" data-aos-delay="300">
                        <a href="{{ route('preinscription.index') }}" class="th-btn">Rejoignez-nous<i
                                class="fas fa-long-arrow-right ms-2"></i></a>
                        <a href="#formations" class="th-btn style4">Nos programmes de formations<i
                                class="fas fa-long-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <section class="bg-smoke-half mt-3" data-aos="fade-up" data-aos-duration="800">
        <div class="container">
            <div class="row">
                <div class="col-xl-6 mb-30 mb-xl-0" data-aos="fade-right" data-aos-delay="100">
                    <div class="cta-box" data-bg-src="{{ asset('asset_vitrine/assets/img/update1/bg/cta_bg_5.jpg') }}">
                        <h3 class="cta-title">Inscrivez-vous pour 2025</h3>
                        <p class="cta-text">Rejoignez nos programmes de formation pour acquérir des compétences
                            pratiques et démarrer une carrière prometteuse. Nos cours sont conçus pour répondre aux
                            besoins du marché local.</p>
                        <a href="{{ route('preinscription.index') }}" class="th-btn">S'inscrire maintenant<i
                                class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
                <div class="col-xl-6" data-aos="fade-left" data-aos-delay="100">
                    <div class="cta-box" data-bg-src="{{ asset('asset_vitrine/assets/img/update1/bg/cta_bg_6.jpg') }}">
                        <h3 class="cta-title">Demandez une bourse</h3>
                        <p class="cta-text">Nous offrons des bourses pour soutenir les étudiants talentueux. Postulez
                            dès maintenant pour bénéficier d'une aide financière.</p>
                        <a href="#" class="th-btn">Postuler maintenant<i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

     <section class="admission-info" id="admission">
        <div class="admission-container">
            <h2 style="text-align: center; font-size: 2.5rem; margin-bottom: 2rem;">Informations d'Admission</h2>
            
            <div class="admission-intro">
                <p><strong>Chers Étudiants, apprenants et professionnels,</strong></p>
                <p>Vous avez déjà un cadre exceptionnel pour vos études, un campus High Tech, une équipe d'enseignants qualifiés, des laboratoires très bien équipés.</p>
            </div>

            <div class="admission-grid">
                <div class="admission-card">
                    <h3><i class="fas fa-graduation-cap"></i> Conditions d'admission</h3>
                    <p><strong>1ère année BTS/HND :</strong> BAC, GCE ou tout autre diplôme équivalent</p>
                    <p><strong>Licence & Master :</strong> Diplômes requis selon le niveau</p>
                </div>

                <div class="admission-card">
                    <h3><i class="fas fa-file-invoice-dollar"></i> Frais d'inscription</h3>
                    <p><strong>50.000 FCFA</strong></p>
                    <p>Frais uniques lors de l'inscription</p>
                </div>

                <div class="admission-card">
                    <h3><i class="fas fa-book"></i> Scolarité complète</h3>
                    <p><strong>1.000.000 FCFA</strong></p>
                    <p>Payable en 3 tranches</p>
                    <p style="font-size: 0.95rem; margin-top: 1rem;">Inclut : Scolarité, transport, assurances, tenues scolaires, kit matériel pour chaque étudiant</p>
                </div>

                <div class="admission-card">
                    <h3><i class="fas fa-building"></i> Logement - Cité Universitaire</h3>
                    <p><strong>50.000 FCFA/mois</strong></p>
                    <p>10 mois d'avance + 2 mois de caution remboursables</p>
                </div>
            </div>
        </div>
    </section>

    <section class="space bg-smoke" id="formations" data-aos="fade-up" data-aos-duration="800">
        <div class="container">
            <div class="title-area text-center">
                <span class="sub-title">Nos programmes</span>
                <h2 class="sec-title fw-semibold">Excellence dans la formation professionnelle</h2>
            </div>
            <div class="row gy-4">
                <!-- Cycle BTS -->
                <div class="col-12" data-aos="fade-up" data-aos-delay="100">
                    <h3 class="sec-title text-center mb-30">Cycle BTS</h3>
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
                                    <a href="#" class="link-btn register-btn" data-filiere="Génie Électrique">S'inscrire
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
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/civil.jpg') }}"
                                        alt="image"></div>
                                <div class="admission-card_content">
                                    <h3 class="admission-card_title box-title">Génie Civil</h3>
                                    <p class="admission-card_text">Maîtrisez la construction et les travaux publics.
                                    </p>
                                    <a href="#" class="link-btn register-btn" data-filiere="Génie Civil">S'inscrire
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
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/admission_1_3.jpg') }}"
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
                    </div>
                </div>
                <!-- Cycle Licence -->
                <div class="col-12" data-aos="fade-up" data-aos-delay="200">
                    <h3 class="sec-title text-center mb-30">Cycle Licence</h3>
                    <div class="row gy-4" style="display: flex; flex-wrap: wrap; justify-content: space-between;">
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
                                    <a href="#" class="link-btn register-btn" data-filiere="Kinésithérapie">S'inscrire
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
                    </div>
                </div>
                <!-- Cycle Masters -->
                <div class="col-12 mt-5" data-aos="fade-up" data-aos-delay="300">
                    <h3 class="sec-title text-center mb-30">Cycle Masters</h3>
                    <div class="row gy-4" style="display: flex; flex-wrap: wrap; justify-content: space-between;">
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
                                    <a href="#" class="link-btn register-btn" data-filiere="Radiologie">S'inscrire
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
                                        src="{{ asset('asset_vitrine/assets/img/update1/normal/clinique.jpg') }}"
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
                                    <a href="#" class="link-btn register-btn" data-filiere="Cytopathologie">S'inscrire
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
                                    <a href="#" class="link-btn register-btn" data-filiere="Assistant ORL">S'inscrire
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




    <!-- Modal pour l'inscription -->
    <div id="registerModal" class="modal">
        <div class="modal-content">
            <span class="close">×</span>
            <h3>Inscription à une formation</h3>
            <form id="registerForm">
                <div class="form-group">
                    <label for="filiere">Filiere</label>
                    <input type="text" id="filiere" name="filiere" readonly>
                </div>
                <div class="form-group">
                    <div>
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" class="" placeholder="Votre nom" required>
                    </div>
                    <div>
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" class="" placeholder="Votre prénom" required>
                    </div>
                </div>
                <div class="form-group">
                    <div>
                        <label for="contact">Contact</label>
                        <input type="tel" id="contact" name="contact" class="" placeholder="Votre numéro" required
                            pattern="[0-9]{10}">
                    </div>
                    <div>
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email" class="" placeholder="Votre email" required>
                    </div>
                </div>
                <div class="form-group">
                    <div>
                        <label for="pays">Pays</label>
                        <input type="text" id="pays" name="pays" class="" placeholder="Votre pays" required>
                    </div>
                    <div>
                        <label for="typeFormation">Type de formation</label>
                        <select id="typeFormation" name="typeFormation" class="" required>
                            <option value="" disabled selected>Choisissez une option</option>
                            <option value="en_ligne">En ligne</option>
                            <option value="presentiel">Présentiel</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="th-btn">Soumettre<i class="fas fa-long-arrow-right ms-2"></i></button>
            </form>
            <p id="formMessage" style="color: red; display: none; text-align: center; margin-top: 10px;"></p>
        </div>
    </div>

    <section class="space" data-bg-src="{{ asset('asset_vitrine/assets/img/update1/bg/testi_bg_5.jpg') }}"
        data-overlay="title" data-opacity="9" data-aos="fade-up" data-aos-duration="800">
        <div class="container z-index-3">
            <div class="row justify-content-between align-items-end">
                <div class="col-md-auto">
                    <div class="title-area text-center text-md-start">
                        <span class="sub-title">Ce que disent nos futurs étudiants</span>
                        <h2 class="sec-title fw-medium text-white">Témoignages </h2>
                    </div>
                </div>
                <div class="col-auto d-none d-md-block">
                    <div class="sec-btn">
                        <div class="icon-box">
                            <button data-slick-prev="#testiSlide5" class="slick-arrow default"><i
                                    class="far fa-arrow-left"></i></button>
                            <button data-slick-next="#testiSlide5" class="slick-arrow default"><i
                                    class="far fa-arrow-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row th-carousel" id="testiSlide5" data-slide-show="2" data-md-slide-show="1">
                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="testi-list">
                        <div class="testi-list_img">
                            <img src="{{ asset('asset_vitrine/assets/img/update1/testimonial/testi_2_1.jpg') }}"
                                alt="Avater">
                            <div class="testi-list_quote"><img
                                    src="{{ asset('asset_vitrine/assets/img/update1/icon/quote_left.svg') }}"
                                    alt="icon"></div>
                        </div>
                        <div class="testi-list_content">
                            <p class="testi-list_text">J'espère que la formation en mécanique me permettra d'ouvrir mon
                                propre garage une fois l'école créée. Les cours pratiques semblent prometteurs !</p>
                            <h3 class="testi-list_name box-title">Emmanuel Ndongo</h3>
                            <span class="testi-list_desig">Futur étudiant en mécanique</span>
                            <div class="testi-list_review">
                                <i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i
                                    class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i
                                    class="fa-solid fa-star-sharp"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="testi-list">
                        <div class="testi-list_img">
                            <img src="{{ asset('asset_vitrine/assets/img/update1/testimonial/testi_2_2.jpg') }}"
                                alt="Avater">
                            <div class="testi-list_quote"><img
                                    src="{{ asset('asset_vitrine/assets/img/update1/icon/quote_left.svg') }}"
                                    alt="icon"></div>
                        </div>
                        <div class="testi-list_content">
                            <p class="testi-list_text">Je suis enthousiaste à l'idée de lancer ma marque de vêtements
                                grâce à une future formation en couture. L'approche bilingue me motive !</p>
                            <h3 class="testi-list_name box-title">Clara Essomba</h3>
                            <span class="testi-list_desig">Futur étudiant en couture</span>
                            <div class="testi-list_review">
                                <i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i
                                    class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i
                                    class="fa-solid fa-star-sharp"></i>
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
        {{-- <div class="shape-mockup video-shape1 jump-reverse d-lg-block d-none" data-right="-35%"><img
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
                                src="{{ asset('asset_vitrine/assets/img/normal/video2.png') }}" alt="img">
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>














    <section class="" data-bg-src="{{ asset('asset_vitrine/assets/img/update1/bg/cta_bg_7.jpg') }}" data-aos="fade-up"
        data-aos-duration="800">
        <div class="container">
            <div class="row flex-row-reverse align-items-center">
                <div class="col-xl-6" data-aos="fade-left" data-aos-delay="200">
                    <div class="ps-xxl-5 ms-xl-2 pt-5 pt-xl-0 mt-4 mt-xl-0 mb-n5 mb-xl-0">
                        <div class="text-center">
                            <img src="{{ asset('asset_vitrine/assets/img/update1/normal/vector_4.png') }}" alt="vector">
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 space text-center text-xl-start" data-aos="fade-right" data-aos-delay="200">
                    <div class="title-area mb-35">
                        <span class="sub-title">Bourses ouvertes</span>
                        <h2 class="sec-title fw-medium text-white">Inscriptions ouvertes pour 2025<br>pour des
                            formations professionnelles</h2>
                    </div>
                    <p class="mt-n2 mb-35 text-light">Rejoignez notre école pour acquérir des compétences pratiques et
                        démarrer une carrière réussie. Nos programmes sont conçus pour répondre aux besoins du marché
                        local.</p>
                    <a href="#" class="th-btn style3 shadow-none">S'inscrire maintenant<i
                            class="fas fa-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
    </section>

    <section class="space" id="actualites" data-aos="fade-up" data-aos-duration="800">
        <div class="container">
            <div class="title-area text-center">
                <span class="sub-title">Nos articles</span>
                <h2 class="sec-title fw-semibold">Actualités ISM</h2>
            </div>
            <div class="row slider-shadow th-carousel" data-slide-show="2" data-lg-slide-show="2" data-md-slide-show="1"
                data-sm-slide-show="1">
                @forelse($articles as $article)
                    <div class="col-md-6 col-xl-6" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 + 100 }}">
                        <div class="blog-recent">
                            <div class="blog-img">
                                @if($article->image)
                                    <img src="{{ asset('storage/' . $article->image) }}" alt="{{ $article->title }}">
                                @else
                                    <img src="{{ asset('images/placeholder.jpg') }}" alt="Image par défaut">
                                @endif
                            </div>
                            <div class="blog-content">
                                <div class="blog-meta style2">
                                    <a href="#"><i class="far fa-clock"></i>
                                        {{ \Carbon\Carbon::parse($article->created_at)->format('d M Y') }}</a>
                                </div>
                                <h3 class="blog-title">{{ $article->title }}</h3>
                                <a href="{{ route('articles.show', $article->id) }}" class="th-btn style4">Lire les
                                    détails<i class="fas fa-arrow-right ms-2"></i></a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center">
                        <p class="text-gray-600">Aucun article disponible pour le moment.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>





    <div class="space-bottom" id="contact" data-aos="fade-up" data-aos-duration="800">
        <div class="container">
            <div class="row">
                <div class="col-xl-5 mb-30 mb-xl-0" data-aos="fade-right" data-aos-delay="200">
                    <div class="me-xxl-5 mt-60">
                        <div class="title-area mb-25">
                            <h2 class="border-title h3">Une question ?</h2>
                        </div>
                        <p class="mt-n2 mb-25">Vous avez une question ou une suggestion ? Remplissez le formulaire
                            ci-dessous pour contacter notre équipe.</p>
                        <div class="contact-feature">
                            <div class="contact-feature-icon"><i class="fal fa-location-dot"></i></div>
                            <div class="media-body">
                                <p class="contact-feature_label">Notre adresse</p>
                                <span class="contact-feature_link">Ndazoa, près de Mbankomo, Yaoundé, Cameroun.
                                    Ndazoa est un quartier périphérique de Yaoundé. Bien que les informations précises
                                    sur la distance ne soient pas disponibles, on peut estimer que la distance entre la
                                    Poste Centrale et Ndazoa est d’environ 10 à 15 km, en fonction de l’itinéraire
                                    emprunté. Le temps de trajet en voiture peut varier entre 20 et 40 minutes, selon
                                    les conditions de circulation.
                                </span>
                            </div>
                        </div>
                        <div class="contact-feature">
                            <div class="contact-feature-icon"><i class="fal fa-phone"></i></div>
                            <div class="media-body">
                                <p class="contact-feature_label">Numéro de téléphone</p>
                                <span class="contact-feature_link">Mobile : <span>+237 691612145 </span></span>
                                <span class="contact-feature_link">Fixe : <span>+237 695830031</span></span>
                            </div>
                        </div>
                        <div class="contact-feature">
                            <div class="contact-feature-icon"><i class="fal fa-clock"></i></div>
                            <div class="media-body">
                                <p class="contact-feature_label">Horaires d'ouverture</p>
                                <span class="contact-feature_link">Lundi - Vendredi : 08:00 - 17:00</span>
                                <span class="contact-feature_link">Samedi : 09:00 - 13:00</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-7" data-aos="fade-left" data-aos-delay="200">
                    <div class="contact-form-wrap"
                        data-bg-src="{{ asset('asset_vitrine/assets/img/bg/contact_bg_1.png') }}">
                        <span class="sub-title">Contactez-nous !</span>
                        <h2 class="border-title">Prenez contact</h2>
                        <p class="mt-n1 mb-30 sec-text">Nous sommes là pour répondre à toutes vos questions.
                            Remplissez
                            le formulaire pour nous contacter.</p>
                        <form>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control style-white" name="name" id="name"
                                            placeholder="Votre nom*">
                                        <i class="fal fa-user"></i>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="email" class="form-control style-white" name="email" id="email"
                                            placeholder="Adresse e-mail*">
                                        <i class="fal fa-envelope"></i>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <select name="subject" id="subject"
                                            class="single-select nice-select form-select style-white">
                                            <option value="" disabled selected hidden>Sélectionnez le sujet*
                                            </option>
                                            <option value="Mécanique">Mécanique</option>
                                            <option value="Couture">Couture</option>
                                            <option value="Informatique">Informatique</option>
                                            <option value="Inscription">Inscription</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="tel" class="form-control style-white" name="number" id="number"
                                            placeholder="Numéro de téléphone*">
                                        <i class="fal fa-phone"></i>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <textarea name="message" id="message" cols="30" rows="3"
                                            class="form-control style-white"
                                            placeholder="Écrivez votre message*"></textarea>
                                        <i class="fal fa-pen"></i>
                                    </div>
                                </div>
                                <div class="form-btn col-12 mt-10">
                                    <button class="th-btn">Envoyer le message<i
                                            class="fas fa-long-arrow-right ms-2"></i></button>
                                </div>
                            </div>
                            <p class="form-messages mb-0 mt-3"></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div data-pos-for=".footer-wrapper" data-sec-pos="bottom-half" data-aos="fade-up" data-aos-duration="800">
        <div class="container">
            <div class="susbcribe-bg"
                data-bg-src="{{ asset('asset_vitrine/assets/img/update1/bg/subscribe_bg_1.jpg') }}">
                <div class="newsletter-overlay">
                    <div class="col-xl-8">
                        <h3 class="text-white fw-semibold mt-n2 mb-20">Inscrivez-vous à notre newsletter</h3>
                        <form action="#" class="newsletter-form style2">
                            <div class="form-group">
                                <input type="text" class="form-control style2" placeholder="Entrez votre e-mail">
                                <i class="fa-thin fa-envelope"></i>
                            </div>
                            <button type="submit" class="th-btn">S'abonner maintenant<i
                                    class="fas fa-arrow-right ms-2"></i></button>
                        </form>
                    </div>
                    <div class="col-xl-4">
                        <div class="ps-xl-5">
                            <h3 class="text-white fw-semibold mt-n2 mb-20">Restez en contact !</h3>
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
            </div>
        </div>
    </div>
    <footer class="footer-wrapper footer-layout9"
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
                                    <li><a href="#">À propos de nous</a></li>
                                    <li><a href="#">Nos formations</a></li>
                                    <li><a href="#">Nos formateurs</a></li>
                                    <li><a href="#">Blog</a></li>
                                    <li><a href="#">Contactez-nous</a></li>
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
                                                href="mailto:info@ecoledemetiersndazoa.com">info@ism-ndazoa.com</a>
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
                        <p class="copyright-text">© 2025 <a href="#">INSTITUT SUPERIEUR LA MAJESTUEUSE</a>.
                            Tous droits réservés.</p>
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

    <!-- JS -->
    <script src="{{ asset('asset_vitrine/assets/js/vendor/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('asset_vitrine/assets/js/app.min.js') }}"></script>
    <script src="{{ asset('asset_vitrine/assets/js/main.js') }}"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
        // Gestion du modal d'inscription
        document.querySelectorAll('.register-btn').forEach(button => {
            button.addEventListener('click', function () {
                const filiere = this.getAttribute('data-filiere');
                document.getElementById('filiere').value = filiere;
                document.getElementById('registerModal').style.display = 'flex';
            });
        });

        document.querySelector('.close').addEventListener('click', function () {
            document.getElementById('registerModal').style.display = 'none';
        });

        window.addEventListener('click', function (event) {
            const modal = document.getElementById('registerModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });



        window.addEventListener('load', function () {
            const preloader = document.getElementById('magnificentPreloader');
            preloader.classList.add('preloader-fadeout');
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 1000); // Temps correspondant à la durée de l'animation fadeOut
        });
    </script>
</body>

</html>