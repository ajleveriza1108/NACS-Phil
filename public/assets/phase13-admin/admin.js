(() => {
    "use strict";

    const sidebar = document.querySelector("[data-p13-sidebar]");
    const search = document.querySelector("[data-p13-nav-search]");
    const navGroups = document.querySelector("[data-p13-nav-groups]");

    document.querySelectorAll(".cm-nav a.is-active").forEach((link) => {
        link.setAttribute("aria-current", "page");
    });

    document.querySelectorAll("table.p12-table").forEach((table) => {
        if (table.parentElement?.classList.contains("p13-table-scroll")) return;

        const wrapper = document.createElement("div");
        wrapper.className = "p13-table-scroll";
        wrapper.tabIndex = 0;
        wrapper.setAttribute("role", "region");
        wrapper.setAttribute("aria-label", "Scrollable administration table");

        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
    });

    if (sidebar) {
        const storageKey = "nacs-p13-sidebar-scroll";

        try {
            const saved = Number(sessionStorage.getItem(storageKey) || "0");
            if (Number.isFinite(saved) && saved > 0) sidebar.scrollTop = saved;

            sidebar.addEventListener("scroll", () => {
                sessionStorage.setItem(storageKey, String(sidebar.scrollTop));
            }, { passive: true });
        } catch (_) {
            // Session storage is optional. Navigation works without it.
        }
    }

    if (search && navGroups) {
        const headings = Array.from(navGroups.querySelectorAll("[data-p13-nav-heading]"));
        const navigations = Array.from(navGroups.querySelectorAll(".cm-nav"));
        const links = Array.from(navGroups.querySelectorAll(".cm-nav a"));

        const empty = document.createElement("div");
        empty.className = "p13-nav-empty";
        empty.textContent = "No administration tool matches that search.";
        empty.hidden = true;
        navGroups.appendChild(empty);

        const applySearch = () => {
            const query = search.value.trim().toLowerCase();
            let visibleCount = 0;

            links.forEach((link) => {
                const match = query === "" || link.textContent.toLowerCase().includes(query);
                link.hidden = !match;
                if (match) visibleCount += 1;
            });

            navigations.forEach((nav) => {
                const hasVisible = Array.from(nav.querySelectorAll("a")).some((link) => !link.hidden);
                nav.hidden = !hasVisible;
            });

            headings.forEach((heading) => {
                let sibling = heading.nextElementSibling;
                heading.hidden = !(sibling?.classList.contains("cm-nav") && !sibling.hidden);
            });

            empty.hidden = visibleCount !== 0;
        };

        search.addEventListener("input", applySearch);
        search.addEventListener("search", applySearch);
    }

    document.addEventListener("keydown", (event) => {
        if (event.key !== "Escape") return;

        const closeButton = document.querySelector("[data-cm-close]");
        if (sidebar?.classList.contains("is-open")) {
            closeButton?.click();
            document.querySelector("[data-cm-open]")?.focus();
        }
    });
})();
