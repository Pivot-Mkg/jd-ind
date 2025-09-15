// James Douglas Website JavaScript

document.addEventListener('DOMContentLoaded', function () {
    const canHover = window.matchMedia("(hover: hover) and (pointer: fine)");
    const wide = window.matchMedia("(min-width: 992px)");

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
            const cur = currentlyOpen;
            const curDD = getDD(cur);
            if (curDD) {
                cur.addEventListener('hidden.bs.dropdown', function handler() {
                    cur.removeEventListener('hidden.bs.dropdown', handler);
                    currentlyOpen = null;
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
            }, 100);
        }
    }

    function closeDelayed(target) {
        if (!shouldHover()) return;
        clearTimeout(openTO); clearTimeout(closeTO);
        closeTO = setTimeout(() => {
            const dd = getDD(target);
            if (dd) {
                dd.hide();
                currentlyOpen = null;
            }
        }, 200);
    }

    // Attach hover listeners to dropdown items
    document.querySelectorAll('.navbar .dropdown').forEach(dropdown => {
        dropdown.addEventListener('mouseenter', () => openSequential(dropdown));
        dropdown.addEventListener('mouseleave', () => closeDelayed(dropdown));
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
});