(()=>{"use strict";
const h=document.querySelector("[data-g-header]"),b=document.querySelector("[data-g-menu]"),m=document.querySelector("[data-g-mobile]");
const sh=()=>h&&h.classList.toggle("shadow",scrollY>16);sh();addEventListener("scroll",sh,{passive:true});
if(b&&m){const c=()=>{b.setAttribute("aria-expanded","false");m.hidden=true};b.addEventListener("click",()=>{const x=b.getAttribute("aria-expanded")==="true";b.setAttribute("aria-expanded",x?"false":"true");m.hidden=x});m.addEventListener("click",e=>{if(e.target.closest("a"))c()});addEventListener("resize",()=>{if(innerWidth>1024)c()})}
const rs=[...document.querySelectorAll("[data-g-reveal]")];if(matchMedia("(prefers-reduced-motion: reduce)").matches||!("IntersectionObserver"in window))rs.forEach(n=>n.classList.add("show"));else{const io=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){e.target.classList.add("show");io.unobserve(e.target)}}),{threshold:.1});rs.forEach(n=>io.observe(n))}
const lb=document.querySelector("[data-g-lightbox]"),ops=[...document.querySelectorAll("[data-g-open]")];if(!lb||!ops.length)return;
const im=lb.querySelector("[data-g-image]"),t=lb.querySelector("[data-g-title]"),cat=lb.querySelector("[data-g-category]"),cap=lb.querySelector("[data-g-caption]"),cr=lb.querySelector("[data-g-credit]"),cl=lb.querySelector("[data-g-close]"),pr=lb.querySelector("[data-g-prev]"),nx=lb.querySelector("[data-g-next]");let i=0,last=null;
const render=n=>{i=(n+ops.length)%ops.length;const o=ops[i];im.src=o.dataset.image||"";im.alt=o.dataset.alt||"";t.textContent=o.dataset.title||"";cat.textContent=o.dataset.category||"";cap.textContent=o.dataset.caption||"";cr.textContent=o.dataset.credit||""};
const open=n=>{last=document.activeElement;render(n);lb.hidden=false;document.body.classList.add("lock");cl.focus()};
const close=()=>{lb.hidden=true;document.body.classList.remove("lock");im.src="";if(last&&last.focus)last.focus()};
ops.forEach((o,n)=>o.addEventListener("click",()=>open(n)));cl.addEventListener("click",close);pr.addEventListener("click",()=>render(i-1));nx.addEventListener("click",()=>render(i+1));lb.addEventListener("click",e=>{if(e.target===lb)close()});document.addEventListener("keydown",e=>{if(lb.hidden)return;if(e.key==="Escape")close();if(e.key==="ArrowLeft")render(i-1);if(e.key==="ArrowRight")render(i+1)});
})();
;
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

;
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

;
