'use strict';

const csrf = document.querySelector('meta[name="cms-csrf"]')?.content || '';
const list = document.getElementById('seo-list');
const filter = document.getElementById('seo-filter');
const form = document.getElementById('seo-form');
const save = document.getElementById('save-seo');
const heading = document.getElementById('seo-heading');
const editor = document.querySelector('.seo-editor');
const statusEl = document.getElementById('seo-status');
const qualityHeading = document.getElementById('seo-quality-heading');
const qualitySummary = document.getElementById('seo-quality-summary');
const siteFindings = document.getElementById('seo-site-findings');
const pageQuality = document.getElementById('seo-page-quality');
const pageScore = document.getElementById('seo-page-score');
const pageFindings = document.getElementById('seo-page-findings');
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

function findingNode(issue) {
  const item = document.createElement('article'); item.className = 'revision-item'; item.dataset.severity = issue?.severity || 'info';
  const title = document.createElement('strong'); title.textContent = String(issue?.severity || 'info').toUpperCase();
  const message = document.createElement('p'); message.textContent = issue?.message || issue?.code || 'SEO finding';
  item.append(title, message); return item;
}
function renderSiteQuality(data) {
  const summary = data?.summary || {}; const errors = Number(summary.errors || 0); const warnings = Number(summary.warnings || 0); const score = Number(summary.score ?? 0); const open = errors + warnings;
  qualityHeading.textContent = errors ? `${errors} search error${errors === 1 ? '' : 's'} need attention` : warnings ? `${warnings} search warning${warnings === 1 ? '' : 's'}` : 'Search surface is clear';
  qualitySummary.textContent = `${Number(summary.pages || 0)} search pages · ${score}% score · ${open} search findings · ${Number(summary.sitemapUrls || 0)} sitemap URLs · ${Number(summary.managedPages ?? data?.pages?.length ?? 0)} managed pages`;
  siteFindings.replaceChildren(); for (const issue of data?.siteFindings || []) siteFindings.append(findingNode(issue));
}
function renderPageQuality(page) {
  const issues = page?.issues || []; pageQuality.hidden = false; pageScore.textContent = `${Number(page?.score ?? 100)}% · ${issues.length} finding${issues.length === 1 ? '' : 's'}${page?.indexable === false ? ' · not indexed' : ''}`; pageFindings.replaceChildren();
  if (!issues.length) { const p = document.createElement('p'); p.className = 'muted'; p.textContent = 'No page-level SEO findings.'; pageFindings.append(p); return; }
  for (const issue of issues) pageFindings.append(findingNode(issue));
}

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
  currentPath = page.path; expectedCanonical = page.expectedCanonical || ''; heading.textContent = page.label || page.path; editor.dataset.seoState = 'selected';
  const seo = page.seo || {}; const controls = page.controls || {};
  setValue('seo-title', seo.title || ''); setValue('seo-description', seo.description || ''); setValue('seo-canonical', seo.canonical || expectedCanonical);
  setChecked('seo-index', controls.index !== false); setChecked('seo-follow', controls.follow !== false); setChecked('seo-archive', controls.archive !== false);
  setValue('seo-snippet', controls.snippetLimit ?? -1); setValue('seo-image-preview', controls.imagePreview || 'large'); setValue('seo-video-preview', controls.videoPreviewLimit ?? -1);
  setValue('seo-canonical-mode', controls.canonicalMode || 'self'); setValue('seo-social-mode', controls.socialMode || 'inherit');
  setValue('seo-og-title', seo.ogTitle || seo.title || ''); setValue('seo-og-description', seo.ogDescription || seo.description || '');
  setValue('seo-twitter-title', seo.twitterTitle || seo.title || ''); setValue('seo-twitter-description', seo.twitterDescription || seo.description || '');
  document.getElementById('seo-expected').textContent = `Expected self-canonical: ${expectedCanonical}`; form.hidden = false; save.disabled = false; syncModes(); renderPageQuality(page);
}

function filterPages() {
  const term = (filter?.value || '').trim().toLowerCase();
  for (const button of list.querySelectorAll('button[data-path]')) {
    const haystack = `${button.dataset.path || ''} ${button.textContent || ''}`.toLowerCase();
    button.hidden = term !== '' && !haystack.includes(term);
  }
}

async function loadIndex(preferred = '') {
  const data = await request('/api/cms-seo.php'); renderSiteQuality(data); list.replaceChildren();
  for (const page of data.pages || []) {
    const button = document.createElement('button'); button.type = 'button'; const count = (page.issues || []).length; button.textContent = `${page.label || page.path}${count ? ` · ${count}` : ''}${page.indexable === false ? ' · not indexed' : ''}`; button.dataset.path = page.path;
    if (page.path === preferred) button.setAttribute('aria-current', 'true'); button.addEventListener('click', () => loadPage(page.path)); list.append(button);
  }
  if (!(data.pages || []).length) { const empty = document.createElement('p'); empty.className = 'empty-list'; empty.textContent = 'No SEO-editable HTML targets found.'; list.append(empty); }
  filterPages();
}

async function loadPage(path) {
  setStatus('Loading…');
  try { const data = await request(`/api/cms-seo.php?path=${encodeURIComponent(path)}`); renderSiteQuality(data); fill(data.page); await loadIndex(path); setStatus(''); }
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
  try { const data = await request('/api/cms-seo.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CMS-CSRF': csrf }, body: JSON.stringify(payload()) }); renderSiteQuality(data); fill(data.page); await loadIndex(currentPath); setStatus('SEO saved, projected, and re-audited.', 'success'); }
  catch (error) { setStatus(error.message, 'error'); }
  finally { save.disabled = false; }
});

filter?.addEventListener('input', filterPages);
for (const id of ['seo-canonical-mode', 'seo-social-mode']) document.getElementById(id)?.addEventListener('change', syncModes);
for (const id of ['seo-title', 'seo-description']) document.getElementById(id)?.addEventListener('input', () => { if (value('seo-social-mode') === 'inherit') syncModes(); });
document.getElementById('logout')?.addEventListener('click', async () => { try { await request('/api/cms-auth.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CMS-CSRF': csrf }, body: JSON.stringify({ action: 'logout' }) }); } finally { location.href = '/cms/'; } });

loadIndex().catch((error) => setStatus(error.message, 'error'));
