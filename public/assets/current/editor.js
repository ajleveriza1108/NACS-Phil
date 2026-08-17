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
    const p=e.target.closest('[data-ve-preview-revision]');if(p&&revisionsData){try{const all=JSON.parse(revisionsData.textContent||'[]'),rev=all.find(x=>x.key===p.dataset.revisionKey);if(rev){apply({values:rev.content,hidden:rev.hidden});push();note('Revision loaded as unsaved preview');}}catch{}return;}
  });

  fields.forEach(f=>f.addEventListener('input',()=>{if(applying)return;push();mark();schedule();}));
  form.querySelectorAll('input[type="range"]').forEach(f=>f.addEventListener('input',()=>{if(applying)return;push();mark();schedule();}));
  root.querySelector('[data-ve-image-input]')?.addEventListener('change',()=>{dirty=true;if(status){status.textContent='Unsaved changes';status.classList.add('is-dirty');}});

  document.addEventListener('keydown',e=>{if(!(e.ctrlKey||e.metaKey))return;const k=e.key.toLowerCase();if(k==='z'&&!e.shiftKey){e.preventDefault();undo();note('Undo - Ctrl+Z');}else if(k==='y'||(k==='z'&&e.shiftKey)){e.preventDefault();redo();note('Redo - Ctrl+Y / Ctrl+Shift+Z');}});
  window.addEventListener('beforeunload',e=>{if(!dirty||allowLeave)return;e.preventDefault();e.returnValue='';});
  document.querySelectorAll('a[href]').forEach(a=>a.addEventListener('click',e=>{if(!dirty||allowLeave||a.target==='_blank')return;if(!confirm('You have Unsaved changes. Leave without publishing?'))e.preventDefault();}));
  form.addEventListener('submit',()=>{allowLeave=true;try{localStorage.removeItem(DRAFT);}catch{}});

  const frame=root.querySelector('[data-ve-frame]');frame?.addEventListener('load',()=>{try{const st=frame.contentDocument.createElement('style');st.textContent='[data-ve-editor-hidden]{display:none!important}';frame.contentDocument.head.appendChild(st);syncHidden();}catch{}});
  try{const d=JSON.parse(localStorage.getItem(DRAFT)||'null');if(d&&!same(d,initial)&&draftBanner)draftBanner.hidden=false;}catch{}
  history=[initial];index=0;syncHidden();mark();buttons();
})();
