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
