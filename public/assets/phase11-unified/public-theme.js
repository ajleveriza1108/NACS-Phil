(() => {
    "use strict";

    const header = document.querySelector("[data-nacs11-header]");
    const button = document.querySelector("[data-nacs11-menu-button]");
    const menu = document.querySelector("[data-nacs11-mobile-nav]");
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    const updateHeader = () => {
        if (!header) return;
        header.classList.toggle("is-scrolled", window.scrollY > 16);
    };

    updateHeader();
    window.addEventListener("scroll", updateHeader, { passive: true });

    if (button && menu) {
        const isMenuOpen = () => button.getAttribute("aria-expanded") === "true";

        const closeMenu = (restoreFocus = false) => {
            const wasExpanded = isMenuOpen();
            button.setAttribute("aria-expanded", "false");
            menu.hidden = true;

            if (restoreFocus && wasExpanded) {
                button.focus();
            }
        };

        button.addEventListener("click", () => {
            const expanded = isMenuOpen();
            button.setAttribute("aria-expanded", expanded ? "false" : "true");
            menu.hidden = expanded;
        });

        menu.addEventListener("click", (event) => {
            if (event.target.closest("a")) closeMenu();
        });

        document.addEventListener("pointerdown", (event) => {
            if (!isMenuOpen()) return;
            if (button.contains(event.target) || menu.contains(event.target)) return;

            closeMenu();
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape" && isMenuOpen()) {
                event.preventDefault();
                closeMenu(true);
            }
        });

        window.addEventListener("resize", () => {
            if (window.innerWidth > 1050 && isMenuOpen()) closeMenu();
        });
    }

    if (reduceMotion) {
        document.documentElement.classList.add("nacs11-reduced-motion");
    }
})();