'use strict';

const view = document.body?.dataset.cmsView || '';
const csrf = document.querySelector('meta[name="cms-csrf"]')?.content || '';

async function request(url, options = {}) {
  const response = await fetch(url, { credentials: 'same-origin', ...options });
  let data = {};
  try { data = await response.json(); } catch (_) { data = { ok: false, error: 'Invalid server response.' }; }
  if (response.status === 401 && view !== 'login') {
    location.href = '/cms/';
    throw new Error('Authentication required.');
  }
  if (!response.ok || data.ok === false) {
    const error = new Error(data.error || `Request failed (${response.status}).`);
    error.status = response.status;
    throw error;
  }
  return data;
}

function setStatus(element, message, tone = '') {
  if (!element) return;
  element.textContent = message;
  element.dataset.tone = tone;
}

function initLogin() {
  const form = document.getElementById('login-form');
  const status = document.getElementById('login-status');
  if (!form) return;
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const button = form.querySelector('button[type="submit"]');
    button.disabled = true;
    setStatus(status, 'Signing in…');
    try {
      await request('/api/cms-auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'login', username: document.getElementById('username').value, password: document.getElementById('password').value }),
      });
      location.href = '/cms/pages.php';
    } catch (error) {
      setStatus(status, error.message, 'error');
      button.disabled = false;
    }
  });
}

function initPages() {
  const select = document.getElementById('page-select');
  const heading = document.getElementById('page-heading');
  const blocksRoot = document.getElementById('blocks');
  const save = document.getElementById('save-page');
  const status = document.getElementById('editor-status');
  const authority = document.getElementById('authority-state');
  const logout = document.getElementById('logout');
  if (!select || !blocksRoot || !save) return;

  function dirtyChanges() {
    return [...blocksRoot.querySelectorAll('.block-input')]
      .filter((input) => input.value !== input.dataset.original)
      .map((input) => ({ id: input.dataset.id, html: input.value, hash: input.dataset.hash }));
  }

  function updateSaveState() {
    save.disabled = dirtyChanges().length === 0;
  }

  function renderAuthority(info) {
    const ready = Boolean(info?.ready);
    authority.textContent = ready
      ? `Canonical SQL · ${info.blocks || 0} editable blocks`
      : 'Canonical state will initialize on first save.';
    authority.dataset.ready = ready ? 'true' : 'false';
  }

  function renderBlocks(page, blocks) {
    blocksRoot.replaceChildren();
    heading.textContent = page?.label || page?.title || page?.path || 'Page';
    if (!blocks.length) {
      const empty = document.createElement('p');
      empty.className = 'empty';
      empty.textContent = 'This page has no data-cms-id text leaves yet.';
      blocksRoot.append(empty);
      save.disabled = true;
      return;
    }
    for (const block of blocks) {
      const item = document.createElement('article');
      item.className = 'block-card';
      const meta = document.createElement('div');
      meta.className = 'block-meta';
      const id = document.createElement('strong');
      id.textContent = block.id;
      const tag = document.createElement('span');
      tag.textContent = String(block.tag || 'text').toUpperCase();
      meta.append(id, tag);
      const input = document.createElement('textarea');
      input.className = 'block-input';
      input.rows = Math.max(3, Math.min(14, Math.ceil(String(block.html || '').length / 80)));
      input.value = block.html || '';
      input.dataset.original = block.html || '';
      input.dataset.id = block.id;
      input.dataset.hash = block.hash || '';
      input.setAttribute('aria-label', `Edit ${block.id}`);
      input.addEventListener('input', updateSaveState);
      item.append(meta, input);
      blocksRoot.append(item);
    }
    updateSaveState();
  }

  async function loadPage(path) {
    if (!path) return;
    setStatus(status, 'Loading…');
    save.disabled = true;
    try {
      const data = await request(`/api/cms-pages.php?path=${encodeURIComponent(path)}`);
      renderAuthority(data.contentAuthority);
      renderBlocks(data.page, data.blocks || []);
      setStatus(status, '');
    } catch (error) {
      blocksRoot.replaceChildren();
      setStatus(status, error.message, 'error');
    }
  }

  async function loadIndex() {
    try {
      const data = await request('/api/cms-pages.php');
      renderAuthority(data.contentAuthority);
      select.replaceChildren();
      for (const page of data.pages || []) {
        const option = document.createElement('option');
        option.value = page.path;
        option.textContent = page.label || page.title || page.path;
        select.append(option);
      }
      if (select.value) await loadPage(select.value);
      else setStatus(status, 'No configured editable pages were found.');
    } catch (error) {
      setStatus(status, error.message, 'error');
    }
  }

  select.addEventListener('change', () => loadPage(select.value));
  save.addEventListener('click', async () => {
    const changes = dirtyChanges();
    if (!changes.length) return;
    save.disabled = true;
    setStatus(status, `Saving ${changes.length} change${changes.length === 1 ? '' : 's'}…`);
    try {
      await request('/api/cms-pages.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CMS-CSRF': csrf },
        body: JSON.stringify({ path: select.value, changes }),
      });
      setStatus(status, 'Saved.', 'success');
      await loadPage(select.value);
    } catch (error) {
      setStatus(status, error.status === 409 ? 'The page changed elsewhere. Reload it before saving.' : error.message, 'error');
      updateSaveState();
    }
  });

  logout?.addEventListener('click', async () => {
    logout.disabled = true;
    try {
      await request('/api/cms-auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CMS-CSRF': csrf },
        body: JSON.stringify({ action: 'logout' }),
      });
    } finally {
      location.href = '/cms/';
    }
  });

  loadIndex();
}

if (view === 'login') initLogin();
if (view === 'pages') initPages();
