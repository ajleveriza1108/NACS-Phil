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
        const closeMenu = () => {
            button.setAttribute("aria-expanded", "false");
            menu.hidden = true;
        };

        button.addEventListener("click", () => {
            const expanded = button.getAttribute("aria-expanded") === "true";
            button.setAttribute("aria-expanded", expanded ? "false" : "true");
            menu.hidden = expanded;
        });

        menu.addEventListener("click", (event) => {
            if (event.target.closest("a")) closeMenu();
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                closeMenu();
                button.focus();
            }
        });

        window.addEventListener("resize", () => {
            if (window.innerWidth > 1050) closeMenu();
        });
    }

    if (reduceMotion) {
        document.documentElement.classList.add("nacs11-reduced-motion");
    }
})();