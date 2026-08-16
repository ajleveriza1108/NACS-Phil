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

/* NACS-Phil Phase 45 R1.6 - accessible grouped mobile navigation.
   Active state is derived in the browser so the Blade partial stays parser-simple. */
(() => {
    "use strict";

    const menu = document.querySelector("[data-nacs11-mobile-nav]");
    const menuButton = document.querySelector("[data-nacs11-menu-button]");
    const groups = [...document.querySelectorAll("[data-nacs45-mobile-group]")];

    if (!menu || !menuButton || groups.length === 0) return;

    const normalizePath = (value) => {
        const cleaned = (value || "/").replace(/\/+$/, "");
        return cleaned === "" ? "/" : cleaned;
    };

    const currentPath = normalizePath(window.location.pathname);

    const setGroup = (group, expanded) => {
        const toggle = group.querySelector("[data-nacs45-mobile-group-toggle]");
        const panel = group.querySelector("[data-nacs45-mobile-group-panel]");
        if (!toggle || !panel) return;

        toggle.setAttribute("aria-expanded", expanded ? "true" : "false");
        panel.hidden = !expanded;
        group.classList.toggle("is-open", expanded);
    };

    const closeGroups = (except = null) => {
        groups.forEach((group) => {
            if (group !== except) setGroup(group, false);
        });
    };

    const markCurrentLocation = () => {
        let activeGroup = null;

        [...menu.querySelectorAll("a[href]")].forEach((link) => {
            let target;

            try {
                target = new URL(link.href, window.location.href);
            } catch {
                return;
            }

            if (target.origin !== window.location.origin) return;

            const exact = normalizePath(target.pathname) === currentPath;
            link.classList.toggle("is-active", exact);

            if (exact) {
                link.setAttribute("aria-current", "page");
                activeGroup = link.closest("[data-nacs45-mobile-group]") || activeGroup;
            } else {
                link.removeAttribute("aria-current");
            }
        });

        if (!activeGroup) {
            activeGroup = groups.find((group) => {
                const prefixes = (group.dataset.nacs45Prefixes || "")
                    .split(",")
                    .map((prefix) => normalizePath(prefix.trim()))
                    .filter(Boolean);

                return prefixes.some((prefix) =>
                    currentPath === prefix || currentPath.startsWith(`${prefix}/`)
                );
            }) || null;
        }

        groups.forEach((group) => {
            group.classList.toggle("is-active", group === activeGroup);
        });

        return activeGroup;
    };

    let activeGroup = markCurrentLocation();

    const openActiveGroup = () => {
        activeGroup = markCurrentLocation();

        if (!activeGroup) {
            closeGroups();
            return;
        }

        closeGroups(activeGroup);
        setGroup(activeGroup, true);
    };

    groups.forEach((group) => {
        const toggle = group.querySelector("[data-nacs45-mobile-group-toggle]");
        if (!toggle) return;

        toggle.addEventListener("click", () => {
            const expanding = toggle.getAttribute("aria-expanded") !== "true";
            closeGroups(expanding ? group : null);
            setGroup(group, expanding);
        });
    });

    menuButton.addEventListener("click", () => {
        requestAnimationFrame(() => {
            if (menu.hidden) {
                closeGroups();
            } else {
                openActiveGroup();
            }
        });
    });

    menu.addEventListener("click", (event) => {
        if (event.target.closest("a")) closeGroups();
    });

    window.addEventListener("resize", () => {
        if (window.innerWidth > 1050) closeGroups();
    });
})();
