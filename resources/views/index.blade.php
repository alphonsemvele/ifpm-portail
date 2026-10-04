<!DOCTYPE html>
<html class="no-js" lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Institut Superieur La Majestueuse</title>
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
                    <li><a href="#hero">Accueil</a></li>
                    <li><a href="#about-sec">À propos</a></li>
                    <li><a href="#course-sec">Formations</a></li>
                    <li><a href="#blog-sec">Actualités</a></li>
                    <li><a href="#contact-sec">Contactez-nous</a></li>
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
                                                <li><a href="#hero">Accueil</a></li>
                                                <li><a href="#about-sec">À propos</a></li>
                                                <li><a href="#course-sec">Formations</a></li>
                                                <li><a href="#blog-sec">Actualités</a></li>
                                                <li><a href="#contact-sec">Contactez-nous</a></li>
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
        <div class="th-hero-slide" data-aos="fade-up" data-aos-duration="1000">
            <div class="th-hero-bg"
                data-bg-src="{{ asset('asset_vitrine/assets/img/update1/hero/hero_bg_10_1.jpg') }}">
            </div>
            <div class="container  ">

                <div class="row flex justify-content-center" style="padding: 40px 0; gap: 30px;">
                    <div class="hero-style15 col-xl-5 col-md-6"
                        style="padding: 20px; display: flex; flex-direction: column; align-items: center; text-align: center;">

                        <h1 class="hero-title" data-aos="slide-down" data-aos-delay="300"
                            style="margin-bottom: 20px; font-size: 2rem; font-weight: bold;">INSTITUT SUPERIEUR LA
                            MAJESTUEUSE(ISM)</h1>
                        <p class="hero-text" data-aos="slide-up" data-aos-delay="100"
                            style="margin-bottom: 30px; font-size: 1rem; line-height: 1.6;">Rejoignez notre école pour
                            acquérir des compétences pratiques dans divers métiers.</p>
                        <div class="btn-group" data-aos="slide-up" data-aos-delay="300"
                            style="display: flex; gap: 15px;">
                            <a href="{{route('ism')}}" class="th-btn"
                                style="padding: 12px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">ISM<i
                                    class="fas fa-long-arrow-right ms-2"></i></a>
                        </div>
                    </div>
                      {{--  <div class="hero-style15 col-xl-5 col-md-6"
                        style="padding: 20px; display: flex; flex-direction: column; align-items: center; text-align: center;">

                        <h1 class="hero-title" data-aos="slide-down" data-aos-delay="300"
                            style="margin-bottom: 20px; font-size: 2rem; font-weight: bold;">INSTITUT DE FORMATION
                            PROFESSIONNELLE(IFPM)</h1>
                        <p class="hero-text" data-aos="slide-up" data-aos-delay="100"
                            style="margin-bottom: 30px; font-size: 1rem; line-height: 1.6;">Nos programmes bilingues
                            préparent les étudiants à exceller dans un monde globalisé.</p>
                        <div class="btn-group" data-aos="slide-up" data-aos-delay="300"
                            style="display: flex; gap: 15px;">
                            <a href="{{route('ifpm')}}" class="th-btn style4"
                                style="padding: 12px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;">IFPM<i
                                    class="fas fa-long-arrow-right ms-2"></i></a>
                        </div>
                    </div> --}}



                </div>



            </div>
        </div>
        {{-- <div class="th-hero-slide" data-aos="fade-up" data-aos-duration="1000">
            <div class="th-hero-bg" data-bg-src="{{asset('asset_vitrine/assets/img/update1/hero/hero_bg_10_2.jpg')}}">
            </div>
            <div class="container">
                <div class="hero-style15">
                    <span class="hero-name" data-aos="slide-down" data-aos-delay="500">Début des cours en Septembre</span>
                    <h1 class="hero-title" data-aos="slide-down" data-aos-delay="300">Formation d'excellence</h1>
                    <p class="hero-text" data-aos="slide-up" data-aos-delay="100">Nos formations pratiques et bilingues vous préparent à des carrières réussies dans les métiers techniques et artisanaux.</p>
                    <div class="btn-group" data-aos="slide-up" data-aos-delay="300">
                         <a href="#" class="th-btn">ISM(Institut Supérieur la Majestueuse)<i class="fas fa-long-arrow-right ms-2"></i></a>
                        <a href="#" class="th-btn style4">IFPM (Institut de Formation professionnelle la Majestueuse<i class="fas fa-long-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
        </div> --}}
        {{-- <div class="th-hero-slide" data-aos="fade-up" data-aos-duration="1000">
            <div class="th-hero-bg" data-bg-src="{{asset('asset_vitrine/assets/img/update1/hero/hero_bg_10_3.jpg')}}">
            </div>
            <div class="container">
                <div class="hero-style15">
                    <span class="hero-name" data-aos="slide-down" data-aos-delay="500">Cours débutant en Septembre</span>
                    <h1 class="hero-title" data-aos="slide-down" data-aos-delay="300">Votre avenir commence ici</h1>
                    <p class="hero-text" data-aos="slide-up" data-aos-delay="100">À l'institut, nous formons des professionnels compétents dans un cadre stimulant et bilingue.</p>
                    <div class="btn-group" data-aos="slide-up" data-aos-delay="300">
                         <a href="#" class="th-btn">ISM(Institut Supérieur la Majestueuse)<i class="fas fa-long-arrow-right ms-2"></i></a>
                        <a href="#" class="th-btn style4">IFPM (Institut de Formation professionnelle la Majestueuse<i class="fas fa-long-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
    <section class="space-top feature-sec" data-aos="fade-up" data-aos-duration="800">
        <div class="container">
            <div class="row gy-4 justify-content-center">
                <div class="col-xl-3 col-md-6" data-aos="zoom-in" data-aos-delay="100">
                    <div class="feature-list">
                        <div class="feature-list_icon"><i class="fal fa-tools"></i></div>
                        <h3 class="feature-list_title">Métiers techniques</h3>
                        <p class="feature-list_text">Apprenez des compétences pratiques en mécanique, électricité et
                            plus encore, avec des formateurs expérimentés.</p>
                        <a href="#" class="icon-btn"><i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6" data-aos="zoom-in" data-aos-delay="200">
                    <div class="feature-list">
                        <div class="feature-list_icon"><i class="fal fa-award"></i></div>
                        <h3 class="feature-list_title">Bourses</h3>
                        <p class="feature-list_text">Bénéficiez de bourses pour soutenir votre formation et accéder à
                            une éducation de qualité.</p>
                        <a href="#" class="icon-btn"><i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6" data-aos="zoom-in" data-aos-delay="300">
                    <div class="feature-list">
                        <div class="feature-list_icon"><i class="fal fa-user-check"></i></div>
                        <h3 class="feature-list_title">Inscription</h3>
                        <p class="feature-list_text">Un processus d'inscription simple pour rejoindre nos programmes
                            bilingues et pratiques.</p>
                        <a href="#" class="icon-btn"><i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6" data-aos="zoom-in" data-aos-delay="400">
                    <div class="feature-list">
                        <div class="feature-list_icon"><i class="fal fa-briefcase"></i></div>
                        <h3 class="feature-list_title">Formations professionnelles</h3>
                        <p class="feature-list_text">Découvrez nos programmes variés, de la couture à l'informatique,
                            pour une carrière réussie.</p>
                        <a href="#" class="icon-btn"><i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-smoke-half mt-5" data-aos="fade-up" data-aos-duration="800">
        <div class="container">
            <div class="row">
                <div class="col-xl-6 mb-30 mb-xl-0" data-aos="fade-right" data-aos-delay="100">
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
                <div class="col-xl-6" data-aos="fade-left" data-aos-delay="100">
                    <div class="cta-box"
                        data-bg-src="{{ asset('asset_vitrine/assets/img/update1/bg/cta_bg_6.jpg') }}">
                        <h3 class="cta-title">Demandez une bourse</h3>
                        <p class="cta-text">Nous offrons des bourses pour soutenir les étudiants talentueux. Postulez
                            dès maintenant pour bénéficier d'une aide financière.</p>
                        <a href="#" class="th-btn">Postuler maintenant<i
                                class="fas fa-arrow-right ms-2"></i></a>
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
                        <input type="text" id="nom" name="nom" class="" placeholder="Votre nom"
                            required>
                    </div>
                    <div>
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" class=""
                            placeholder="Votre prénom" required>
                    </div>
                </div>
                <div class="form-group">
                    <div>
                        <label for="contact">Contact</label>
                        <input type="tel" id="contact" name="contact" class=""
                            placeholder="Votre numéro" required pattern="[0-9]{10}">
                    </div>
                    <div>
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email" class=""
                            placeholder="Votre email" required>
                    </div>
                </div>
                <div class="form-group">
                    <div>
                        <label for="pays">Pays</label>
                        <input type="text" id="pays" name="pays" class="" placeholder="Votre pays"
                            required>
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

    <div class="space" data-bg-src="{{ asset('asset_vitrine/assets/img/update1/bg/why_bg_2.png') }}"
        data-aos="fade-up" data-aos-duration="800">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xl-6 mb-45 mb-xl-0 text-center text-xl-start" data-aos="fade-right"
                    data-aos-delay="200">
                    <div class="title-area mb-35">
                        <span class="sub-title">Nos installations</span>
                        <h2 class="sec-title fw-semibold">Transformez votre avenir<br>avec une formation pratique</h2>
                    </div>
                    <div class="row gy-4 mb-5">
                        <div class="col-md-6" data-aos="fade-up" data-aos-delay="100">
                            <div class="feature-card style2">
                                <div class="feature-card_icon"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/icon/feature_1_5.png') }}"
                                        alt="icon"></div>
                                <h3 class="feature-card_title">Certificat reconnu</h3>
                                <p class="feature-card_text">Nos certificats sont reconnus localement et vous ouvrent
                                    des portes vers des opportunités professionnelles.</p>
                            </div>
                        </div>
                        <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
                            <div class="feature-card style2">
                                <div class="feature-card_icon"><img
                                        src="{{ asset('asset_vitrine/assets/img/update1/icon/feature_1_6.png') }}"
                                        alt="icon"></div>
                                <h3 class="feature-card_title">Support éducatif</h3>
                                <p class="feature-card_text">Bénéficiez d'un accompagnement personnalisé par nos
                                    formateurs pour réussir votre parcours.</p>
                            </div>
                        </div>
                    </div>
                    <a href="#" class="th-btn">En savoir plus<i class="fas fa-arrow-right ms-2"></i></a>
                </div>
                <div class="col-xl-6" data-aos="fade-left" data-aos-delay="200">
                    <div class="ps-xxl-5 ms-xl-2">
                        <div class="video-box2">
                            <img src="{{ asset('asset_vitrine/assets/img/update1/normal/video_4.jpg') }}"
                                alt="video">
                            <a href="#" class="play-btn style3 popup-video"><i class="fas fa-play"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section class="space" data-bg-src="{{ asset('asset_vitrine/assets/img/update1/bg/testi_bg_5.jpg') }}"
        data-overlay="title" data-opacity="9" data-aos="fade-up" data-aos-duration="800">
        <div class="container z-index-3">
            <div class="row justify-content-between align-items-end">
                <div class="col-md-auto">
                    <div class="title-area text-center text-md-start">
                        <span class="sub-title">Ce que disent nos étudiants</span>
                        <h2 class="sec-title fw-medium text-white">Témoignages des étudiants</h2>
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
                            <p class="testi-list_text">Grâce à la formation en mécanique, j'ai pu ouvrir mon propre
                                garage. Les cours pratiques et les formateurs m'ont beaucoup aidé.</p>
                            <h3 class="testi-list_name box-title">Emmanuel Ndongo</h3>
                            <span class="testi-list_desig">Étudiant en mécanique</span>
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
                            <p class="testi-list_text">La formation en couture m'a permis de lancer ma propre marque de
                                vêtements. L'environnement bilingue est un atout majeur.</p>
                            <h3 class="testi-list_name box-title">Clara Essomba</h3>
                            <span class="testi-list_desig">Étudiante en couture</span>
                            <div class="testi-list_review">
                                <i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i
                                    class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i
                                    class="fa-solid fa-star-sharp"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="testi-list">
                        <div class="testi-list_img">
                            <img src="{{ asset('asset_vitrine/assets/img/update1/testimonial/testi_2_3.jpg') }}"
                                alt="Avater">
                            <div class="testi-list_quote"><img
                                    src="{{ asset('asset_vitrine/assets/img/update1/icon/quote_left.svg') }}"
                                    alt="icon"></div>
                        </div>
                        <div class="testi-list_content">
                            <p class="testi-list_text">Les cours d'informatique m'ont ouvert les portes du monde
                                numérique. Je travaille maintenant comme technicien réseau.</p>
                            <h3 class="testi-list_name box-title">Luc Atangana</h3>
                            <span class="testi-list_desig">Étudiant en informatique</span>
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
                        <p>Nous mettons à disposition des bus et véhicules modernes pour faciliter les déplacements des étudiants. Notre service de transport est conçu pour offrir commodité, sécurité et ponctualité. Voici nos engagements :</p>
                        <div class="checklist mt-35 mb-40">
                            <ul>
                                <li>Véhicules confortables et sécurisés.</li>
                                <li>Horaires adaptés aux besoins académiques.</li>
                                <li>Service fiable pour une expérience sans stress.</li>
                                <li>Couverture des trajets vers les campus et lieux de stage.</li>
                            </ul>
                        </div>
                        <div class="btn-group"><a class="th-btn" href="transport.html">En savoir plus sur le transport <i
                                    class="fa fa-arrow-right me"></i></a></div>
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
    <section class="" data-bg-src="{{ asset('asset_vitrine/assets/img/update1/bg/cta_bg_7.jpg') }}"
        data-aos="fade-up" data-aos-duration="800">
        <div class="container">
            <div class="row flex-row-reverse align-items-center">
                <div class="col-xl-6" data-aos="fade-left" data-aos-delay="200">
                    <div class="ps-xxl-5 ms-xl-2 pt-5 pt-xl-0 mt-4 mt-xl-0 mb-n5 mb-xl-0">
                        <div class="text-center">
                            <img src="{{ asset('asset_vitrine/assets/img/update1/normal/vector_4.png') }}"
                                alt="vector">
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
    <section class="space" id="blog-sec" data-aos="fade-up" data-aos-duration="800">
        <div class="container">
            <div class="title-area text-center">
                <span class="sub-title">Nos articles</span>
                <h2 class="sec-title fw-semibold">Actualités ISM</h2>
            </div>
            <div class="row slider-shadow th-carousel" data-slide-show="2" data-lg-slide-show="2"
                data-md-slide-show="1" data-sm-slide-show="1">
                <div class="col-md-6 col-xl-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="blog-recent">
                        <div class="blog-img"><img
                                src="{{ asset('asset_vitrine/assets/img/update1/blog/blog_5_1.jpg') }}"
                                alt="blog image"></div>
                        <div class="blog-content">
                            <div class="blog-meta style2">
                                <a href="#"><i class="far fa-clock"></i>1 Juin 2025</a>
                                {{-- <a href="#"><i class="far fa-user"></i>par Jean Mbarga</a> --}}
                            </div>
                            <h3 class="blog-title">Recrutement massif du personnel ISM</h3>
                            <a href="#" class="th-btn style4">Lire les détails<i
                                    class="fas fa-arrow-right ms-2"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="blog-recent">
                        <div class="blog-img"><img
                                src="{{ asset('asset_vitrine/assets/img/update1/blog/blog_5_2.jpg') }}"
                                alt="blog image"></div>
                        <div class="blog-content">
                            <div class="blog-meta style2">
                                <a href="#"><i class="far fa-clock"></i>2 Juin 2025</a>
                                <a href="#"><i class="far fa-user"></i>par Marie Ngo</a>
                            </div>
                            <h3 class="blog-title">Préinscriptions en ligne bientôt ouvertes</h3>
                            <a href="#" class="th-btn style4">Lire les détails<i
                                    class="fas fa-arrow-right ms-2"></i></a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <div class="space-bottom" id="contact-sec" data-aos="fade-up" data-aos-duration="800">
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
                        <p class="mt-n1 mb-30 sec-text">Nous sommes là pour répondre à toutes vos questions. Remplissez
                            le formulaire pour nous contacter.</p>
                        <form>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control style-white" name="name"
                                            id="name" placeholder="Votre nom*">
                                        <i class="fal fa-user"></i>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="email" class="form-control style-white" name="email"
                                            id="email" placeholder="Adresse e-mail*">
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
                                        <input type="tel" class="form-control style-white" name="number"
                                            id="number" placeholder="Numéro de téléphone*">
                                        <i class="fal fa-phone"></i>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <textarea name="message" id="message" cols="30" rows="3" class="form-control style-white"
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
                                    <a href="#"><img
                                            src="{{ asset('images/logo_white.png') }}"
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
                                    <p class="info-box_text">Ndazoa, près de Mbankomo, Yaoundé, Cameroun</p>
                                </div>
                                <div class="info-box">
                                    <div class="info-box_icon"><i class="fas fa-envelope"></i></div>
                                    <p class="info-box_text">ecoledemetiersndazoa.com</p>
                                </div>
                                <div class="info-box">
                                    <div class="info-box_icon"><i class="fas fa-phone"></i></div>
                                    <p class="info-box_text">+237 695830031 | 691612145</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="copyright-wrap">
            <div class="container">
                <div class="row justify-content-between align-items-center">
                    <div class="col-lg-6">
                        <p class="copyright-text">© 2025 L’École de Métier Ndazoa. Tous droits réservés.</p>
                    </div>
                    <div class="col-lg-6 text-end">
                        <div class="footer-links">
                            <ul>
                                <li><a href="#">À propos</a></li>
                                <li><a href="#">Support</a></li>
                                <li><a href="#">Politique de confidentialité</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <a href="#" class="scrollToTop scroll-btn"><i class="far fa-arrow-up"></i></a>
    <script src="{{ asset('asset_vitrine/assets/js/vendor/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('asset_vitrine/assets/js/app.min.js') }}"></script>
    <script src="{{ asset('asset_vitrine/assets/js/main.js') }}"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js')}}"></script>
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Gestion du modal
        const modal = document.getElementById("registerModal");
        const closeModal = document.getElementsByClassName("close")[0];
        const filiereInput = document.getElementById("filiere");
        const registerButtons = document.querySelectorAll(".register-btn");
        const form = document.getElementById("registerForm");
        const formMessage = document.getElementById("formMessage");

        // Ouvrir le modal et remplir la filière
        registerButtons.forEach(button => {
            button.addEventListener("click", function(e) {
                e.preventDefault();
                const filiere = this.getAttribute("data-filiere");
                filiereInput.value = filiere;
                modal.style.display = "flex";
                formMessage.style.display = "none";
            });
        });

        // Fermer le modal
        closeModal.addEventListener("click", function() {
            modal.style.display = "none";
            formMessage.style.display = "none";
            form.reset();
        });

        // Fermer le modal en cliquant à l'extérieur
        window.addEventListener("click", function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
                formMessage.style.display = "none";
                form.reset();
            }
        });

        // Gérer la soumission du formulaire avec validation
        form.addEventListener("submit", function(e) {
            e.preventDefault();
            const nom = document.getElementById("nom").value.trim();
            const prenom = document.getElementById("prenom").value.trim();
            const contact = document.getElementById("contact").value.trim();
            const email = document.getElementById("email").value.trim();
            const pays = document.getElementById("pays").value.trim();
            const typeFormation = document.getElementById("typeFormation").value;

            if (!nom || !prenom || !contact || !email || !pays || !typeFormation) {
                formMessage.textContent = "Veuillez remplir tous les champs obligatoires.";
                formMessage.style.display = "block";
                return;
            }

            if (!/^[0-9]{10}$/.test(contact)) {
                formMessage.textContent = "Le numéro de téléphone doit contenir exactement 10 chiffres.";
                formMessage.style.display = "block";
                return;
            }

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                formMessage.textContent = "Veuillez entrer une adresse email valide.";
                formMessage.style.display = "block";
                return;
            }

            // Simuler envoi avec succès
            alert("Formulaire soumis avec succès !\nFilière: " + filiereInput.value + "\nNom: " + nom +
                "\nPrénom: " + prenom + "\nContact: " + contact + "\nEmail: " + email + "\nPays: " + pays +
                "\nType de formation: " + typeFormation);
            modal.style.display = "none";
            formMessage.style.display = "none";
            form.reset();
        });
    </script>
</body>

</html>
