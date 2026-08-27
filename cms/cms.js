'use strict';

const view = document.body?.dataset.cmsView || '';

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
      let target = '/cms/onboarding.php';
      try {
        const state = await request('/api/cms-onboarding.php');
        if (state.onboarding?.ready) target = '/cms/composer.php';
      } catch (_) {}
      location.href = target;
    } catch (error) {
      setStatus(status, error.message, 'error');
      button.disabled = false;
    }
  });
}

if (view === 'login') initLogin();
