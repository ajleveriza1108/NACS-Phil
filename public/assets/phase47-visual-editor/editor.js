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
