'use strict';

const csrf = document.querySelector('meta[name="cms-csrf"]')?.content || '';
const form = document.getElementById('post-form');
const list = document.getElementById('post-list');
const save = document.getElementById('save-post');
const heading = document.getElementById('writing-heading');
const statusEl = document.getElementById('writing-status');
const stateEl = document.getElementById('post-state');
const revisionsCard = document.getElementById('revisions-card');
const revisionsRoot = document.getElementById('revision-list');
let current = null;
let originalSlug = '';
let expectedHash = '';

async function request(url, options = {}) {
  const response = await fetch(url, { credentials: 'same-origin', ...options });
  let data = {};
  try { data = await response.json(); } catch (_) { data = { ok: false, error: 'Invalid server response.' }; }
  if (response.status === 401) { location.href = '/cms/'; throw new Error('Authentication required.'); }
  if (!response.ok || data.ok === false) { const error = new Error(data.error || `Request failed (${response.status}).`); error.status = response.status; throw error; }
  return data;
}

function setStatus(message, tone = '') { statusEl.textContent = message; statusEl.dataset.tone = tone; }
function csv(value) { return String(value || '').split(',').map((v) => v.trim()).filter(Boolean); }
function value(id) { return document.getElementById(id).value; }
function setValue(id, next) { document.getElementById(id).value = next ?? ''; }

function postFromForm() {
  return {
    slug: value('post-slug'), title: value('post-title'), dek: value('post-dek'), category: value('post-category'), categoryLabel: value('post-category-label'),
    date: value('post-date'), status: value('post-status'), tags: csv(value('post-tags')), thesis: value('post-thesis'), related: csv(value('post-related')), body: value('post-body'),
    territoryImage: current?.territoryImage || '', featuredImage: current?.featuredImage || '', socialImage: current?.socialImage || '', imageAlt: current?.imageAlt || '', featured: Boolean(current?.featured),
  };
}

function fillForm(post) {
  current = post;
  originalSlug = post?.slug || '';
  expectedHash = post?.revisionHash || '';
  setValue('post-title', post?.title || ''); setValue('post-slug', post?.slug || ''); setValue('post-dek', post?.dek || '');
  setValue('post-category', post?.category || 'writing'); setValue('post-category-label', post?.categoryLabel || 'Writing');
  setValue('post-date', post?.date || new Date().toISOString().slice(0, 10)); setValue('post-status', post?.status || 'draft');
  setValue('post-tags', (post?.tags || []).join(', ')); setValue('post-thesis', post?.thesis || ''); setValue('post-related', (post?.related || []).join(', ')); setValue('post-body', post?.body || '');
  heading.textContent = post?.title || 'New post'; stateEl.textContent = post?.status === 'published' ? 'Published' : 'Draft'; form.hidden = false; save.disabled = false;
}

function renderRevisions(revisions) {
  revisionsRoot.replaceChildren(); revisionsCard.hidden = !revisions.length;
  for (const revision of revisions) {
    const row = document.createElement('div'); row.className = 'revision-row';
    const text = document.createElement('span'); text.textContent = `${revision.createdAt || ''} · ${revision.snapshot?.title || revision.originalSlug}`;
    const button = document.createElement('button'); button.type = 'button'; button.className = 'secondary'; button.textContent = 'Restore';
    button.addEventListener('click', () => restoreRevision(revision.id)); row.append(text, button); revisionsRoot.append(row);
  }
}

async function loadIndex(preferred = '') {
  const data = await request('/api/cms-writing.php'); list.replaceChildren();
  for (const post of data.posts || []) {
    const button = document.createElement('button'); button.type = 'button'; button.dataset.slug = post.slug; button.textContent = `${post.title || post.slug} · ${post.status}`;
    if (post.slug === preferred) button.setAttribute('aria-current', 'true');
    button.addEventListener('click', () => loadPost(post.slug)); list.append(button);
  }
  if (!(data.posts || []).length) { const empty = document.createElement('p'); empty.className = 'empty-list'; empty.textContent = 'No posts yet.'; list.append(empty); }
}

async function loadPost(slug) {
  setStatus('Loading…');
  try { const data = await request(`/api/cms-writing.php?slug=${encodeURIComponent(slug)}`); fillForm(data.post); renderRevisions(data.revisions || []); await loadIndex(data.post.slug); setStatus(''); }
  catch (error) { setStatus(error.message, 'error'); }
}

async function savePost() {
  save.disabled = true; setStatus('Saving…');
  try {
    const data = await request('/api/cms-writing.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CMS-CSRF': csrf }, body: JSON.stringify({ action: 'save', post: postFromForm(), originalSlug, expectedHash }) });
    fillForm(data.post); renderRevisions(data.revisions || []); await loadIndex(data.post.slug); setStatus(data.post.status === 'published' ? 'Saved and published.' : 'Draft saved.', 'success');
  } catch (error) { setStatus(error.status === 409 ? 'This post changed elsewhere. Reload it before saving.' : error.message, 'error'); }
  finally { save.disabled = false; }
}

async function restoreRevision(revisionId) {
  if (!current) return; setStatus('Restoring revision…');
  try {
    const data = await request('/api/cms-writing.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CMS-CSRF': csrf }, body: JSON.stringify({ action: 'restore', slug: current.slug, revisionId, expectedHash }) });
    fillForm(data.post); renderRevisions(data.revisions || []); await loadIndex(data.post.slug); setStatus('Revision restored as the current version.', 'success');
  } catch (error) { setStatus(error.status === 409 ? 'This post changed elsewhere. Reload it before restoring.' : error.message, 'error'); }
}

document.getElementById('new-post')?.addEventListener('click', () => { fillForm(null); renderRevisions([]); setStatus('New draft.'); });
save?.addEventListener('click', savePost);
document.getElementById('logout')?.addEventListener('click', async () => { try { await request('/api/cms-auth.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CMS-CSRF': csrf }, body: JSON.stringify({ action: 'logout' }) }); } finally { location.href = '/cms/'; } });

loadIndex().catch((error) => setStatus(error.message, 'error'));
