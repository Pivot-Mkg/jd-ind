<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Join Us - James Douglas India</title>
    <link rel="icon" type="image/x-icon" href="./images/icons/favicon-jdg.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./assets/css/style.css" />
    <link rel="stylesheet" href="./assets/css/open-roles.css" />
    <!-- AOS CSS -->
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <style>
        :root {
            --mouse-w: 26px;
            /* outer mouse width */
            --mouse-h: 44px;
            /* outer mouse height */
            --mouse-border: 2px;
            /* border thickness */
            --mouse-radius: 16px;
            /* outer radius */
            --dot-w: 3px;
            /* inner line width */
            --dot-h: 10px;
            /* inner line height */
            --pad: 6px;
            /* top/bottom padding inside mouse */
            --color: rgba(255, 255, 255, 0.95);
            --shadow: rgba(0, 0, 0, .25);
            --z: 10;
        }

        /* Container for the indicator */
        .scroll-indicator {
            position: absolute;
            left: 50%;
            bottom: 10px;
            transform: translateX(-50%);
            display: inline-grid;
            place-items: center;
            gap: .4rem;
            cursor: pointer;
            -webkit-user-select: none;
            user-select: none;
            z-index: var(--z);
        }

        .scroll-indicator .mouse {
            width: var(--mouse-w);
            height: var(--mouse-h);
            border: var(--mouse-border) solid var(--color);
            border-radius: var(--mouse-radius);
            position: relative;
            box-shadow: 0 4px 10px var(--shadow);
        }

        /* The animated center line */
        .scroll-indicator .dot {
            position: absolute;
            left: 50%;
            width: var(--dot-w);
            height: var(--dot-h);
            margin-left: calc(var(--dot-w) / -2);
            background: var(--color);
            border-radius: 2px;
            /* Start near the top */
            top: var(--pad);
            /* Animation: slow down, quick up, hold at top */
            animation: slide 3s ease-in-out infinite;
        }

        @keyframes slide {
            0% {
                top: var(--pad);
                opacity: 1;
            }

            55% {
                top: calc(var(--mouse-h) - var(--pad) - var(--dot-h));
                opacity: 1;
            }

            70% {
                top: var(--pad);
                opacity: 1;
            }

            88% {
                top: var(--pad);
                opacity: 1;
            }

            92% {
                opacity: .7;
            }

            100% {
                top: var(--pad);
                opacity: 1;
            }
        }

        .scroll-indicator .hint {
            font-size: .75rem;
            letter-spacing: .08em;
            opacity: .9;
        }

        .scroll-indicator .hint svg {
            vertical-align: -2px;
        }

        .scroll-indicator:hover .mouse {
            box-shadow: 0 0 0 5px rgba(255, 255, 255, .08), 0 4px 10px var(--shadow);
        }

        .leadership-img {
            width: 100%;
            height: auto;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .secondary-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1em;
            width: fit-content;
            margin: auto;
        }

        .impact-section .legacy-image {
            width: 80%;
            height: 100%;
            border-radius: 16px;
            display: block;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-left: 3rem;
        }


        #join-us-banner {
            background: url(./assets/images/join-us-img.jpg) center / cover no-repeat;
        }

        @media (max-width:576px) {
            .legacy-content {
                padding: 19px;
            }

            .impact-section .legacy-image {
                width: 80%;
                height: 100%;
                border-radius: 16px;
                display: block;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
                margin-left: 0rem;
                padding-left: 9px;
            }
        }

        .btn-warning-custom {
            color: #fff;
            background-color: var(--jd-warning) !important;
            border-color: var(--jd-warning) !important;
            white-space: normal;
            transition: all 0.5s ease-in-out;
        }

        .role-cta-strip {
            margin-top: 48px;
        }

        .role-cta-inner {
            background-image: url(./assets/images/19209.jpg);

            width: 100%;
            min-height: 180px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 32px;
            padding: 48px 40px 13px 25px;
            /* background: #f3f3f3; */
            border: 1px solid #ececec;
        }

        .role-cta-left {
            display: flex;
            align-items: center;
            gap: 18px;
            min-width: 0;
        }

        .role-cta-avatar-wrap {
            position: relative;
            width: 92px;
            height: 66px;
            flex: 0 0 92px;
            overflow: visible;
        }

        .role-cta-avatar {
            position: absolute;
            left: 8px;
            bottom: 0;
            width: 80px;
            height: 120px;
            object-fit: cover;
            object-position: center top;
            z-index: 2;
        }

        .role-cta-line {
            position: absolute;
            border: 1.5px solid #cfd2ff;
            border-radius: 999px;
            z-index: 1;
        }

        .role-cta-line-one {
            width: 74px;
            height: 74px;
            left: 0;
            top: -1px;
            border-right-color: transparent;
            border-bottom-color: transparent;
            transform: rotate(-8deg);
        }

        .role-cta-line-two {
            width: 32px;
            height: 28px;
            right: 2px;
            top: 8px;
            border-left-color: transparent;
            border-bottom-color: transparent;
            transform: rotate(14deg);
        }

        .role-cta-dot {
            position: absolute;
            right: 6px;
            top: -2px;
            width: 14px;
            height: 14px;
            border: 1.5px solid #cfd2ff;
            border-radius: 50%;
            background: transparent;
        }

        .role-cta-content {
            text-align: left;
        }

        .role-cta-content h3 {
            margin: 0 0 6px;
            font-size: 18px;
            line-height: 1.2;
            font-weight: 700;
            color: var(--jd-primary-dark);
        }

        .role-cta-content p {
            margin: 0;
            font-size: 15px;
            line-height: 1.45;
            color: #4c5064;
            max-width: 460px;
        }

        .role-cta-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 134px;
            height: 38px;
            padding: 0 16px;
            background: #4b45d6;
            color: #fff;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: background 0.25s ease;
            white-space: nowrap;
        }

        .role-cta-btn:hover {
            background: #3832c2;
            color: #fff;
        }

        @media (max-width: 767px) {
            .role-cta-inner {
                flex-direction: column;
                /* align-items: flex-start; */
                padding: 18px 16px;
                gap: 16px;
            }

            .role-cta-left {
                align-items: flex-start;
            }

            .role-cta-content p {
                max-width: 100%;
            }

            .role-cta-btn {
                width: 100%;
            }

            .role-cta-avatar {
                position: absolute;
                left: 8px;
                bottom: -23px;
                width: 80px;
                height: 105px;
                object-fit: cover;
                object-position: center top;
                z-index: 2;
                padding-top: -25px;
                /* margin-top: 44px; */
            }


        }
    </style>

    <style>
        /* Hire Talent Modal */
        .hire-talent-modal {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: none;
        }

        .hire-talent-modal.active {
            display: block;
        }

        .hire-talent-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(12, 18, 37, 0.65);
            backdrop-filter: blur(2px);
        }

        .hire-talent-dialog {
            position: relative;
            width: min(835px, calc(100% - 40px));
            max-height: calc(100vh - 40px);
            margin: 20px auto;
            background: #f8f8f8;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 22px 70px rgba(0, 0, 0, 0.28);
        }

        .hire-talent-dialog-inner {
            padding: 28px 42px 40px;
            overflow-y: auto;
            max-height: calc(100vh - 40px);
        }

        .hire-talent-close {
            position: absolute;
            top: 26px;
            right: 28px;
            width: 44px;
            height: 44px;
            border: none;
            background: transparent;
            color: #5f6b76;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            cursor: pointer;
            z-index: 5;
            transition: color 0.25s ease, transform 0.25s ease;
        }

        .hire-talent-close:hover {
            color: #111827;
            transform: rotate(90deg);
        }

        .hire-talent-form {
            margin-top: 30px;
        }

        .hire-talent-form .form-label {
            font-size: 1rem;
            font-weight: 600;
            color: #06102b;
            margin-bottom: 10px;
        }

        .hire-input {
            min-height: 48px;
            border: 1px solid #cfd5df;
            border-radius: 0;
            background: #fff;
            box-shadow: none !important;
            padding: 12px 16px;
            font-size: 1rem;
            color: #1f2937;
        }

        .hire-input::placeholder {
            color: #98a2b3;
        }

        .hire-textarea {
            min-height: 118px;
            resize: vertical;
            padding-top: 14px;
        }

        .hire-textarea.message-box {
            min-height: 130px;
        }

        .hire-upload-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            min-height: 48px;
        }

        .hire-upload-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #1f1f1f;
            color: #fff;
            border-radius: 999px;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }

        .hire-upload-btn:hover {
            background: #111;
            color: #fff;
        }

        .hire-file-name {
            font-size: 14px;
            color: #6b7280;
        }

        .hire-check-wrap {
            margin-top: 4px;
        }

        .hire-check-wrap .form-check-label {
            font-size: 13px;
            line-height: 1.6;
            color: #374151;
        }

        .hire-check-wrap .form-check-label a {
            color: #111827;
        }

        body.modal-open-custom {
            overflow: hidden;
        }

        @media (max-width: 991px) {
            .hire-talent-dialog {
                width: calc(100% - 165px);
                max-height: calc(100vh - 24px);
                margin: 12px auto;
                border-radius: 18px;
            }

            .hire-talent-dialog-inner {
                padding: 24px 20px 30px;
                max-height: calc(100vh - 24px);
            }

            .hire-talent-close {
                top: 18px;
                right: 18px;
                width: 38px;
                height: 38px;
                font-size: 22px;
            }
        }

        @media (max-width: 576px) {
            .hire-upload-wrap {
                align-items: flex-start;
                flex-direction: column;
            }

            .hire-talent-dialog {
                width: calc(100% - 32px);
                max-height: calc(100vh - 24px);
                margin: 12px auto;
                border-radius: 18px;
            }

            .hire-talent-dialog-inner .section-heading {
                text-align: left !important;
                font-size: 22px;
                font-weight: 500;
            }

            .hire-talent-dialog-inner .section-divider {
                margin-left: 0;
            }
        }

        .team-swiper-wrapper {
            width: 100%;
        }

        .team-card {
            position: relative;
            border-radius: 18px;
            overflow: hidden;
            height: 345px;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.18);
        }

        .team-card-img {
            width: 100%;
            height: stretch;
            /* height: 100%; */
            object-fit: fill;
            display: block;
            transition: transform 0.4s ease;
        }

        .team-card:hover .team-card-img {
            transform: scale(1.05);
        }

        .team-card-overlay {
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            padding: 20px 18px;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.15), transparent);
            color: #fff;
        }

        .team-card-overlay h5 {
            margin-bottom: 4px;
            font-size: 20px;
            font-weight: 600;
        }

        .team-card-overlay p {
            margin: 0;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.85);
        }

        .teamSwiper .swiper-pagination-bullet {
            background: #ffffff;
            opacity: 0.6;
        }

        .teamSwiper .swiper-pagination-bullet-active {
            background: #00aeef;
            opacity: 1;
        }

        .career-slider-wrap {
            position: relative;
            padding: 0 45px;
        }

        .careerCardsSwiper {
            overflow: hidden;
        }

        .careerCardsSwiper .swiper-slide {
            height: auto;
        }

        .career-square-card {
            background: #fff;
            border: 1px solid #e7ecf7;
            border-radius: 18px;
            /* box-shadow: 0 4px 16px rgba(34, 56, 120, 0.08); */
            padding: 28px 22px;
            height: 225px;
            /* aspect-ratio: 1 / 1; */
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            transition: all 0.3s ease;
        }

        .career-square-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 26px rgba(34, 56, 120, 0.14);
        }

        .career-card-icon {
            width: 58px;
            height: 58px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .career-card-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .career-square-card h5 {
            font-size: 22px;
            line-height: 1.4;
            font-weight: 600;
            color: #173b6b;
            margin-bottom: 14px;
        }

        .career-square-card p {
            font-size: 16px;
            line-height: 1.65;
            color: #173b6b;
            margin-bottom: 0;
        }

        .career-swiper-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #ffffff;
            border: 1px solid #dfe7f3;
            box-shadow: 0 4px 12px rgba(22, 59, 105, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            color: #173b6b;
            font-size: 20px;
            transition: all 0.3s ease;
        }

        .career-swiper-btn:hover {
            background: #173b6b;
            color: #fff;
        }

        .career-swiper-prev {
            left: -10px;
        }

        .career-swiper-next {
            right: -10px;
        }

        @media (max-width: 991px) {
            .career-slider-wrap {
                padding: 0 35px;
            }

            .career-square-card {
                min-height: 220px;
            }

            .career-square-card h5 {
                font-size: 18px;
            }

            .career-square-card p {
                font-size: 14px;
            }
        }

        @media (max-width: 767px) {
            .career-slider-wrap {
                padding: 0 28px;
            }

            .career-swiper-btn {
                width: 36px;
                height: 36px;
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top py-3" id="mainNavbar">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="index.html">
                <img src="assets/images/JD_India_logo.png" alt="James Douglas Logo" height="44" class="me-2 ms-5" />
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav mb-2 mb-lg-0">
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="index.html">Home</a>
                    </li> -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="findOpportunitiesDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Find Opportunities
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="findOpportunitiesDropdown">
                            <li><a class="nav-link" href="./careers/">Open Roles</a></li>
                            <li><a class="nav-link" href="./join-us.php">Join US</a></li>
                        </ul>
                    </li>
                    <li>
                        <a class="nav-link" href="hiring-solution.html">Hire Talent</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about-us.html">Our Expertise</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact-us.html">Contact Us</a>
                    </li>

                    <!-- <li class="nav-item">
                        <a class="nav-link" href="join-us.php">Join Us</a>
                    </li> -->

                    <!-- <li>
                        <a class="nav-link" href="services.html">Services</a>
                    </li> -->
                    <!-- <li>
                        <a class="nav-link" href="company.html">For Company</a>
                    </li>
                    <li>
                        <a class="nav-link" href="leaders.html">For Leaders</a>
                    </li> -->

                    <!-- <li class="nav-item">
                        <a class="nav-link" href="insights-hub.html">Insights</a>
                    </li> -->
                </ul>
            </div>
        </div>
    </nav>
    <!-- End Navbar -->

    <!-- Sentinel (used to detect when the original navbar leaves the viewport) -->
    <div id="navSentinel" aria-hidden="true"></div>

    <!-- Sticky clone will be injected here by JS -->
    <div id="stickyNavbarContainer" aria-hidden="true"></div>

    <!-- Banner -->
    <section class="banner" id="join-us-banner">
        <div class="banner-overlay">
            <div class="banner-content">
                <h1>
                    <span class="hero-corner">Join Us in Shaping </span><br>the Future of Leadership

                </h1>
            </div>
        </div>
        <div class="scroll-indicator" role="button" aria-label="Scroll down" data-offset="300" data-target="#features">
            <div class="mouse" aria-hidden="true">
                <div class="dot"></div>
            </div>
            <!-- <div class="hint">Scroll</div> -->
        </div>
    </section>
    <!-- Banner end -->

    <!-- about us -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="text-center" data-aos="fade-up">
                <h2 class="section-heading">
                    Are You Ready to Build, Lead, and Create Impact?
                </h2>
                <div class="section-divider"></div>
                <p class="mt-4 section-subtitle">
                    James Douglas is a leadership search and advisory firm working with organisations across sectors to
                    build high-impact leadership teams. We partner with founders, CXOs, investors and boards to identify
                    and attract leaders who can drive meaningful change. Working at James Douglas offers the opportunity
                    to engage deeply with businesses, industries and leadership journeys while building long-term
                    relationships with senior leaders. We aim to build entrepreneurial consultants who grow into
                    practice leaders.</p>
            </div>

        </div>
    </section>
    <!-- about us end -->

    <!-- Why Choose James Douglas Section -->
    <section class="py-5 impact-section" style="
background: linear-gradient(
120deg,
#23235b 0%,
#23235b 60%,
#2b3a67 100%
);
">
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <!-- Left: Heading, description, buttons -->
                <div class="col-lg-7 mb-4 mb-lg-0 legacy-content" data-aos="fade-right">
                    <h2 class="section-heading text-white">
                        Why Work
                        <span style="color: #00aeef"><br />With Us</span>
                    </h2>
                    <div
                        style="width: 80px; height: 4px; background: #ff9900; margin: 16px 0 24px 0; border-radius: 2px;">
                    </div>

                    <p class="text-white-50 mb-4" style="font-size: 14px">
                        At James Douglas, a career in leadership search goes beyond recruitment. Our work sits at the
                        intersection of business, leadership and long-term relationships.</p>

                    <p class="text-white-50 mb-4 fw-bold" style="font-size: 14px; color:white!important;">
                        You will have the opportunity to:
                    </p>

                    <ul class="text-white-50 mb-4">
                        <li>Work closely with founders, CXOs and senior leadership teams</li>
                        <li>Understand how organisations make critical hiring decisions</li>
                        <li>Build long-term relationships across industries</li>
                        <li>Develop commercial judgment and advisory skills</li>
                        <li>Grow into a practice leader over time</li>
                    </ul>
                    <p class="text-white-50 mb-4">We believe the best consultants are entrepreneurial, curious and
                        comfortable taking ownership of outcomes.</p>

                </div>
                <!-- Right: Two separate containers with 2 cards each -->
                <div class="col-lg-5" data-aos="fade-left">
                    <div class="legacy-image-container">
                        <img src="assets/images/join-us-2.jpg" alt="EMA Partners Global Network"
                            class="img-fluid legacy-image">

                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Why Choose James Douglas Section -->

    <!-- leadership team -->
    <section class="py-5 bg-light d-none" id="management">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="section-heading">
                    Meet Our Leadership
                </h2>
                <div class="section-divider"></div>
                <p class="mt-4 section-subtitle">
                    Our consultants bring onboard top-quality experience and knowledge, making their mark amongst the
                    best in the industry. With an in-depth understanding of organizational relationships and culture, we
                    take pride in our client and candidate relationships nurtured over the years.
                </p>
            </div>

            <!-- Leadership Swiper Carousel -->
            <div class="swiper leadership-swiper" data-aos="slide-up" data-aos-delay="200">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <div class="leadership-card">
                            <div class="leadership-image">
                                <img src="./assets/images/nupur-about-us-img.jpeg" alt="Rajesh Sharma"
                                    class="leadership-img">
                            </div>
                            <div class="leadership-content">
                                <h5 class="leadership-name">Nupur Mehta</h5>
                                <p class="leadership-position">Managing Partner</p>

                                <div class="leadership-social">
                                    <a href="https://www.linkedin.com/in/nupurmehtampi/?originalSubdomain=in"
                                        class="leadership-social-link">
                                        <i class="bi bi-linkedin"></i>
                                    </a>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="swiper-slide">
                        <div class="leadership-card">
                            <div class="leadership-image ">
                                <img src="./images/diksha-uniyal.jpg" alt="Priya Mehta" class="leadership-img">
                            </div>
                            <div class="leadership-content">
                                <h5 class="leadership-name">Diksha Uniyal</h5>
                                <p class="leadership-position">Senior Director</p>

                                <div class="leadership-social">
                                    <a href="https://www.linkedin.com/in/diksha-uniyal-27573054/?originalSubdomain=in"
                                        class="leadership-social-link">
                                        <i class="bi bi-linkedin"></i>
                                    </a>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="swiper-slide">
                        <div class="leadership-card">
                            <div class="leadership-image">
                                <img src="./images/Sakshi-Punjabi-profile.jpg" alt="Amit Kumar" class="leadership-img">
                            </div>
                            <div class="leadership-content">
                                <h5 class="leadership-name">Sakshi Punjabi</h5>
                                <p class="leadership-position">Director</p>

                                <div class="leadership-social">
                                    <a href="https://www.linkedin.com/in/sakshi-punjabi-817147103/?originalSubdomain=in"
                                        class="leadership-social-link">
                                        <i class="bi bi-linkedin"></i>
                                    </a>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Swiper Pagination -->
                <div class="swiper-pagination"></div>

                <!-- Swiper Navigation -->
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>

        </div>
    </section>

    <!-- about us -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="text-center" data-aos="fade-up">
                <h2 class="section-heading">
                    What We Look For
                </h2>
                <div class="section-divider"></div>
                <p class="mt-4 section-subtitle">
                    We look for individuals who bring curiosity, ownership and commercial instinct to their work.
                </p>

            </div>

            <!-- Career Cards Slider -->
            <div class="career-slider-wrap position-relative mt-5" data-aos="fade-up" data-aos-delay="100">
                <div class="swiper careerCardsSwiper">
                    <div class="swiper-wrapper">

                        <div class="swiper-slide">
                            <div class="career-square-card">
                                <div class="career-card-icon">
                                    <img src="assets/images/icons/What We Look For/Entrepreneurial mindset and desire to build.svg"
                                        alt="Growth Opportunities">
                                </div>
                                <p>Entrepreneurial mindset and desire to build</p>
                            </div>
                        </div>

                        <div class="swiper-slide">
                            <div class="career-square-card">
                                <div class="career-card-icon">
                                    <img src="assets/images/icons/What We Look For/Comfort operating in fast-moving and ambiguous environments.svg"
                                        alt="Collaborative Culture">
                                </div>
                                <p>Comfort operating in fast-moving and ambiguous environments</p>
                            </div>
                        </div>

                        <div class="swiper-slide">
                            <div class="career-square-card">
                                <div class="career-card-icon">
                                    <img src="assets/images/icons/What We Look For/Strong ability to drive outcomes and close mandates.svg"
                                        alt="Challenging Work">
                                </div>
                                <p>Strong ability to drive outcomes and close mandates</p>
                            </div>
                        </div>

                        <div class="swiper-slide">
                            <div class="career-square-card">
                                <div class="career-card-icon">
                                    <img src="assets/images/icons/What We Look For/Depth in delivery and execution.svg"
                                        alt="People First">
                                </div>
                                <p>Depth in delivery and execution</p>
                            </div>
                        </div>

                        <div class="swiper-slide">
                            <div class="career-square-card">
                                <div class="career-card-icon">
                                    <img src="assets/images/icons/What We Look For/Resilience and ownership mindset.svg"
                                        alt="Meaningful Impact">
                                </div>
                                <p>Resilience and ownership mindset</p>
                            </div>
                        </div>

                        <div class="swiper-slide">
                            <div class="career-square-card">
                                <div class="career-card-icon">
                                    <img src="assets/images/icons/What We Look For/Structured thinking and problem-solving ability.svg"
                                        alt="Career Progression">
                                </div>
                                <p>Structured thinking and problem-solving ability</p>
                            </div>
                        </div>

                        <div class="swiper-slide">
                            <div class="career-square-card">
                                <div class="career-card-icon">
                                    <img src="assets/images/icons/What We Look For/Intellectual curiosity and ability to have meaningful conversations.svg"
                                        alt="Shared Wins">
                                </div>
                                <p>Intellectual curiosity and ability to have meaningful conversations</p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Arrows -->
                <div class="career-swiper-btn career-swiper-prev">
                    <i class="bi bi-chevron-left"></i>
                </div>
                <div class="career-swiper-btn career-swiper-next">
                    <i class="bi bi-chevron-right"></i>
                </div>
            </div>
            <p class="mt-4 section-subtitle">
                Our consultants frequently engage with CXOs, founders and industry leaders, so strong communication and
                relationship-building abilities are essential.
            </p>
        </div>
    </section>
    <!-- about us end -->

    <section class="py-5 impact-section" style="
background: linear-gradient(
120deg,
#23235b 0%,
#23235b 60%,
#2b3a67 100%
);
">
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <!-- Left: Heading, description -->
                <div class="col-lg-7 mb-4 mb-lg-0 legacy-content" data-aos="fade-right">
                    <h2 class="section-heading text-white">
                        The Way We
                        <span style="color: #00aeef"><br />Work</span>
                    </h2>

                    <div
                        style="width: 80px; height: 4px; background: #ff9900; margin: 16px 0 24px 0; border-radius: 2px;">
                    </div>

                    <p class="text-white-50 mb-4" style="font-size: 14px">
                        Our work is demanding, fast-paced and intellectually engaging, but we believe it should also be
                        enjoyable. We work in a close-knit team where conversations are direct, ideas are openly
                        discussed, and there is always room for humour along the way. Long hours on a search, tough
                        mandates and challenging client situations are part of the job, but so are shared wins, team
                        lunches, spontaneous debates and the satisfaction of closing a difficult assignment.
                    </p>

                    <p class="text-white-50 mb-4" style="font-size: 14px;">
                        We take our work seriously, but we do not take ourselves too seriously. The energy at James
                        Douglas comes from working with people who are driven, curious and enjoy what they do.
                    </p>
                </div>

                <!-- Right: Team Members Swiper -->
                <div class="col-lg-5" data-aos="fade-left">
                    <div class="team-swiper-wrapper position-relative">
                        <div class="swiper teamSwiper">
                            <div class="swiper-wrapper">

                                <!-- Slide 1 -->
                                <div class="swiper-slide">
                                    <div class="team-card">
                                        <img src="assets/images/team-photos/jd-india-team-2.jpeg" alt="Team Member 2"
                                            class="team-card-img">
                                        <div class="team-card-overlay">

                                        </div>
                                    </div>
                                </div>

                                <!-- Slide 3 -->
                                <!-- <div class="swiper-slide">
                                    <div class="team-card">
                                        <img src="assets/images/team-photos/jd-india-team-4 1.jpg" alt="Team Member 3"
                                            class="team-card-img">
                                        <div class="team-card-overlay">

                                        </div>
                                    </div>
                                </div> -->

                                <!-- Slide 4 -->
                                <!-- <div class="swiper-slide">
                                    <div class="team-card">
                                        <img src="assets/images/team-photos/jd-india-team-3.jpg" alt="Team Member 4"
                                            class="team-card-img">
                                        <div class="team-card-overlay">
                                        </div>
                                    </div>
                                </div> -->

                                <!-- Slide 4 -->
                                <div class="swiper-slide">
                                    <div class="team-card">
                                        <img src="assets/images/team-photos/jd-india-team-4.jpeg" alt="Team Member 4"
                                            class="team-card-img">
                                        <div class="team-card-overlay">
                                        </div>
                                    </div>
                                </div>

                                <!-- Slide 4 -->
                                <div class="swiper-slide">
                                    <div class="team-card">
                                        <img src="assets/images/team-photos/jd-team-5.jpeg" alt="Team Member 4"
                                            class="team-card-img">
                                        <div class="team-card-overlay">
                                        </div>
                                    </div>
                                </div>
                                <!-- Slide 4 -->
                                <div class="swiper-slide">
                                    <div class="team-card">
                                        <img src="assets/images/team-photos/Offsite.jpg" alt="Team Member 4"
                                            class="team-card-img">
                                        <div class="team-card-overlay">
                                        </div>
                                    </div>
                                </div>
                                <!-- Slide 4 -->
                                <div class="swiper-slide">
                                    <div class="team-card">
                                        <img src="assets/images/team-photos/offsite 2.jpg" alt="Team Member 4"
                                            class="team-card-img">
                                        <div class="team-card-overlay">
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Pagination -->
                            <div class="swiper-pagination mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container">
            <div class="text-center" data-aos="fade-up">
                <h2 class="section-heading">
                    What You Can Expect From Us
                </h2>
                <div class="section-divider"></div>
                <p class="mt-4 section-subtitle">
                    We believe careers in leadership search should be intellectually stimulating, commercially rewarding
                    and personally fulfilling.
                </p>
                <p class="mt-4 section-subtitle">
                    At James Douglas, you can expect:
                </p>

            </div>

            <!-- Career Cards Slider -->
            <div class="career-slider-wrap position-relative mt-5" data-aos="fade-up" data-aos-delay="100">
                <div class="swiper careerCardsSwiper">
                    <div class="swiper-wrapper">

                        <div class="swiper-slide">
                            <div class="career-square-card">
                                <div class="career-card-icon">
                                    <img src="assets/images/icons/What You Can Expect From Us/Exponential personal and professional growth.svg"
                                        alt="Growth Opportunities">
                                </div>
                                <p>Exponential personal and professional growth</p>
                            </div>
                        </div>

                        <div class="swiper-slide">
                            <div class="career-square-card">
                                <div class="career-card-icon">
                                    <img src="assets/images/icons/What You Can Expect From Us/A steep learning curve and expanding capabilities.svg"
                                        alt="Collaborative Culture">
                                </div>
                                <p>A steep learning curve and expanding capabilities</p>
                            </div>
                        </div>

                        <div class="swiper-slide">
                            <div class="career-square-card">
                                <div class="career-card-icon">
                                    <img src="assets/images/icons/What You Can Expect From Us/A collaborative, high-energy team environment.svg"
                                        alt="Challenging Work">
                                </div>
                                <p>A collaborative, high-energy team environment</p>
                            </div>
                        </div>

                        <div class="swiper-slide">
                            <div class="career-square-card">
                                <div class="career-card-icon">
                                    <img src="assets/images/icons/What You Can Expect From Us/Exposure to decision-makers across industries.svg"
                                        alt="People First">
                                </div>
                                <p>Exposure to decision-makers across industries</p>
                            </div>
                        </div>

                        <div class="swiper-slide">
                            <div class="career-square-card">
                                <div class="career-card-icon">
                                    <img src="assets/images/icons/What You Can Expect From Us/Opportunities to build practices and take on leadership responsibilities.svg"
                                        alt="Meaningful Impact">
                                </div>
                                <p>Opportunities to build practices and take on leadership responsibilities</p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Arrows -->
                <div class="career-swiper-btn career-swiper-prev">
                    <i class="bi bi-chevron-left"></i>
                </div>
                <div class="career-swiper-btn career-swiper-next">
                    <i class="bi bi-chevron-right"></i>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container">
            <div class="text-center" data-aos="fade-up">
                <h2 class="section-heading"> Transform your career with James Douglas </h2>
                <div class="section-divider"></div>
                <p class="mt-4 section-subtitle"> If you’re looking for work that’s thoughtful, people-driven, and
                    genuinely meaningful, you’ll feel at home here. If you are interested to know about you. You can
                    explore opportunities below.</p>
                <!-- <p> The people who do well at James Douglas are those who care about building a career, not just completing tasks. They’re curious, responsible, and invested in doing the work properly, with respect for both clients and candidates. </p> <p> We advise organisations on finding and developing strong talent, and we apply the same standards internally. Growth here isn’t about titles or speed, it’s about learning the craft, building judgment, and becoming better at what you do over time. </p> <p>If you value passion, collaboration, and long-term thinking, we’d like to hear from you.</p> -->
            </div>

            <!-- CTA Strip -->
            <div class="role-cta-strip mt-5" data-aos="fade-up" data-aos-delay="150">
                <div class="role-cta-inner">
                    <div class="role-cta-left">


                        <div class="role-cta-content">
                            <h3>Haven’t found your desired role?</h3>
                            <p>
                                Join us to discover opportunities that match your skills,
                                aspirations, and career goals.
                            </p>
                        </div>
                    </div>

                    <button class="btn btn-warning-custom" id="openHireTalentModal">Register With Us</button>
                </div>
            </div>
            <!-- <div style="display: flex; align-items: center; justify-content: center;"> <img src="./assets/images/join-us-infographic.png" style="height: 500px;" alt="join-us-infographic" class="img-fluid" data-aos="fade-up" data-aos-delay="100"> </div> -->
            <!-- Learn More Button -->
            <!-- <div class="legacy-cta text-center mt-4" data-aos="fade-up" data-aos-delay="400"> <a href="././careers/" class="btn btn-warning-custom px-4 py-2 fw-semibold">View Open Opportunities at James Douglas ></a> </div> -->
        </div>
    </section>

    <!-- Internal Job Listings -->
    <section class="open-roles-section" id="internal-openings">
        <div class="container">
            <div class="text-center mb-5">
                <!-- <h2 class="section-heading">Internal Job Listings</h2> -->
                <h2 class="section-heading">Opportunities at James Douglas</h2>
                <div class="section-divider"></div>
                <!-- <p class="mt-4 text-muted" style="font-size: 1.05rem;">
                    Explore current opportunities at James Douglas.
                </p> -->
            </div>
            <div id="internalJobsStatus" class="text-center text-muted mb-4">Loading jobs...</div>
            <div class="open-roles-grid pb-5">
                <div id="internal-job-list"></div>
                <div id="internal-jobs-pagination" class="jobs-pagination"></div>
            </div>
        </div>
    </section>

    <!-- Work With Us -->
    <section class="pb-5 bg-white d-none" id="apply">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-11 col-xl-11">
                    <div class="contact-form-container">
                        <div class="text-center mb-5">
                            <h2 class="section-heading">Power up your career with James Douglas</h2>
                            <div class="section-divider"></div>
                            <p class="mt-4 text-muted" style="font-size: 1.1rem;">
                                We are always looking for thoughtful, high-performing professionals to join our team.
                            </p>
                        </div>

                        <form class="contact-form" id="joinUsApplyForm" enctype="multipart/form-data">
                            <input type="hidden" name="form_type" value="work_with_us">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="joinFirstName" class="form-label">First Name</label>
                                        <input type="text" class="form-control contact-input" id="joinFirstName"
                                            name="first_name" placeholder="Enter first name" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="joinLastName" class="form-label">Last Name</label>
                                        <input type="text" class="form-control contact-input" id="joinLastName"
                                            name="last_name" placeholder="Enter last name" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="joinPhone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control contact-input" id="joinPhone" name="phone"
                                            placeholder="+91 98XXXXXX" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="joinEmail" class="form-label">Email</label>
                                        <input type="email" class="form-control contact-input" id="joinEmail"
                                            name="email" placeholder="you@example.com" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="joinFunction" class="form-label">Function / Department</label>
                                        <input type="text" class="form-control contact-input" id="joinFunction"
                                            name="function" placeholder="e.g. Sales, Finance, HR" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="joinIndustry" class="form-label">Industry</label>
                                        <input type="text" class="form-control contact-input" id="joinIndustry"
                                            name="industry" placeholder="Enter your industry" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="joinJobTitle" class="form-label">Job Title</label>
                                        <input type="text" class="form-control contact-input" id="joinJobTitle"
                                            name="job_title" placeholder="Enter your current job title" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="joinOrganization" class="form-label">Current Organization</label>
                                        <input type="text" class="form-control contact-input" id="joinOrganization"
                                            name="current_organization" placeholder="Enter current organization"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="joinCurrentSalary" class="form-label">Current CTC</label>
                                        <input type="text" class="form-control contact-input" id="joinCurrentSalary"
                                            name="current_salary" placeholder="Enter current CTC" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="joinLinkedIn" class="form-label">LinkedIn</label>
                                        <input type="url" class="form-control contact-input" id="joinLinkedIn"
                                            name="linkedin_profile"
                                            placeholder="https://www.linkedin.com/in/your-profile" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="joinResume" class="form-label">Resume</label>
                                        <input type="file" class="form-control contact-input" id="joinResume"
                                            name="resume" accept=".pdf,.doc,.docx" required>
                                    </div>
                                </div>

                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-submit">Register your profile</button>
                                    <div id="joinUsStatus" class="small text-muted mt-3"></div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Hire Talent Modal -->
    <div class="hire-talent-modal" id="hireTalentModal" aria-hidden="true">
        <div class="hire-talent-backdrop" id="hireTalentBackdrop"></div>

        <div class="hire-talent-dialog" role="dialog" aria-modal="true" aria-labelledby="hireTalentModalTitle">
            <button type="button" class="hire-talent-close" id="closeHireTalentModal" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>

            <div class="hire-talent-dialog-inner">
                <h2 class="section-heading text-center" id="hireTalentModalTitle">Hire talent with us</h2>
                <div class="section-divider"></div>

                <form id="hireTalentForm" class="hire-talent-form">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="hireFirstName" class="form-label">First Name</label>
                            <input type="text" class="form-control hire-input" id="hireFirstName"
                                placeholder="First name" required>
                        </div>

                        <div class="col-md-6">
                            <label for="hireLastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control hire-input" id="hireLastName" placeholder="Last name"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label for="company" class="form-label">Company</label>
                            <input type="text" class="form-control hire-input" id="company" placeholder="Company"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label for="designation" class="form-label">Designation</label>
                            <input type="text" class="form-control hire-input" id="designation"
                                placeholder="Designation" required>
                        </div>

                        <div class="col-md-6">
                            <label for="hireEmail" class="form-label">Email</label>
                            <input type="email" class="form-control hire-input" id="hireEmail"
                                placeholder="you@company.com" required>
                        </div>

                        <div class="col-md-6">
                            <label for="hirePhone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control hire-input" id="hirePhone" placeholder="Phone no"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label for="hireCity" class="form-label">City</label>
                            <input type="text" class="form-control hire-input" id="hireCity" placeholder="City"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label for="hireCountry" class="form-label">Country</label>
                            <input type="text" class="form-control hire-input" id="hireCountry" placeholder="Country"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label for="hireAttachment" class="form-label">Attach Relevant Document</label>
                            <div class="hire-upload-wrap">
                                <input type="file" id="hireAttachment" hidden>
                                <label for="hireAttachment" class="hire-upload-btn">
                                    <i class="bi bi-upload"></i> Upload files
                                </label>
                                <span class="hire-file-name" id="hireFileName">No file chosen</span>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="hireMessage" class="form-label">Message</label>
                            <textarea class="form-control hire-input hire-textarea message-box" id="hireMessage"
                                placeholder="Message" required></textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check hire-check-wrap">
                                <input class="form-check-input" type="checkbox" id="hireTerms" required>
                                <label class="form-check-label" for="hireTerms">
                                    I agree to the terms &amp; conditions. I consent to be contacted by James Douglas.
                                    <a href="terms-of-service.html" target="_blank">Terms &amp; Conditions</a>
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-warning-custom">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="pt-5 pb-4" style="background: linear-gradient(135deg, #2b3a67 0%, #23235b 100%);">
        <div class="container">
            <div class="row">
                <!-- Company Info -->
                <div class="col-lg-5 col-md-6 mb-4">
                    <div class="about-us-footer" style="max-width: 440px;">
                        <h5 class="footer-heading">About</h5>
                        <p class="text-white mb-4" style="opacity: 0.8; line-height: 1.6;">
                            At James Douglas, we connect exceptional leadership talent with forward-thinking companies
                            through tailored executive search solutions driven by insight, precision, and impact.
                        </p>
                    </div>
                    <!-- Social Media Icons -->
                    <div class="d-flex gap-2">
                        <a href="https://www.linkedin.com/company/jamesdouglas/about/?viewAsMember=true"
                            class="text-white" style="opacity: 0.7;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 40px; height: 40px; background-color: rgba(255,255,255,0.1);">
                                <i class="bi bi-linkedin"></i>
                            </div>
                        </a>
                        <!-- <a href="#" class="text-white" style="opacity: 0.7;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 40px; height: 40px; background-color: rgba(255,255,255,0.1);">
                                <i class="bi bi-twitter"></i>
                            </div>
                        </a>
                        <a href="#" class="text-white" style="opacity: 0.7;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 40px; height: 40px; background-color: rgba(255,255,255,0.1);">
                                <i class="bi bi-globe"></i>
                            </div>
                        </a>
                        <a href="#" class="text-white" style="opacity: 0.7;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 40px; height: 40px; background-color: rgba(255,255,255,0.1);">
                                <i class="bi bi-envelope"></i>
                            </div>
                        </a> -->
                    </div>
                </div>

                <!-- Our Services -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="footer-heading">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="about-us.html" class="text-white text-decoration-none" style="opacity: 0.8;">About
                                Us</a>
                        </li>
                        <li class="mb-2">
                            <a href="./careers/" class="text-white text-decoration-none"
                                style="opacity: 0.8;">Career</a>
                        </li>
                        <li class="mb-2">
                            <a href="insights-hub.html" class="text-white text-decoration-none"
                                style="opacity: 0.8;">Insights</a>
                        </li>
                        <li class="mb-2">
                            <a href="contact-us.html" class="text-white text-decoration-none"
                                style="opacity: 0.8;">Contact</a>
                        </li>
                        <!-- <li class="mb-2">
                            <a href="./about-us.html#management" class="text-white text-decoration-none" style="opacity: 0.8;">Management
                                Team</a>
                        </li> -->
                    </ul>
                </div>

                <!-- Industries -->
                <!-- <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="text-info fw-semibold mb-3">Industries</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="#" class="text-white text-decoration-none" style="opacity: 0.8;">Financial
                                Services</a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-white text-decoration-none" style="opacity: 0.8;">Technology</a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-white text-decoration-none" style="opacity: 0.8;">Healthcare</a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-white text-decoration-none" style="opacity: 0.8;">Manufacturing</a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-white text-decoration-none" style="opacity: 0.8;">Consumer Goods</a>
                        </li>
                    </ul>
                </div> -->

                <!-- Get in Touch -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="text-info fw-semibold mb-3">Get in Touch</h5>
                    <div class="mb-3">
                        <div class="d-flex align-items-start mb-2">
                            <i class="bi bi-geo-alt-fill text-white me-2 mt-1" style="opacity: 0.7;"></i>
                            <div>
                                <p class="text-white mb-0" style="opacity: 0.8;">
                                    1012, 10th Floor, C-Wing, ONE BKC, “G Block”, Bandra – Kurla Complex Bandra (East),
                                    Mumbai- 400 051 India.
                                </p>
                                <!-- <p class="text-white mb-0" style="opacity: 0.8;">Business District</p> -->
                            </div>
                        </div>
                    </div>
                    <!-- <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-telephone-fill text-white me-2" style="opacity: 0.7;"></i>
                            <p class="text-white mb-0" style="opacity: 0.8;">+91 22 3500 8804</p>
                        </div>
                    </div> -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-envelope-fill text-white me-2" style="opacity: 0.7;"></i>
                            <p class="text-white mb-0" style="opacity: 0.8;">info@jamesdouglas.co.in</p>
                        </div>
                    </div>
                    <a href="./contact-us.html" class="btn btn-warning-custom px-4 py-2 fw-semibold">
                        Start a Conversation
                    </a>
                </div>
            </div>

            <!-- Footer Bottom -->
            <hr class="my-4" style="border-color: rgba(255,255,255,0.2);">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-white mb-0" style="opacity: 0.6;"> 2025 James Douglas. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center">
                    <div class="d-flex flex-wrap justify-content-md-end gap-3">
                        <a href="privacy-policy.html" class="text-white text-decoration-none"
                            style="opacity: 0.6;">Privacy Policy</a>
                        <a href="terms-of-service.html" class="text-white text-decoration-none"
                            style="opacity: 0.6;">Terms of Service</a>
                        <a href="cookie-policy.html" class="text-white text-decoration-none"
                            style="opacity: 0.6;">Cookie Policy</a>
                    </div>
                </div>

            </div>
        </div>
    </footer>
    <script src="./assets/js/main.js"></script>

    <!-- Footer end -->

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Initialize Swiper -->
    <script>
        const swiper = new Swiper('.leadership-swiper', {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                768: {
                    slidesPerView: 2,
                    spaceBetween: 30,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
            }
        });
    </script>

    <script>
        function findNextSection(el) {
            const sel = el.dataset.target;
            if (sel) {
                const t = document.querySelector(sel);
                if (t) return t;
            }

            const currentSection = el.closest('section');
            if (currentSection) {
                let sib = currentSection.nextElementSibling;
                while (sib && sib.tagName.toLowerCase() !== 'section') {
                    sib = sib.nextElementSibling;
                }
                if (sib) return sib;
            }

            return null;
        }

        function setupScrollIndicators() {
            const indicators = document.querySelectorAll('.scroll-indicator');
            indicators.forEach(ind => {
                ind.addEventListener('click', () => {
                    const target = findNextSection(ind);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    } else {
                        const offset = parseInt(ind.dataset.offset || '300', 10);
                        window.scrollBy({
                            top: offset,
                            left: 0,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        }

        document.addEventListener('DOMContentLoaded', setupScrollIndicators);
    </script>

    <script>
        const internalJobsList = document.getElementById('internal-job-list');
        const internalJobsStatus = document.getElementById('internalJobsStatus');
        const internalJobsPagination = document.getElementById('internal-jobs-pagination');
        const API_URL = 'https://api.recruitcrm.io/v1/jobs';
        const QUALIFICATIONS_API_URL = 'https://api.recruitcrm.io/v1/qualifications';
        const API_TOKEN = 'Bearer 2k5UW8wswGNHr7zRCWuvP0F7t8wpLFPxJxLfegndOi6PAYs4cXtCfLbVbZg5v8YiGWlAY_F8m-UlRJrWOE9aCV8xNzY5MTYxNjIyOnw6cHJvZHVjdGlvbg==';
        const API_FETCH_LIMIT = 50;
        const PAGE_SIZE = 9;

        let internalJobsCache = null;
        let qualificationsMapCache = null;

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function stringify(value) {
            if (Array.isArray(value)) {
                return value.filter((item) => item !== null && item !== undefined && String(item).trim() !== '').join(', ');
            }
            if (typeof value === 'boolean') {
                return value ? 'Yes' : 'No';
            }
            return value === null || value === undefined ? '' : String(value);
        }

        function getHiringForValue(job) {
            const fields = Array.isArray(job?.custom_fields) ? job.custom_fields : [];
            for (const field of fields) {
                if (!field || typeof field !== 'object') {
                    continue;
                }
                const fieldId = Number(field.field_id ?? field.id ?? 0);
                const entityType = String(field.entity_type ?? '').trim().toLowerCase();
                const fieldName = String(field.field_name ?? field.name ?? '').trim().toLowerCase();
                const fieldType = String(field.field_type ?? field.type ?? '').trim().toLowerCase();
                if (fieldId !== 7 || entityType !== 'job' || fieldName !== 'hiring for' || fieldType !== 'dropdown') {
                    continue;
                }

                let value = field.value ?? '';
                if (value && typeof value === 'object') {
                    value = value.value ?? value.label ?? value.name ?? '';
                }
                return String(value ?? '').trim();
            }
            return '';
        }

        function isJobOpen(job) {
            const status = job?.job_status;
            if (status && typeof status === 'object') {
                const label = String(status.label ?? status.name ?? '').trim();
                if (label) {
                    return label.toLowerCase() === 'open';
                }
                return Number(status.id ?? 0) === 1;
            }
            return String(status ?? '').trim().toLowerCase() === 'open';
        }

        function shouldPostOnWebsite(job) {
            const fields = Array.isArray(job?.custom_fields) ? job.custom_fields : [];
            return fields.some((field) => {
                if (!field || typeof field !== 'object') {
                    return false;
                }

                const fieldId = Number(field.field_id ?? field.id ?? 0);
                const entityType = String(field.entity_type ?? '').trim().toLowerCase();
                const fieldName = String(field.field_name ?? field.name ?? '').trim().toLowerCase();
                const fieldType = String(field.field_type ?? field.type ?? '').trim().toLowerCase();
                let value = field.value ?? field.field_value ?? field.selected ?? '';

                if (value && typeof value === 'object') {
                    value = value.value ?? value.label ?? value.name ?? '';
                }

                return (
                    fieldId === 10 &&
                    entityType === 'job' &&
                    fieldName === 'post on website' &&
                    fieldType === 'dropdown' &&
                    String(value ?? '').trim().toLowerCase() === 'yes'
                );
            });
        }

        function isExactJoinUsInternalJob(job) {
            return getHiringForValue(job) === 'JD (Internal)' &&
                shouldPostOnWebsite(job) &&
                isJobOpen(job);
        }

        function getIndustryValue(job) {
            const candidates = [
                job?.custom_fields,
                job?.custom_field,
                job?.custom_fields_values,
                job?.custom_field_values,
                job?.fields,
            ];

            for (const fields of candidates) {
                if (!Array.isArray(fields)) {
                    continue;
                }

                for (const field of fields) {
                    if (!field || typeof field !== 'object') {
                        continue;
                    }

                    const fieldName = String(field.field_name ?? field.name ?? '').trim().toLowerCase();
                    const fieldId = Number(field.field_id ?? field.id ?? 0);
                    if (fieldId !== 3 && fieldName !== 'job - industry') {
                        continue;
                    }

                    let value = field.value ?? field.field_value ?? field.selected ?? '';
                    if (Array.isArray(value)) {
                        const flat = value.map((item) => String(item)).filter((item) => item.trim() !== '');
                        if (flat.length) {
                            return flat[0].trim();
                        }
                    }
                    if (value && typeof value === 'object') {
                        value = value.value ?? value.label ?? value.name ?? '';
                    }
                    return String(value ?? '').trim();
                }
            }

            const fallback = job?.job_industry ?? '';
            if (Array.isArray(fallback)) {
                return fallback.map((item) => String(item)).filter((item) => item.trim() !== '').join(', ').trim();
            }
            return String(fallback ?? '').trim();
        }

        function getFunctionValue(job) {
            const candidates = [
                job?.custom_fields,
                job?.custom_field,
                job?.custom_fields_values,
                job?.custom_field_values,
                job?.fields,
            ];

            for (const fields of candidates) {
                if (!Array.isArray(fields)) {
                    continue;
                }
                for (const field of fields) {
                    if (!field || typeof field !== 'object') {
                        continue;
                    }
                    const fieldName = String(field.field_name ?? field.name ?? '').trim().toLowerCase();
                    const fieldId = Number(field.field_id ?? field.id ?? 0);
                    if (fieldId !== 8 && fieldName !== 'job - function') {
                        continue;
                    }
                    let value = field.value ?? field.field_value ?? field.selected ?? '';
                    if (Array.isArray(value)) {
                        const flat = value.map((item) => String(item)).filter((item) => item.trim() !== '');
                        if (flat.length) {
                            return flat[0].trim();
                        }
                        value = value.value ?? value.label ?? value.name ?? '';
                    }
                    return String(value ?? '').trim();
                }
            }

            const fallback = job?.job_function ?? job?.function ?? '';
            if (Array.isArray(fallback)) {
                return fallback.map((item) => String(item)).filter((item) => item.trim() !== '').join(', ').trim();
            }
            return String(fallback ?? '').trim();
        }

        function extractQualificationId(job) {
            const directId = job?.qualification_id;
            if (directId !== null && directId !== undefined && directId !== '' && !Number.isNaN(Number(directId))) {
                return Number(directId);
            }

            const nestedId = job?.qualification?.qualification_id ?? job?.qualification?.id;
            if (nestedId !== null && nestedId !== undefined && nestedId !== '' && !Number.isNaN(Number(nestedId))) {
                return Number(nestedId);
            }

            return null;
        }

        function getQualificationValue(job) {
            const directValue = job?.qualification?.label ?? job?.qualification?.name ?? job?.qualification_name ?? job?.qualification_label ?? '';
            const normalizedDirectValue = String(directValue ?? '').trim();
            if (normalizedDirectValue) {
                return normalizedDirectValue;
            }

            const qualificationId = extractQualificationId(job);
            if (qualificationId !== null && qualificationsMapCache && qualificationsMapCache.has(qualificationId)) {
                return qualificationsMapCache.get(qualificationId) || '';
            }

            return '';
        }

        function formatExperienceRange(minExp, maxExp) {
            const min = stringify(minExp).trim();
            const max = stringify(maxExp).trim();

            if (min && max) {
                return min === max ? `${min} Years` : `${min} to ${max} Years`;
            }

            if (min) {
                return `${min} Years`;
            }

            if (max) {
                return `${max} Years`;
            }

            return 'Not specified';
        }

        async function fetchPage(url) {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                    Authorization: API_TOKEN,
                },
            });

            if (!response.ok) {
                throw new Error('Unable to load roles');
            }

            const data = await response.json();
            const jobs = data.data ?? data.jobs ?? (Array.isArray(data) ? data : []);
            const nextPageUrl = data.next_page_url ?? null;

            return {
                jobs,
                nextPageUrl
            };
        }

        async function fetchQualificationsMap() {
            if (qualificationsMapCache) {
                return qualificationsMapCache;
            }

            const response = await fetch(QUALIFICATIONS_API_URL, {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                    Authorization: API_TOKEN,
                },
            });

            if (!response.ok) {
                throw new Error('Unable to load qualifications');
            }

            const data = await response.json();
            const items = Array.isArray(data) ?
                data :
                Array.isArray(data?.data) ?
                data.data :
                Array.isArray(data?.qualifications) ?
                data.qualifications :
                [];

            qualificationsMapCache = new Map(
                items
                .filter((item) => item && typeof item === 'object')
                .map((item) => [Number(item.qualification_id ?? item.id), String(item.label ?? item.name ?? '').trim()])
                .filter(([id, label]) => !Number.isNaN(id) && label)
            );

            return qualificationsMapCache;
        }

        async function fetchAllInternalJobs(limit = API_FETCH_LIMIT, maxPages = 100) {
            let allJobs = [];
            let nextUrl = `${API_URL}?limit=${limit}`;
            let pageCount = 0;

            while (nextUrl && pageCount < maxPages) {
                pageCount += 1;
                const {
                    jobs,
                    nextPageUrl
                } = await fetchPage(nextUrl);
                allJobs = allJobs.concat(jobs);
                nextUrl = nextPageUrl;
            }

            return allJobs.filter((job) => isExactJoinUsInternalJob(job));
        }

        async function getInternalJobs() {
            if (!internalJobsCache) {
                await fetchQualificationsMap();
                internalJobsCache = await fetchAllInternalJobs();
            }
            return internalJobsCache;
        }

        function renderInternalPagination(pagination) {
            if (!internalJobsPagination) {
                return;
            }
            if (!pagination || pagination.last_page <= 1) {
                internalJobsPagination.innerHTML = '';
                return;
            }

            const current = pagination.current_page || 1;
            const last = pagination.last_page || 1;
            let html = '';

            html += `<button class="pagination-btn" data-page="${Math.max(1, current - 1)}" ${current === 1 ? 'disabled' : ''}>Prev</button>`;

            const start = Math.max(1, current - 2);
            const end = Math.min(last, current + 2);

            if (start > 1) {
                html += '<button class="pagination-btn" data-page="1">1</button>';
                if (start > 2) {
                    html += '<span class="pagination-ellipsis">...</span>';
                }
            }

            for (let i = start; i <= end; i += 1) {
                html += `<button class="pagination-btn ${i === current ? 'active' : ''}" data-page="${i}">${i}</button>`;
            }

            if (end < last) {
                if (end < last - 1) {
                    html += '<span class="pagination-ellipsis">...</span>';
                }
                html += `<button class="pagination-btn" data-page="${last}">${last}</button>`;
            }

            html += `<button class="pagination-btn" data-page="${Math.min(last, current + 1)}" ${current === last ? 'disabled' : ''}>Next</button>`;
            internalJobsPagination.innerHTML = html;

            internalJobsPagination.querySelectorAll('.pagination-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    const nextPage = parseInt(button.dataset.page || '1', 10);
                    if (Number.isNaN(nextPage) || nextPage === current) {
                        return;
                    }
                    loadInternalJobs(nextPage);
                    internalJobsList.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                });
            });
        }

        function renderInternalJobs(jobs) {
            if (!internalJobsList || !internalJobsStatus) {
                return;
            }

            internalJobsStatus.textContent = '';
            internalJobsList.innerHTML = '';

            if (!jobs.length) {
                internalJobsStatus.textContent = 'No internal jobs available right now.';
                return;
            }

            jobs.forEach((job, index) => {
                const title = stringify(job?.name ?? job?.title ?? 'Open Position');
                const city = stringify(job?.city ?? '');
                const minExp = stringify(job?.minimum_experience ?? job?.min_experience ?? job?.min_exp ?? '');
                const maxExp = stringify(job?.maximum_experience ?? job?.max_experience ?? job?.max_exp ?? '');
                const expRange = formatExperienceRange(minExp, maxExp);
                const noteForCandidates = stringify(job?.note_for_candidates ?? '').trim() || 'Not specified';
                const jobSlug = String(job?.slug ?? job?.job_slug ?? '');

                const card = document.createElement('a');
                card.className = `open-role-list-card ${index === 0 ? 'active' : ''} mb-3`;
                card.href = `careers/job-details.php?slug=${encodeURIComponent(jobSlug)}`;
                card.innerHTML = `
                    <div class="open-role-header">
                        <div class="open-role-brand">
                            <div>
                                <div class="open-role-title">${escapeHtml(title)}</div>
                            </div>
                        </div>
                    </div>
                    <div class="open-role-info">
                        <div>
                            <div class="open-role-info-label">Location</div>
                            <div class="open-role-info-value">${escapeHtml(city || 'Not specified')}</div>
                        </div>
                        <div>
                            <div class="open-role-info-label">Experience</div>
                            <div class="open-role-info-value">${escapeHtml(expRange)}</div>
                        </div>
                        <div class="open-role-info-note">
                            <div class="open-role-info-label">Note for Candidates</div>
                            <div class="open-role-info-value">${escapeHtml(noteForCandidates)}</div>
                        </div>
                    </div>
                `;
                internalJobsList.appendChild(card);
            });
        }

        async function loadInternalJobs(page = 1) {
            if (!internalJobsList || !internalJobsStatus) {
                return;
            }

            try {
                internalJobsStatus.textContent = 'Loading jobs...';
                const allInternalJobs = await getInternalJobs();
                const total = allInternalJobs.length;
                const lastPage = Math.max(1, Math.ceil(total / PAGE_SIZE));
                const currentPage = Math.min(Math.max(1, page), lastPage);
                const offset = (currentPage - 1) * PAGE_SIZE;
                const pageJobs = allInternalJobs.slice(offset, offset + PAGE_SIZE);

                renderInternalJobs(pageJobs);
                renderInternalPagination({
                    current_page: currentPage,
                    per_page: PAGE_SIZE,
                    total,
                    last_page: lastPage,
                });
            } catch (error) {
                internalJobsStatus.textContent = 'Unable to load internal jobs right now.';
                internalJobsList.innerHTML = '';
                if (internalJobsPagination) {
                    internalJobsPagination.innerHTML = '';
                }
            }
        }

        loadInternalJobs();
    </script>

    <script>
        const joinUsApplyForm = document.getElementById('joinUsApplyForm');
        const joinUsStatus = document.getElementById('joinUsStatus');
        if (joinUsApplyForm && joinUsStatus) {
            joinUsApplyForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                joinUsStatus.textContent = 'Submitting...';
                joinUsStatus.classList.remove('text-danger', 'text-success');
                joinUsStatus.classList.add('text-muted');

                const formData = new FormData(joinUsApplyForm);
                try {
                    const response = await fetch('careers/apply-job.php', {
                        method: 'POST',
                        body: formData,
                    });
                    const data = await response.json().catch(() => ({}));
                    if (response.ok && !data.error) {
                        joinUsStatus.textContent = data.message || 'Submitted successfully.';
                        joinUsStatus.classList.remove('text-muted', 'text-danger');
                        joinUsStatus.classList.add('text-success');
                        joinUsApplyForm.reset();
                    } else {
                        joinUsStatus.textContent = data.message || 'Failed to submit.';
                        joinUsStatus.classList.remove('text-muted', 'text-success');
                        joinUsStatus.classList.add('text-danger');
                    }
                } catch (error) {
                    joinUsStatus.textContent = 'Failed to submit.';
                    joinUsStatus.classList.remove('text-muted', 'text-success');
                    joinUsStatus.classList.add('text-danger');
                }
            });
        }
    </script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    </script>

    <script>
        const hireTalentModal = document.getElementById('hireTalentModal');
        const openHireTalentModal = document.getElementById('openHireTalentModal');
        const closeHireTalentModal = document.getElementById('closeHireTalentModal');
        const hireTalentBackdrop = document.getElementById('hireTalentBackdrop');
        const hireAttachment = document.getElementById('hireAttachment');
        const hireFileName = document.getElementById('hireFileName');
        const hireTalentForm = document.getElementById('hireTalentForm');

        function openModal() {
            hireTalentModal.classList.add('active');
            document.body.classList.add('modal-open-custom');
            hireTalentModal.setAttribute('aria-hidden', 'false');
        }

        function closeModal() {
            hireTalentModal.classList.remove('active');
            document.body.classList.remove('modal-open-custom');
            hireTalentModal.setAttribute('aria-hidden', 'true');
        }

        if (openHireTalentModal) {
            openHireTalentModal.addEventListener('click', openModal);
        }

        if (closeHireTalentModal) {
            closeHireTalentModal.addEventListener('click', closeModal);
        }

        if (hireTalentBackdrop) {
            hireTalentBackdrop.addEventListener('click', closeModal);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && hireTalentModal.classList.contains('active')) {
                closeModal();
            }
        });

        if (hireAttachment) {
            hireAttachment.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    hireFileName.textContent = this.files[0].name;
                } else {
                    hireFileName.textContent = 'No file chosen';
                }
            });
        }

        if (hireTalentForm) {
            hireTalentForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const firstName = document.getElementById('hireFirstName').value.trim();
                const lastName = document.getElementById('hireLastName').value.trim();
                const company = document.getElementById('company').value.trim();
                const designation = document.getElementById('designation').value.trim();
                const email = document.getElementById('hireEmail').value.trim();
                const phone = document.getElementById('hirePhone').value.trim();
                const city = document.getElementById('hireCity').value.trim();
                const country = document.getElementById('hireCountry').value.trim();
                const message = document.getElementById('hireMessage').value.trim();
                const terms = document.getElementById('hireTerms').checked;

                if (!firstName || !lastName || !company || !designation || !email || !phone || !city || !country || !message) {
                    alert('Please fill in all required fields.');
                    return;
                }

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    alert('Please enter a valid email address.');
                    return;
                }

                const phoneRegex = /^[+]?[\d\s\-()]+$/;
                if (!phoneRegex.test(phone)) {
                    alert('Please enter a valid phone number.');
                    return;
                }

                if (!terms) {
                    alert('Please accept the terms and conditions.');
                    return;
                }

                alert('Thank you for your enquiry! We will get back to you soon.');
                this.reset();
                hireFileName.textContent = 'No file chosen';
                closeModal();
            });
        }
    </script>
    <script>
        var teamSwiper = new Swiper(".teamSwiper", {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 2500,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".teamSwiper .swiper-pagination",
                clickable: true,
            }
        });
    </script>

    <script>
        var careerCardsSwiper = new Swiper(".careerCardsSwiper", {
            slidesPerView: 4,
            spaceBetween: 24,
            loop: true,
            navigation: {
                nextEl: ".career-swiper-next",
                prevEl: ".career-swiper-prev",
            },
            breakpoints: {
                0: {
                    slidesPerView: 1,
                    spaceBetween: 16,
                },
                576: {
                    slidesPerView: 2,
                    spaceBetween: 18,
                },
                992: {
                    slidesPerView: 3,
                    spaceBetween: 20,
                },
                1200: {
                    slidesPerView: 4,
                    spaceBetween: 24,
                }
            }
        });
    </script>
</body>

</html>