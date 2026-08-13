(() => {
    "use strict";

    const header = document.querySelector("[data-site-header]");
    const menuButton = document.querySelector("[data-menu-button]");
    const mobileMenu = document.querySelector("[data-mobile-menu]");

    const updateHeader = () => {
        if (!header) return;
        header.classList.toggle("is-scrolled", window.scrollY > 16);
    };

    updateHeader();
    window.addEventListener("scroll", updateHeader, { passive: true });

    if (menuButton && mobileMenu) {
        menuButton.addEventListener("click", () => {
            const expanded = menuButton.getAttribute("aria-expanded") === "true";
            menuButton.setAttribute("aria-expanded", expanded ? "false" : "true");
            mobileMenu.hidden = expanded;
        });

        mobileMenu.addEventListener("click", (event) => {
            if (event.target.closest("a")) {
                menuButton.setAttribute("aria-expanded", "false");
                mobileMenu.hidden = true;
            }
        });

        window.addEventListener("resize", () => {
            if (window.innerWidth > 1050) {
                menuButton.setAttribute("aria-expanded", "false");
                mobileMenu.hidden = true;
            }
        });
    }

    const revealNodes = Array.from(document.querySelectorAll("[data-reveal]"));
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    if (reduceMotion || !("IntersectionObserver" in window)) {
        revealNodes.forEach((node) => node.classList.add("is-visible"));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add("is-visible");
            observer.unobserve(entry.target);
        });
    }, {
        threshold: 0.12,
        rootMargin: "0px 0px -5% 0px"
    });

    revealNodes.forEach((node, index) => {
        if (index < 6) {
            node.style.transitionDelay = `${Math.min(index * 45, 180)}ms`;
        }
        observer.observe(node);
    });
})();