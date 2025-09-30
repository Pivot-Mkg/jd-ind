<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Navbar – James Douglas</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Optional: tidy up logo size */
        .navbar-brand img {
            height: 36px;
            /* adjust as needed */
            width: auto;
            object-fit: contain;
        }

        .nav-link {
            font-size: 14px;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg bg-white border-bottom" aria-label="Primary navigation">
        <div class="container">
            <!-- Left: Logo (placeholder) -->
            <a class="navbar-brand d-flex align-items-center" href="/">
                <!-- Replace src with your logo path -->
                <img src="assets/images/JD-logo.png" alt="James Douglas Logo">
            </a>

            <!-- Mobile toggler -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Right: Nav items -->
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

                    <li class="nav-item">
                        <a class="nav-link" href="/">Home</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/for-companies">For Companies</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/for-leaders">For Leaders</a>
                    </li>

                    <!-- Practices (dropdown) -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navPractices" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Practices
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navPractices">
                            <li><a class="dropdown-item" href="/practices/bfsi">Banking &amp; Financial Services</a>
                            </li>
                            <li><a class="dropdown-item" href="/practices/technology-digital">Technology &amp;
                                    Digital</a></li>
                            <li><a class="dropdown-item" href="/practices/industrial-logistics">Industrial &amp;
                                    Logistics</a></li>
                            <li><a class="dropdown-item" href="/practices/family-business-private-enterprise">Family
                                    Business &amp; Private Enterprise</a></li>
                            <li><a class="dropdown-item" href="/practices/nationalization">Nationalization
                                    (Emiratization &amp; Saudization)</a></li>
                        </ul>
                    </li>

                    <!-- Insights Hub (dropdown) -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navInsights" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Insights Hub
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navInsights">
                            <li><a class="dropdown-item" href="/insights/blog">Blog</a></li>
                            <li><a class="dropdown-item" href="/insights/whitepapers">Whitepapers</a></li>
                            <li><a class="dropdown-item" href="/insights/gcc-talent-trends">GCC Talent Trends
                                    Reports</a></li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/about-us">About Us</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/careers">Careers</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/contact">Contact Us</a>
                    </li>

                    <!-- Regional Pages (dropdown) -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navRegional" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Regional Pages
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navRegional">
                            <li><a class="dropdown-item" href="/region/uae-middle-east">UAE &amp; Middle East</a></li>
                            <li><a class="dropdown-item" href="/region/india">India</a></li>
                        </ul>
                    </li>

                </ul>
            </div>
        </div>
    </nav>
    <!-- Bootstrap 5 bundle (must be before the script below) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        /* Smooth slide/opacity for navbar dropdowns (desktop) */
        @media (min-width: 992px) {
            .navbar .dropdown-menu {
                /* Keep menu in DOM to animate; Bootstrap toggles .show */
                display: block;
                /* override default to allow transitions */
                visibility: hidden;
                opacity: 0;
                transform: translateY(-6px);
                pointer-events: none;

                max-height: 0;
                /* slide effect */
                overflow: hidden;

                transition:
                    opacity .18s ease,
                    transform .18s ease,
                    max-height .22s ease;
            }

            .navbar .dropdown.show>.dropdown-menu,
            .navbar .dropdown-menu.show {
                visibility: visible;
                opacity: 1;
                transform: translateY(0);
                pointer-events: auto;

                /* large enough to fit typical menus */
                max-height: 600px;
            }

            /* Respect reduced motion */
            @media (prefers-reduced-motion: reduce) {
                .navbar .dropdown-menu {
                    transition: none;
                    transform: none;
                }
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const canHover = window.matchMedia("(hover: hover) and (pointer: fine)");
            const wide = window.matchMedia("(min-width: 992px)"); // lg+

            let currentlyOpen = null;
            let openTO = null, closeTO = null;

            function shouldHover() { return canHover.matches && wide.matches; }

            function getDD(dropdownEl) {
                const toggleEl = dropdownEl.querySelector('[data-bs-toggle="dropdown"]');
                if (!toggleEl) return null;
                return bootstrap.Dropdown.getOrCreateInstance(toggleEl, { autoClose: "outside" });
            }

            function openSequential(target) {
                if (!shouldHover()) return;

                clearTimeout(openTO); clearTimeout(closeTO);

                const targetDD = getDD(target);
                if (!targetDD) return;

                if (currentlyOpen && currentlyOpen !== target) {
                    // Close the current one first, then open the next when hidden
                    const cur = currentlyOpen;
                    const curDD = getDD(cur);
                    if (curDD) {
                        cur.addEventListener('hidden.bs.dropdown', function handler() {
                            cur.removeEventListener('hidden.bs.dropdown', handler);
                            currentlyOpen = null;
                            // small delay so CSS close animation completes visually
                            openTO = setTimeout(() => {
                                targetDD.show();
                                currentlyOpen = target;
                            }, 40);
                        }, { once: true });
                        curDD.hide();
                    }
                } else {
                    openTO = setTimeout(() => {
                        targetDD.show();
                        currentlyOpen = target;
                    }, 100); // tiny hover delay
                }
            }

            function closeCurrent(dropdown) {
                if (!shouldHover()) return;
                clearTimeout(openTO); clearTimeout(closeTO);

                const dd = getDD(dropdown);
                if (!dd) return;

                closeTO = setTimeout(() => {
                    dd.hide();
                    dropdown.addEventListener('hidden.bs.dropdown', function handler() {
                        dropdown.removeEventListener('hidden.bs.dropdown', handler);
                        if (currentlyOpen === dropdown) currentlyOpen = null;
                    }, { once: true });
                }, 140); // small delay to let users move between items
            }

            // Bind hover behaviors to navbar dropdowns
            document.querySelectorAll('.navbar .dropdown').forEach(dropdown => {
                dropdown.addEventListener('mouseenter', () => openSequential(dropdown));
                dropdown.addEventListener('mouseleave', () => closeCurrent(dropdown));
            });

            // If user clicks a toggle, still enforce single-open rule
            document.querySelectorAll('.navbar [data-bs-toggle="dropdown"]').forEach(tg => {
                tg.addEventListener('click', (e) => {
                    if (!shouldHover()) return; // keep default on mobile
                    const ddEl = tg.closest('.dropdown');
                    if (currentlyOpen && currentlyOpen !== ddEl) {
                        e.preventDefault(); // prevent immediate open
                        openSequential(ddEl); // sequence instead
                    }
                });
            });

            // On window resize, close any open menu to avoid odd states
            window.addEventListener('resize', () => {
                if (!shouldHover() && currentlyOpen) {
                    const curDD = getDD(currentlyOpen);
                    if (curDD) curDD.hide();
                    currentlyOpen = null;
                }
            });
        });
    </script>

</body>

</html>