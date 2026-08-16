(() => {
    "use strict";

    const root = document.documentElement;
    const body = document.body;
    if (!body || body.dataset.nacsMotionBound === "1") return;

    body.dataset.nacsMotionBound = "1";
    root.classList.add("nacs-motion-js");
    body.classList.add("nacs-motion");

    const reduceQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
    const isAdmin = body.matches(".cm-body, .p13-admin, .sis-body");
    const isAuth = body.matches(".nacs-auth-body");

    body.classList.add(
        isAdmin ? "nacs-motion-admin" :
        isAuth ? "nacs-motion-auth" :
        "nacs-motion-public"
    );

    const applyReducedMotion = () => {
        root.classList.toggle("nacs-motion-reduced", reduceQuery.matches);
    };
    applyReducedMotion();

    if (typeof reduceQuery.addEventListener === "function") {
        reduceQuery.addEventListener("change", applyReducedMotion);
    }

    const buttonSelectors = [
        ".nacs11-button",
        ".cm-button",
        ".nacs-auth-primary",
        ".sis-primary",
        ".sis-secondary",
        ".sis-link-button",
        "button[class*='button']",
        "a[class*='button']"
    ];

    document.querySelectorAll(buttonSelectors.join(",")).forEach((element) => {
        element.dataset.nacsButton = "1";
    });

    const cardCandidates = document.querySelectorAll(
        "main article, main a[class*='card'], main div[class*='card'], main section[class*='card']"
    );

    cardCandidates.forEach((element) => {
        const isBaseCard = [...element.classList].some((token) => {
            return token === "card" ||
                token.endsWith("-card") ||
                token.endsWith("__card") ||
                token === "sis-panel";
        });

        if (isBaseCard && !element.closest("[data-g-lightbox]")) {
            element.dataset.nacsCard = "1";
        }
    });

    if (isAdmin) {
        document.querySelectorAll(".sis-table-wrap").forEach((wrapper) => {
            const rows = wrapper.querySelectorAll(".sis-table tbody tr");
            if (rows.length > 10) {
                wrapper.dataset.nacsStickyTable = "1";
            }
        });
    }

    if (!body.classList.contains("nacs-motion-public")) {
        requestAnimationFrame(() => body.classList.add("nacs-motion-entered"));
        return;
    }

    const main = document.querySelector("main");
    if (!main) {
        body.classList.add("nacs-motion-entered");
        return;
    }

    const heroHeading = main.querySelector("h1");
    let heroScope = null;

    if (heroHeading) {
        heroScope = heroHeading.closest("section, header, [class*='hero']") || heroHeading.parentElement;

        if (heroScope) {
            const heroCandidates = [
                heroScope.querySelector("[class*='eyebrow'], [class*='kicker'], [class*='badge']"),
                heroHeading,
                heroScope.querySelector("p"),
                heroScope.querySelector("[class*='actions'], [class*='cta']")
            ].filter(Boolean);

            [...new Set(heroCandidates)].slice(0, 4).forEach((element, index) => {
                element.dataset.nacsHeroItem = "1";
                element.style.setProperty("--nacs-motion-delay", `${index * 60}ms`);
            });
        }
    }

    const revealCandidates = [
        ...main.querySelectorAll("section"),
        ...main.querySelectorAll("[class*='grid']")
    ];

    const uniqueReveals = [...new Set(revealCandidates)].filter((element) => {
        if (heroScope && (element === heroScope || heroScope.contains(element))) return false;
        if (element.closest("[data-g-lightbox]")) return false;
        return true;
    }).slice(0, 48);

    uniqueReveals.forEach((element) => {
        element.dataset.nacsReveal = "1";

        const directChildren = [...element.children].filter((child) => {
            return child.matches("article, a, [class*='card']");
        }).slice(0, 8);

        directChildren.forEach((child, index) => {
            child.dataset.nacsStagger = "1";
            child.style.setProperty("--nacs-motion-delay", `${Math.min(index * 45, 180)}ms`);
        });
    });

    if (reduceQuery.matches || !("IntersectionObserver" in window)) {
        uniqueReveals.forEach((element) => element.classList.add("is-visible"));
    } else {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add("is-visible");
                observer.unobserve(entry.target);
            });
        }, {
            threshold: 0.08,
            rootMargin: "0px 0px -7% 0px"
        });

        uniqueReveals.forEach((element) => observer.observe(element));
    }

    requestAnimationFrame(() => {
        requestAnimationFrame(() => body.classList.add("nacs-motion-entered"));
    });
})();
