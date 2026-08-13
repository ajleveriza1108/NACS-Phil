(() => {
    "use strict";

    const sidebar = document.querySelector("[data-cm-sidebar]");
    const openButton = document.querySelector("[data-cm-open]");
    const closeButton = document.querySelector("[data-cm-close]");
    const backdrop = document.querySelector("[data-cm-backdrop]");

    const closeMenu = () => {
        if (sidebar) sidebar.classList.remove("is-open");
        if (backdrop) backdrop.hidden = true;
    };

    const openMenu = () => {
        if (sidebar) sidebar.classList.add("is-open");
        if (backdrop) backdrop.hidden = false;
    };

    openButton?.addEventListener("click", openMenu);
    closeButton?.addEventListener("click", closeMenu);
    backdrop?.addEventListener("click", closeMenu);

    document.querySelectorAll("[data-cm-file]").forEach((input) => {
        input.addEventListener("change", () => {
            const container = input.closest(".cm-upload-box") || input.parentElement;
            const label = container?.querySelector("[data-cm-file-name]");
            if (!label) return;
            label.textContent = input.files?.[0]?.name || "No new photo selected";
        });
    });

    document.querySelectorAll("[data-cm-confirm]").forEach((form) => {
        form.addEventListener("submit", (event) => {
            const message = form.getAttribute("data-cm-confirm") || "Continue with this action?";
            if (!window.confirm(message)) event.preventDefault();
        });
    });

    document.querySelectorAll("[data-cm-form]").forEach((form) => {
        let dirty = false;

        form.addEventListener("input", () => {
            dirty = true;
            form.classList.add("cm-form-dirty");
        });

        form.addEventListener("change", () => {
            dirty = true;
            form.classList.add("cm-form-dirty");
        });

        form.addEventListener("submit", () => {
            dirty = false;
        });

        window.addEventListener("beforeunload", (event) => {
            if (!dirty) return;
            event.preventDefault();
            event.returnValue = "";
        });
    });
})();