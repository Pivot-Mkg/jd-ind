(function () {
    const original = document.getElementById('mainNavbar');
    const sentinel = document.getElementById('navSentinel');
    const stickyContainer = document.getElementById('stickyNavbarContainer');

    // Clone the original navbar markup into a sticky version
    const sticky = original.cloneNode(true);
    sticky.id = 'stickyNavbar';
    sticky.classList.remove('slide-up'); // ensure clean state
    sticky.classList.add('sticky-navbar');
    stickyContainer.appendChild(sticky);

    // Keep sentinel equal to navbar height to detect when it leaves viewport
    function updateSentinelHeight() {
        // sentinel.style.height = original.offsetHeight + 'px';
        sentinel.style.height = 0 + 'px';
    }
    updateSentinelHeight();
    window.addEventListener('resize', updateSentinelHeight);

    // Track scroll direction for slide-up behavior
    let lastY = window.scrollY;
    let ticking = false;

    function onScrollDirection() {
        const currentY = window.scrollY;
        const scrollingDown = currentY > lastY;

        // Slide the original navbar up only when scrolling down
        if (scrollingDown && currentY > 10) {
            original.classList.add('slide-up');
        } else {
            original.classList.remove('slide-up');
        }

        lastY = currentY;
        ticking = false;
    }

    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(onScrollDirection);
            ticking = true;
        }
    }, { passive: true });

    // Observe when the original navbar leaves the viewport
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                // When the sentinel (placed right under navbar) is NOT visible,
                // it means the original navbar has scrolled out of view.
                if (entry.isIntersecting) {
                    // Original in view → hide sticky, allow original to show
                    sticky.classList.remove('show');
                } else {
                    // Original out of view → show sticky with slide-down
                    sticky.classList.add('show');
                }
            });
        },
        {
            root: null,
            threshold: 0, // trigger as soon as it leaves/enters
        }
    );

    observer.observe(sentinel);
})();

