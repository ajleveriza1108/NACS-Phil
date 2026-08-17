/*
 * NACS-Phil current Homepage editor runtime.
 * Visual-editor foundation followed by the Premium Smart Editor extension.
 * Active layouts intentionally use only the semantic assets/current namespace.
 */

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


(() => {
  const root=document.querySelector('[data-ve-editor]'); if(!root) return;
  const form=root.querySelector('form.ve-panel'); if(!form) return;
  const fields=[...root.querySelectorAll('[data-ve-field]')];
  const hidden=new Set([...root.querySelectorAll('[data-ve-hidden-initial]')].map(x=>x.value).filter(Boolean));
  const hiddenHost=root.querySelector('[data-ve-hidden-host]');
  const hiddenList=root.querySelector('[data-ve-hidden-list]');
  const status=root.querySelector('[data-ve-premium-status]');
  const toast=root.querySelector('[data-ve-premium-toast]');
  const draftBanner=root.querySelector('[data-ve-draft-banner]');
  const revisionsData=root.querySelector('[data-ve-revisions-data]');
  const DRAFT='nacs.ve.home.draft.v2';
  let allowLeave=false, dirty=false, history=[], index=-1, applying=false, timer=null;

  const vals=()=>Object.fromEntries([...form.elements].filter(e=>e.name && !['hidden_fields[]','_token','_method','hero_image_authorized','hero_image'].includes(e.name)).map(e=>[e.name,e.value]));
  const snap=()=>({values:vals(),hidden:[...hidden].sort(),savedAt:Date.now()});
  const initial=snap();
  const same=(a,b)=>JSON.stringify({values:a.values,hidden:a.hidden})===JSON.stringify({values:b.values,hidden:b.hidden});
  const mark=()=>{dirty=!same(snap(),initial); if(status){status.textContent=dirty?'Unsaved changes':'Saved';status.classList.toggle('is-dirty',dirty);}};
  const draft=()=>{try{localStorage.setItem(DRAFT,JSON.stringify(snap()));}catch{}};
  const schedule=()=>{clearTimeout(timer);timer=setTimeout(draft,350);};
  const label=name=>root.querySelector(`[data-ve-field][name="${CSS.escape(name)}"]`)?.closest('.ve-field')?.querySelector('[data-ve-field-label]')?.textContent?.trim()||name;

  function syncHidden(){
    if(hiddenHost){hiddenHost.innerHTML='';[...hidden].sort().forEach(name=>{const i=document.createElement('input');i.type='hidden';i.name='hidden_fields[]';i.value=name;hiddenHost.appendChild(i);});}
    fields.forEach(f=>{f.closest('.ve-field')?.classList.toggle('is-hidden-field',hidden.has(f.name));try{root.querySelector('[data-ve-frame]')?.contentDocument?.querySelectorAll(`[data-visual-field="${CSS.escape(f.name)}"]`).forEach(n=>n.toggleAttribute('data-ve-editor-hidden',hidden.has(f.name)));}catch{}});
    if(hiddenList){hiddenList.innerHTML=''; if(!hidden.size) hiddenList.innerHTML='<p>No hidden elements.</p>'; [...hidden].sort().forEach(name=>{const d=document.createElement('div');d.className='ve58-hidden-row';d.innerHTML='<span></span><button type="button" data-ve-restore-hidden>Restore</button>';d.querySelector('span').textContent=label(name);d.querySelector('button').dataset.field=name;hiddenList.appendChild(d);});}
  }

  function apply(s){
    applying=true; Object.entries(s.values||{}).forEach(([n,v])=>{const e=form.elements.namedItem(n);if(!e||e.type==='file')return;e.value=String(v??'');e.dispatchEvent(new Event('input',{bubbles:true}));});
    hidden.clear();(s.hidden||[]).forEach(n=>{if(root.querySelector(`[data-ve-field][name="${CSS.escape(n)}"]`))hidden.add(n);});syncHidden();applying=false;mark();schedule();
  }
  function push(){if(applying)return;const s=snap();if(index>=0&&same(history[index],s))return;history=history.slice(0,index+1);history.push(s);if(history.length>80)history.shift();index=history.length-1;buttons();}
  function buttons(){const u=root.querySelector('[data-ve-undo]'),r=root.querySelector('[data-ve-redo]');if(u)u.disabled=index<=0;if(r)r.disabled=index>=history.length-1;}
  function undo(){if(index<=0)return;index--;apply(history[index]);buttons();}
  function redo(){if(index>=history.length-1)return;index++;apply(history[index]);buttons();}
  function note(t,canUndo=false){if(!toast)return;toast.innerHTML='';const s=document.createElement('span');s.textContent=t;toast.appendChild(s);if(canUndo){const b=document.createElement('button');b.type='button';b.textContent='Undo';b.onclick=undo;toast.appendChild(b);}toast.hidden=false;setTimeout(()=>toast.hidden=true,5500);}

  root.addEventListener('click',e=>{
    const h=e.target.closest('[data-ve-hide]');if(h){if(!hidden.has(h.dataset.field)){push();hidden.add(h.dataset.field);syncHidden();push();mark();schedule();note('Section hidden - Undo',true);}return;}
    const r=e.target.closest('[data-ve-restore-hidden]');if(r){push();hidden.delete(r.dataset.field);syncHidden();push();mark();schedule();note('Element restored');return;}
    if(e.target.closest('[data-ve-restore-all]')){push();hidden.clear();syncHidden();push();mark();schedule();note('All hidden elements restored');return;}
    if(e.target.closest('[data-ve-undo]'))return undo();
    if(e.target.closest('[data-ve-redo]'))return redo();
    if(e.target.closest('[data-ve-reset-unsaved]')){if(confirm('Discard all unsaved changes and return to the last published version?')){apply(initial);history=[initial];index=0;try{localStorage.removeItem(DRAFT);}catch{}mark();note('Unsaved changes reset');}return;}
    if(e.target.closest('[data-ve-save-draft]')){draft();note('Recovery draft saved on this device');return;}
    if(e.target.closest('[data-ve-restore-draft]')){try{const s=JSON.parse(localStorage.getItem(DRAFT)||'null');if(s){apply(s);push();}}catch{}if(draftBanner)draftBanner.hidden=true;note('Recovery draft restored');return;}
    if(e.target.closest('[data-ve-discard-draft]')){try{localStorage.removeItem(DRAFT);}catch{}if(draftBanner)draftBanner.hidden=true;note('Recovery draft discarded');return;}
    const p=e.target.closest('[data-ve-preview-revision]');if(p&&revisionsData){try{const all=JSON.parse(revisionsData.textContent||'[]'),rev=all.find(x=>x.key===p.dataset.revisionKey);if(rev){apply({values:{...(rev.content||{}),style_overrides:JSON.stringify(rev.styles||{})},hidden:rev.hidden});push();note('Revision loaded as unsaved preview');}}catch{}return;}
  });

  fields.forEach(f=>f.addEventListener('input',()=>{if(applying)return;push();mark();schedule();}));
  form.querySelectorAll('input[type="range"]').forEach(f=>f.addEventListener('input',()=>{if(applying)return;push();mark();schedule();}));
  root.querySelector('[data-ve-style-overrides]')?.addEventListener('input',()=>{if(applying)return;push();mark();schedule();});
  root.querySelector('[data-ve-image-input]')?.addEventListener('change',()=>{dirty=true;if(status){status.textContent='Unsaved changes';status.classList.add('is-dirty');}});

  document.addEventListener('keydown',e=>{if(!(e.ctrlKey||e.metaKey))return;const k=e.key.toLowerCase();if(k==='z'&&!e.shiftKey){e.preventDefault();undo();note('Undo - Ctrl+Z');}else if(k==='y'||(k==='z'&&e.shiftKey)){e.preventDefault();redo();note('Redo - Ctrl+Y / Ctrl+Shift+Z');}});
  window.addEventListener('beforeunload',e=>{if(!dirty||allowLeave)return;e.preventDefault();e.returnValue='';});
  document.querySelectorAll('a[href]').forEach(a=>a.addEventListener('click',e=>{if(!dirty||allowLeave||a.target==='_blank')return;if(!confirm('You have Unsaved changes. Leave without publishing?'))e.preventDefault();}));
  form.addEventListener('submit',()=>{allowLeave=true;try{localStorage.removeItem(DRAFT);}catch{}});

  const frame=root.querySelector('[data-ve-frame]');frame?.addEventListener('load',()=>{try{const st=frame.contentDocument.createElement('style');st.textContent='[data-ve-editor-hidden]{display:none!important}';frame.contentDocument.head.appendChild(st);syncHidden();}catch{}});
  try{const d=JSON.parse(localStorage.getItem(DRAFT)||'null');if(d&&!same(d,initial)&&draftBanner)draftBanner.hidden=false;}catch{}
  history=[initial];index=0;syncHidden();mark();buttons();
})();
/* Phase 59 - professional responsive inspector + smart text fit */
(() => {
  const root = document.querySelector('[data-ve-editor]');
  if (!root) return;

  const frame = root.querySelector('[data-ve-frame]');
  const store = root.querySelector('[data-ve-style-overrides]');
  const controlsHost = root.querySelector('[data-ve-pro-controls]');
  const selectedLabel = root.querySelector('[data-ve-selected-label]');
  const selectedField = root.querySelector('[data-ve-selected-field]');
  const fitHealth = root.querySelector('[data-ve-fit-health]');
  const controlNodes = [...root.querySelectorAll('[data-ve-pro-control]')];
  const formFields = [...root.querySelectorAll('[data-ve-field]')];
  if (!frame || !store) return;

  const numeric = new Set(['font_size','line_height','letter_spacing','max_width','padding_x','padding_y','min_height']);
  let styles = parseStyles(store.value);
  let selected = null;
  let scope = 'base';
  let device = 'desktop';
  let resizeObserver = null;
  let scanTimer = null;

  function parseStyles(value) {
    try {
      const data = JSON.parse(value || '{}');
      return data && typeof data === 'object' && !Array.isArray(data) ? data : {};
    } catch {
      return {};
    }
  }

  function labelFor(name) {
    return root.querySelector(`[data-ve-field][name="${CSS.escape(name)}"]`)
      ?.closest('.ve-field')
      ?.querySelector('[data-ve-field-label]')
      ?.textContent
      ?.trim() || name;
  }

  function fieldNodes(name) {
    try {
      return [...frame.contentDocument.querySelectorAll(`[data-visual-field="${CSS.escape(name)}"]`)];
    } catch {
      return [];
    }
  }

  function scopeStyle(name, which = scope) {
    return styles?.[name]?.[which] || {};
  }

  function effectiveStyle(name) {
    const result = {...(styles?.[name]?.base || {})};
    if (device === 'tablet' || device === 'phone') Object.assign(result, styles?.[name]?.tablet || {});
    if (device === 'phone') Object.assign(result, styles?.[name]?.phone || {});
    return result;
  }

  function clearControlled(node) {
    [
      'fontSize','lineHeight','letterSpacing','maxWidth','paddingLeft','paddingRight',
      'paddingTop','paddingBottom','minHeight','fontWeight','textAlign','whiteSpace',
      'overflowWrap','display','boxSizing'
    ].forEach(prop => node.style[prop] = '');
    try { node.style.textWrap = ''; } catch {}
  }

  function applyNodeStyle(node, rules) {
    clearControlled(node);
    let frameAdjusted = false;

    if (rules.font_size !== undefined) node.style.fontSize = `${rules.font_size}px`;
    if (rules.line_height !== undefined) node.style.lineHeight = String(rules.line_height);
    if (rules.letter_spacing !== undefined) node.style.letterSpacing = `${rules.letter_spacing}px`;

    if (Number(rules.max_width || 0) > 0) {
      node.style.maxWidth = `${rules.max_width}px`;
      frameAdjusted = true;
    }

    if (rules.padding_x !== undefined) {
      node.style.paddingLeft = `${rules.padding_x}px`;
      node.style.paddingRight = `${rules.padding_x}px`;
      frameAdjusted = true;
    }

    if (rules.padding_y !== undefined) {
      node.style.paddingTop = `${rules.padding_y}px`;
      node.style.paddingBottom = `${rules.padding_y}px`;
      frameAdjusted = true;
    }

    if (Number(rules.min_height || 0) > 0) {
      node.style.minHeight = `${rules.min_height}px`;
      frameAdjusted = true;
    }

    if (rules.font_weight) node.style.fontWeight = rules.font_weight;
    if (rules.text_align) node.style.textAlign = rules.text_align;

    if (rules.flow === 'nowrap') {
      node.style.whiteSpace = 'nowrap';
      try { node.style.textWrap = 'nowrap'; } catch {}
      node.style.overflowWrap = 'normal';
    } else if (rules.flow === 'balance') {
      node.style.whiteSpace = 'normal';
      try { node.style.textWrap = 'balance'; } catch {}
    } else if (rules.flow === 'normal') {
      node.style.whiteSpace = 'normal';
      try { node.style.textWrap = 'wrap'; } catch {}
      node.style.overflowWrap = 'anywhere';
    }

    if (frameAdjusted) {
      node.style.display = 'inline-block';
      node.style.boxSizing = 'border-box';
    }
  }

  function applyAll() {
    try {
      frame.contentDocument.querySelectorAll('[data-visual-field]').forEach(node => {
        applyNodeStyle(node, effectiveStyle(node.getAttribute('data-visual-field')));
      });
    } catch {}
    markSelection();
    scheduleScan();
  }

  function persist() {
    store.value = JSON.stringify(styles);
    store.dispatchEvent(new Event('input', {bubbles:true}));
    applyAll();
  }

  function ensureScope(name) {
    styles[name] ||= {};
    styles[name][scope] ||= {};
    return styles[name][scope];
  }

  function compact(name) {
    if (!styles[name]) return;
    for (const key of ['base','tablet','phone']) {
      if (styles[name][key] && !Object.keys(styles[name][key]).length) delete styles[name][key];
    }
    if (!Object.keys(styles[name]).length) delete styles[name];
  }

  function computedDefaults(name) {
    const node = fieldNodes(name)[0];
    if (!node) return {};
    const view = frame.contentWindow;
    const computed = view?.getComputedStyle(node);
    if (!computed) return {};
    return {
      font_size: parseFloat(computed.fontSize) || 16,
      line_height: parseFloat(computed.lineHeight) || 1.2,
      letter_spacing: parseFloat(computed.letterSpacing) || 0,
      font_weight: String(computed.fontWeight || ''),
      text_align: computed.textAlign || 'left'
    };
  }

  function updateControls() {
    if (controlsHost) controlsHost.disabled = !selected;
    if (!selected) return;

    const saved = scopeStyle(selected);
    const defaults = computedDefaults(selected);

    controlNodes.forEach(control => {
      const prop = control.dataset.prop;
      const value = saved[prop];

      if (control.tagName === 'SELECT') {
        control.value = value === undefined ? '' : String(value);
      } else {
        if (value !== undefined) {
          control.value = String(value);
        } else if (defaults[prop] !== undefined && ['font_size','line_height','letter_spacing'].includes(prop)) {
          control.value = String(defaults[prop]);
        } else {
          control.value = '0';
        }
      }

      updateOutput(prop, value, control);
    });
  }

  function updateOutput(prop, value, control) {
    const output = root.querySelector(`[data-ve-pro-output="${CSS.escape(prop)}"]`);
    if (!output) return;

    if (value === undefined || value === '') {
      output.textContent = ['max_width','min_height'].includes(prop) ? 'Auto' : 'Theme';
      return;
    }

    const suffix = ['font_size','letter_spacing','max_width','padding_x','padding_y','min_height'].includes(prop) ? 'px' : '';
    output.textContent = `${value}${suffix}`;
  }

  function markSelection() {
    try {
      frame.contentDocument.querySelectorAll('.ve59-selected').forEach(node => node.classList.remove('ve59-selected'));
      if (selected) fieldNodes(selected).forEach(node => node.classList.add('ve59-selected'));
    } catch {}
  }

  function selectField(name) {
    if (!name || !root.querySelector(`[data-ve-field][name="${CSS.escape(name)}"]`)) return;
    selected = name;
    if (selectedLabel) selectedLabel.textContent = labelFor(name);
    if (selectedField) selectedField.textContent = `${name} · ${device === 'desktop' ? 'desktop base' : device}`;
    markSelection();
    updateControls();
  }

  function setScope(next) {
    if (!['base','tablet','phone'].includes(next)) return;
    scope = next;
    root.querySelectorAll('[data-ve-style-scope]').forEach(button => button.classList.toggle('is-active', button.dataset.veStyleScope === scope));
    updateControls();
  }

  function hasFitIssue(node) {
    const parent = node.parentElement;
    if (!parent) return false;

    const nodeRect = node.getBoundingClientRect();
    const parentRect = parent.getBoundingClientRect();
    const horizontal = nodeRect.right > parentRect.right + 1 || nodeRect.left < parentRect.left - 1;
    const singleLineWrapped = node.dataset.visualFit === 'single-line' && node.getClientRects().length > 1;

    return horizontal || singleLineWrapped;
  }

  function scanFit() {
    let issues = [];
    try {
      const nodes = [...frame.contentDocument.querySelectorAll('[data-visual-field]')];
      nodes.forEach(node => {
        const issue = hasFitIssue(node);
        node.classList.toggle('ve59-fit-alert', issue);
        if (issue) issues.push(node.getAttribute('data-visual-field'));
      });
    } catch {}

    issues = [...new Set(issues)];

    if (fitHealth) {
      fitHealth.classList.toggle('has-alert', issues.length > 0);
      const strong = fitHealth.querySelector('strong');
      const small = fitHealth.querySelector('small');
      if (strong) strong.textContent = issues.length ? `${issues.length} element${issues.length === 1 ? '' : 's'} need fit attention` : 'Responsive fit looks good';
      if (small) small.textContent = issues.length ? 'Select an outlined element or use Fit All Alerts.' : 'No horizontal overflow or forced single-line wrapping detected.';
    }

    return issues;
  }

  function scheduleScan() {
    clearTimeout(scanTimer);
    scanTimer = setTimeout(scanFit, 80);
  }

  function measureTextWidth(node) {
    try {
      const doc = frame.contentDocument;
      const computed = frame.contentWindow.getComputedStyle(node);
      const canvas = doc.createElement('canvas');
      const context = canvas.getContext('2d');
      if (!context) return 0;
      context.font = `${computed.fontStyle} ${computed.fontWeight} ${computed.fontSize} ${computed.fontFamily}`;
      const text = (node.textContent || '').trim();
      const base = context.measureText(text).width;
      const spacing = parseFloat(computed.letterSpacing);
      return base + (Number.isFinite(spacing) ? Math.max(0, text.length - 1) * spacing : 0);
    } catch {
      return 0;
    }
  }

  function autoFit(name) {
    const node = fieldNodes(name)[0];
    if (!node) return false;

    const parent = node.parentElement;
    if (!parent) return false;

    const available = Math.max(40, parent.getBoundingClientRect().width - 2);
    const measured = measureTextWidth(node);
    const computed = frame.contentWindow.getComputedStyle(node);
    const currentSize = parseFloat(computed.fontSize) || 16;

    const rules = ensureScope(name);

    if (node.dataset.visualFit === 'single-line') {
      rules.flow = 'nowrap';
    }

    if (measured > available) {
      const next = Math.max(10, Math.min(currentSize, currentSize * (available / measured) * 0.96));
      rules.font_size = Math.round(next * 2) / 2;
    } else if (node.dataset.visualFit === 'single-line' && !rules.font_size) {
      rules.font_size = Math.round(currentSize * 2) / 2;
    }

    compact(name);
    persist();
    selectField(name);
    return true;
  }

  function fitAll() {
    const issues = scanFit();
    let count = 0;
    issues.forEach(name => { if (autoFit(name)) count++; });
    if (!count) {
      try {
        frame.contentDocument.querySelectorAll('[data-visual-fit="single-line"]').forEach(node => {
          if (autoFit(node.getAttribute('data-visual-field'))) count++;
        });
      } catch {}
    }
    scheduleScan();
  }

  controlNodes.forEach(control => {
    const eventName = control.tagName === 'SELECT' ? 'change' : 'input';
    control.addEventListener(eventName, () => {
      if (!selected) return;
      const prop = control.dataset.prop;
      const rules = ensureScope(selected);

      if (control.tagName === 'SELECT') {
        if (control.value === '') delete rules[prop];
        else rules[prop] = control.value;
      } else if (numeric.has(prop)) {
        rules[prop] = Number(control.value);
      }

      compact(selected);
      updateOutput(prop, rules[prop], control);
      persist();
    });
  });

  root.querySelectorAll('[data-ve-style-scope]').forEach(button => {
    button.addEventListener('click', () => setScope(button.dataset.veStyleScope));
  });

  root.querySelector('[data-ve-auto-fit]')?.addEventListener('click', () => {
    if (selected) autoFit(selected);
  });

  root.querySelector('[data-ve-auto-fit-all]')?.addEventListener('click', fitAll);

  root.querySelector('[data-ve-reset-style]')?.addEventListener('click', () => {
    if (!selected || !styles[selected]) return;
    delete styles[selected][scope];
    compact(selected);
    persist();
    updateControls();
  });

  root.querySelector('[data-ve-reset-style-all]')?.addEventListener('click', () => {
    if (!selected) return;
    delete styles[selected];
    persist();
    updateControls();
  });

  formFields.forEach(field => {
    field.addEventListener('focus', () => selectField(field.name));
    field.addEventListener('input', scheduleScan);
  });

  store.addEventListener('input', () => {
    styles = parseStyles(store.value);
    applyAll();
    updateControls();
  });

  root.querySelectorAll('[data-ve-device]').forEach(button => {
    button.addEventListener('click', () => {
      device = button.dataset.veDevice || 'desktop';
      setScope(device === 'desktop' ? 'base' : device);
      if (selectedField && selected) selectedField.textContent = `${selected} · ${device === 'desktop' ? 'desktop base' : device}`;
      setTimeout(applyAll, 40);
    });
  });

  function wireFrame() {
    try {
      const doc = frame.contentDocument;
      doc.querySelectorAll('[data-visual-field]').forEach(node => {
        node.addEventListener('click', () => selectField(node.getAttribute('data-visual-field')));
      });

      resizeObserver?.disconnect();
      resizeObserver = new ResizeObserver(scheduleScan);
      doc.querySelectorAll('[data-visual-field]').forEach(node => resizeObserver.observe(node));
    } catch {}

    applyAll();

    const preferred = frame.contentDocument?.querySelector('[data-visual-field="why_2_title"]');
    if (!selected && preferred) selectField('why_2_title');
  }

  frame.addEventListener('load', wireFrame);
  if (frame.contentDocument?.readyState === 'complete') wireFrame();

  window.addEventListener('resize', scheduleScan);
})();
