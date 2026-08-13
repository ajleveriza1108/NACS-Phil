(() => {
    "use strict";

    const header = document.querySelector("[data-programs-header]");
    const button = document.querySelector("[data-programs-menu-button]");
    const mobile = document.querySelector("[data-programs-mobile-nav]");

    const updateHeader = () => {
        if (header) header.classList.toggle("is-scrolled", window.scrollY > 16);
    };

    updateHeader();
    window.addEventListener("scroll", updateHeader, { passive: true });

    if (button && mobile) {
        const close = () => {
            button.setAttribute("aria-expanded", "false");
            mobile.hidden = true;
        };

        button.addEventListener("click", () => {
            const expanded = button.getAttribute("aria-expanded") === "true";
            button.setAttribute("aria-expanded", expanded ? "false" : "true");
            mobile.hidden = expanded;
        });

        mobile.addEventListener("click", (event) => {
            if (event.target.closest("a")) close();
        });

        window.addEventListener("resize", () => {
            if (window.innerWidth > 1024) close();
        });
    }

    const nodes = Array.from(document.querySelectorAll("[data-programs-reveal]"));
    const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    if (reduced || !("IntersectionObserver" in window)) {
        nodes.forEach((node) => node.classList.add("is-visible"));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add("is-visible");
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.10, rootMargin: "0px 0px -4% 0px" });

    nodes.forEach((node, index) => {
        if (index < 8) node.style.transitionDelay = `${Math.min(index * 30, 180)}ms`;
        observer.observe(node);
    });
})();