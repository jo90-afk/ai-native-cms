'use strict';

const status = document.getElementById('onboarding-status');
const stepsRoot = document.getElementById('onboarding-steps');
const principlesRoot = document.getElementById('onboarding-principles');
const progressValue = document.getElementById('progress-value');
const progressDetail = document.getElementById('progress-detail');
const logout = document.getElementById('logout');
const csrf = document.querySelector('meta[name="cms-csrf"]')?.content || '';

function setStatus(message, tone = '') {
  if (!status) return;
  status.textContent = message;
  status.dataset.tone = tone;
}

async function request(url, options = {}) {
  const response = await fetch(url, { credentials: 'same-origin', ...options });
  let data = {};
  try { data = await response.json(); } catch (_) { data = { ok: false, error: 'Invalid server response.' }; }
  if (response.status === 401) {
    location.href = '/cms/';
    throw new Error('Authentication required.');
  }
  if (!response.ok || data.ok === false) throw new Error(data.error || `Request failed (${response.status}).`);
  return data;
}

function stateLabel(state) {
  if (state === 'complete') return 'Complete';
  if (state === 'ready') return 'Ready';
  if (state === 'optional') return 'Optional';
  return 'Blocked';
}

function renderPrinciples(principles) {
  principlesRoot?.replaceChildren();
  for (const principle of principles || []) {
    const item = document.createElement('li');
    item.textContent = principle;
    principlesRoot?.append(item);
  }
}

function renderSteps(steps, nextId) {
  stepsRoot?.replaceChildren();
  for (const [index, step] of (steps || []).entries()) {
    const article = document.createElement('article');
    article.className = 'onboarding-step';
    article.dataset.state = step.state || 'blocked';
    if (step.id === nextId) article.dataset.next = 'true';

    const number = document.createElement('span');
    number.className = 'step-number';
    number.textContent = String(index + 1).padStart(2, '0');

    const body = document.createElement('div');
    body.className = 'step-body';
    const meta = document.createElement('div');
    meta.className = 'step-meta';
    const badge = document.createElement('span');
    badge.className = 'step-badge';
    badge.textContent = stateLabel(step.state);
    if (!step.required) badge.setAttribute('title', 'This step does not block launch readiness.');
    const heading = document.createElement('h3');
    heading.textContent = step.label || 'Step';
    meta.append(heading, badge);
    const message = document.createElement('p');
    message.textContent = step.message || '';
    body.append(meta, message);

    const action = document.createElement('a');
    action.className = 'step-action';
    action.href = step.href || '#';
    action.textContent = step.action || 'Open';
    if (String(step.href || '').startsWith('/docs/') || step.href === '/') {
      action.target = '_blank';
      action.rel = 'noopener';
    }

    article.append(number, body, action);
    stepsRoot?.append(article);
  }
}

function render(data) {
  const onboarding = data.onboarding || {};
  const progress = onboarding.progress || {};
  if (progressValue) progressValue.textContent = onboarding.ready ? 'Foundation ready' : `${progress.complete || 0} of ${progress.required || 0} required steps complete`;
  if (progressDetail) progressDetail.textContent = onboarding.ready ? 'The site has cleared the foundational onboarding gate. Keep using these links whenever you want to revisit the setup.' : 'Your next step is highlighted below. Optional writing does not block the foundation.';
  renderPrinciples(onboarding.principles || []);
  renderSteps(onboarding.steps || [], progress.next || null);
  setStatus(onboarding.ready ? 'Foundational onboarding state is ready.' : 'Onboarding reflects the site’s current durable state.', onboarding.ready ? 'success' : '');
}

async function load() {
  try { render(await request('/api/cms-onboarding.php')); }
  catch (error) {
    stepsRoot?.replaceChildren();
    const message = document.createElement('p');
    message.className = 'empty';
    message.textContent = error.message;
    stepsRoot?.append(message);
    setStatus(error.message, 'error');
  }
}

logout?.addEventListener('click', async () => {
  logout.disabled = true;
  try {
    await request('/api/cms-auth.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CMS-CSRF': csrf },
      body: JSON.stringify({ action: 'logout' }),
    });
  } finally { location.href = '/cms/'; }
});

load();
