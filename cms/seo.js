'use strict';

const csrf = document.querySelector('meta[name="cms-csrf"]')?.content || '';
const list = document.getElementById('seo-list');
const form = document.getElementById('seo-form');
const save = document.getElementById('save-seo');
const heading = document.getElementById('seo-heading');
const statusEl = document.getElementById('seo-status');
let currentPath = '';
let expectedCanonical = '';

async function request(url, options = {}) {
  const response = await fetch(url, { credentials: 'same-origin', ...options });
  let data = {};
  try { data = await response.json(); } catch (_) { data = { ok: false, error: 'Invalid server response.' }; }
  if (response.status === 401) { location.href = '/cms/'; throw new Error('Authentication required.'); }
  if (!response.ok || data.ok === false) { const error = new Error(data.error || `Request failed (${response.status}).`); error.status = response.status; throw error; }
  return data;
}

function setStatus(message, tone = '') { statusEl.textContent = message; statusEl.dataset.tone = tone; }
function value(id) { return document.getElementById(id).value; }
function checked(id) { return document.getElementById(id).checked; }
function setValue(id, next) { document.getElementById(id).value = next ?? ''; }
function setChecked(id, next) { document.getElementById(id).checked = Boolean(next); }

function syncModes() {
  const canonicalCustom = value('seo-canonical-mode') === 'custom';
  const canonical = document.getElementById('seo-canonical'); canonical.disabled = !canonicalCustom; if (!canonicalCustom) canonical.value = expectedCanonical;
  const socialCustom = value('seo-social-mode') === 'custom';
  for (const id of ['seo-og-title', 'seo-og-description', 'seo-twitter-title', 'seo-twitter-description']) document.getElementById(id).disabled = !socialCustom;
  if (!socialCustom) {
    setValue('seo-og-title', value('seo-title')); setValue('seo-og-description', value('seo-description'));
    setValue('seo-twitter-title', value('seo-title')); setValue('seo-twitter-description', value('seo-description'));
  }
}

function fill(page) {
  currentPath = page.path; expectedCanonical = page.expectedCanonical || ''; heading.textContent = page.label || page.path;
  const seo = page.seo || {}; const controls = page.controls || {};
  setValue('seo-title', seo.title || ''); setValue('seo-description', seo.description || ''); setValue('seo-canonical', seo.canonical || expectedCanonical);
  setChecked('seo-index', controls.index !== false); setChecked('seo-follow', controls.follow !== false); setChecked('seo-archive', controls.archive !== false);
  setValue('seo-snippet', controls.snippetLimit ?? -1); setValue('seo-image-preview', controls.imagePreview || 'large'); setValue('seo-video-preview', controls.videoPreviewLimit ?? -1);
  setValue('seo-canonical-mode', controls.canonicalMode || 'self'); setValue('seo-social-mode', controls.socialMode || 'inherit');
  setValue('seo-og-title', seo.ogTitle || seo.title || ''); setValue('seo-og-description', seo.ogDescription || seo.description || '');
  setValue('seo-twitter-title', seo.twitterTitle || seo.title || ''); setValue('seo-twitter-description', seo.twitterDescription || seo.description || '');
  document.getElementById('seo-expected').textContent = `Expected self-canonical: ${expectedCanonical}`; form.hidden = false; save.disabled = false; syncModes();
}

async function loadIndex(preferred = '') {
  const data = await request('/api/cms-seo.php'); list.replaceChildren();
  for (const page of data.pages || []) {
    const button = document.createElement('button'); button.type = 'button'; button.textContent = page.label || page.path; button.dataset.path = page.path;
    if (page.path === preferred) button.setAttribute('aria-current', 'true'); button.addEventListener('click', () => loadPage(page.path)); list.append(button);
  }
  if (!(data.pages || []).length) { const empty = document.createElement('p'); empty.className = 'empty-list'; empty.textContent = 'No SEO-editable HTML targets found.'; list.append(empty); }
}

async function loadPage(path) {
  setStatus('Loading…');
  try { const data = await request(`/api/cms-seo.php?path=${encodeURIComponent(path)}`); fill(data.page); await loadIndex(path); setStatus(''); }
  catch (error) { setStatus(error.message, 'error'); }
}

function payload() {
  return {
    path: currentPath,
    seo: { title: value('seo-title'), description: value('seo-description'), canonical: value('seo-canonical'), ogTitle: value('seo-og-title'), ogDescription: value('seo-og-description'), twitterTitle: value('seo-twitter-title'), twitterDescription: value('seo-twitter-description') },
    controls: { index: checked('seo-index'), follow: checked('seo-follow'), archive: checked('seo-archive'), snippetLimit: Number(value('seo-snippet')), imagePreview: value('seo-image-preview'), videoPreviewLimit: Number(value('seo-video-preview')), canonicalMode: value('seo-canonical-mode'), socialMode: value('seo-social-mode') },
  };
}

save?.addEventListener('click', async () => {
  if (!currentPath) return; save.disabled = true; setStatus('Saving…');
  try { const data = await request('/api/cms-seo.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CMS-CSRF': csrf }, body: JSON.stringify(payload()) }); fill(data.page); await loadIndex(currentPath); setStatus('SEO saved and projected.', 'success'); }
  catch (error) { setStatus(error.message, 'error'); }
  finally { save.disabled = false; }
});

for (const id of ['seo-canonical-mode', 'seo-social-mode']) document.getElementById(id)?.addEventListener('change', syncModes);
for (const id of ['seo-title', 'seo-description']) document.getElementById(id)?.addEventListener('input', () => { if (value('seo-social-mode') === 'inherit') syncModes(); });
document.getElementById('logout')?.addEventListener('click', async () => { try { await request('/api/cms-auth.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CMS-CSRF': csrf }, body: JSON.stringify({ action: 'logout' }) }); } finally { location.href = '/cms/'; } });

loadIndex().catch((error) => setStatus(error.message, 'error'));
