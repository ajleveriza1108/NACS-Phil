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
;
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

;
(() => {
  'use strict';

  const acceptedHost = (hostname) => {
    const host = String(hostname || '').toLowerCase();
    return host === 'facebook.com'
      || host.endsWith('.facebook.com')
      || host === 'fb.watch'
      || host.endsWith('.fb.watch');
  };

  const normalizeFacebookUrl = (value) => {
    try {
      const url = new URL(String(value || '').trim());
      if (url.protocol !== 'https:' || !acceptedHost(url.hostname)) return null;
      if (url.username || url.password) return null;
      return url.toString();
    } catch {
      return null;
    }
  };

  const buildEmbedUrl = (facebookUrl) => {
    const query = new URLSearchParams({
      href: facebookUrl,
      show_text: 'false',
      width: '1200',
    });
    return `https://www.facebook.com/plugins/video.php?${query.toString()}`;
  };

  const input = document.querySelector('[data-facebook-url-input]');
  const frame = document.querySelector('[data-facebook-preview-frame]');
  const status = document.querySelector('[data-facebook-preview-status]');

  if (!input || !frame || !status) return;

  let timer = 0;

  const showEmpty = (message, detail) => {
    frame.replaceChildren();

    const empty = document.createElement('div');
    empty.className = 'p22-admin-preview__empty';

    const icon = document.createElement('span');
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = '\u25B6';

    const strong = document.createElement('strong');
    strong.textContent = message;

    const small = document.createElement('small');
    small.textContent = detail;

    empty.append(icon, strong, small);
    frame.appendChild(empty);
  };

  const render = () => {
    const facebookUrl = normalizeFacebookUrl(input.value);

    if (!facebookUrl) {
      status.textContent = input.value.trim()
        ? 'Invalid or unsupported Facebook URL'
        : 'Waiting for a Facebook link';

      showEmpty(
        input.value.trim() ? 'Preview unavailable' : 'No preview yet',
        input.value.trim()
          ? 'Use an HTTPS facebook.com or fb.watch public video link.'
          : 'A valid public Facebook video or live URL will appear here.'
      );
      return;
    }

    frame.replaceChildren();

    const iframe = document.createElement('iframe');
    iframe.src = buildEmbedUrl(facebookUrl);
    iframe.title = 'Facebook video preview';
    iframe.loading = 'lazy';
    iframe.allowFullscreen = true;
    iframe.referrerPolicy = 'strict-origin-when-cross-origin';
    iframe.setAttribute('allow', 'autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share');

    frame.appendChild(iframe);
    status.textContent = 'Facebook preview loaded';
  };

  const scheduleRender = () => {
    window.clearTimeout(timer);
    timer = window.setTimeout(render, 250);
  };

  input.addEventListener('input', scheduleRender);
  input.addEventListener('change', render);
  input.addEventListener('paste', scheduleRender);

  if (input.value.trim()) render();
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
(() => {
  const editor = document.querySelector('[data-ve-editor]');
  if (!editor) return;

  const frame = editor.querySelector('[data-ve-frame]');
  const wrap = editor.querySelector('[data-ve-frame-wrap]');
  const fields = [...editor.querySelectorAll('[data-ve-field]')];
  const focusX = editor.querySelector('[name="hero_image_focus_x"]');
  const focusY = editor.querySelector('[name="hero_image_focus_y"]');
  const zoom = editor.querySelector('[name="hero_image_zoom"]');
  const imageInput = editor.querySelector('[data-ve-image-input]');
  const fileMeta = editor.querySelector('[data-ve-file-meta]');
  let previewUrl = null;

  function fieldNodes(name) {
    try {
      return [...frame.contentDocument.querySelectorAll(`[data-visual-field="${CSS.escape(name)}"]`)];
    } catch {
      return [];
    }
  }

  function updateCounter(field) {
    const max = Number(field.dataset.max || field.maxLength || 0);
    const recommended = Number(field.dataset.recommended || max);
    const length = field.value.length;
    const box = field.closest('.ve-field');
    const count = box?.querySelector('[data-ve-count]');
    const status = box?.querySelector('[data-ve-fit]');

    if (count) count.textContent = `${length} / ${max}`;

    if (status) {
      status.classList.remove('is-near', 'is-over');
      if (length > max) {
        status.textContent = 'Too long for the locked content frame.';
        status.classList.add('is-over');
      } else if (length > recommended) {
        status.textContent = `Fits the hard limit, but shorter than ${recommended} characters is recommended.`;
        status.classList.add('is-near');
      } else {
        status.textContent = 'Comfortable fit for this locked frame.';
      }
    }
  }

  function updateField(field) {
    updateCounter(field);
    for (const node of fieldNodes(field.name)) {
      node.textContent = field.value;
    }
  }

  function applyImagePosition() {
    let doc;
    try { doc = frame.contentDocument; } catch { return; }
    if (!doc) return;

    const image = doc.querySelector('[data-visual-image="hero_image"]');
    if (!image) return;

    image.style.objectFit = 'cover';
    image.style.objectPosition = `${focusX?.value || 50}% ${focusY?.value || 50}%`;
    image.style.transform = `scale(${zoom?.value || 1})`;
    image.style.transformOrigin = 'center';
  }

  function wirePreview() {
    let doc;
    try { doc = frame.contentDocument; } catch { return; }
    if (!doc) return;

    const style = doc.createElement('style');
    style.textContent = `
      [data-visual-field]{outline:2px solid transparent;outline-offset:4px;cursor:pointer}
      [data-visual-field]:hover{outline-color:#c89a3d;background:rgba(255,244,208,.16)}
      [data-visual-image]{cursor:grab}
    `;
    doc.head.appendChild(style);

    doc.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', event => event.preventDefault());
    });

    doc.querySelectorAll('[data-visual-field]').forEach(node => {
      node.addEventListener('click', event => {
        event.preventDefault();
        event.stopPropagation();
        const name = node.getAttribute('data-visual-field');
        const input = editor.querySelector(`[name="${CSS.escape(name)}"]`);
        if (!input) return;
        input.focus({ preventScroll: false });
        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
      });
    });

    const heroImage = doc.querySelector('[data-visual-image="hero_image"]');
    if (heroImage && focusX && focusY) {
      let dragging = false;
      let startX = 0;
      let startY = 0;
      let startFocusX = 50;
      let startFocusY = 50;

      heroImage.addEventListener('pointerdown', event => {
        dragging = true;
        startX = event.clientX;
        startY = event.clientY;
        startFocusX = Number(focusX.value || 50);
        startFocusY = Number(focusY.value || 50);
        heroImage.setPointerCapture?.(event.pointerId);
        heroImage.style.cursor = 'grabbing';
        event.preventDefault();
      });

      heroImage.addEventListener('pointermove', event => {
        if (!dragging) return;
        const width = Math.max(1, heroImage.clientWidth);
        const height = Math.max(1, heroImage.clientHeight);
        const nextX = Math.max(0, Math.min(100, startFocusX - ((event.clientX - startX) / width) * 100));
        const nextY = Math.max(0, Math.min(100, startFocusY - ((event.clientY - startY) / height) * 100));
        focusX.value = String(Math.round(nextX));
        focusY.value = String(Math.round(nextY));
        const xValue = focusX.closest('.ve-slider')?.querySelector('[data-ve-slider-value]');
        const yValue = focusY.closest('.ve-slider')?.querySelector('[data-ve-slider-value]');
        if (xValue) xValue.textContent = focusX.value;
        if (yValue) yValue.textContent = focusY.value;
        applyImagePosition();
        event.preventDefault();
      });

      const stopDrag = event => {
        if (!dragging) return;
        dragging = false;
        heroImage.releasePointerCapture?.(event.pointerId);
        heroImage.style.cursor = 'grab';
      };

      heroImage.addEventListener('pointerup', stopDrag);
      heroImage.addEventListener('pointercancel', stopDrag);
    }

    fields.forEach(updateField);
    applyImagePosition();
  }

  fields.forEach(field => {
    field.addEventListener('input', () => updateField(field));
    updateCounter(field);
  });

  [focusX, focusY, zoom].filter(Boolean).forEach(control => {
    control.addEventListener('input', () => {
      const value = control.closest('.ve-slider')?.querySelector('[data-ve-slider-value]');
      if (value) value.textContent = control.value;
      applyImagePosition();
    });
  });

  imageInput?.addEventListener('change', () => {
    const file = imageInput.files?.[0];
    if (!file) return;

    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = URL.createObjectURL(file);

    const img = new Image();
    img.onload = () => {
      const mb = (file.size / 1024 / 1024).toFixed(2);
      const goodDimensions = img.naturalWidth >= 1200 && img.naturalHeight >= 750;
      const goodSize = file.size <= 5 * 1024 * 1024;

      if (fileMeta) {
        fileMeta.textContent = `${img.naturalWidth} x ${img.naturalHeight}px · ${mb} MB · ${goodDimensions && goodSize ? 'Ready to crop' : 'Check requirements'}`;
        fileMeta.classList.toggle('is-over', !(goodDimensions && goodSize));
      }

      try {
        const previewImage = frame.contentDocument.querySelector('[data-visual-image="hero_image"]');
        if (previewImage) {
          previewImage.src = previewUrl;
          applyImagePosition();
        }
      } catch {}
    };
    img.src = previewUrl;
  });

  editor.querySelectorAll('[data-ve-device]').forEach(button => {
    button.addEventListener('click', () => {
      editor.querySelectorAll('[data-ve-device]').forEach(item => item.classList.remove('is-active'));
      button.classList.add('is-active');
      wrap.dataset.device = button.dataset.veDevice;
    });
  });

  frame?.addEventListener('load', wirePreview);
  if (frame?.contentDocument?.readyState === 'complete') wirePreview();
})();

;
