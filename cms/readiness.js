(()=>{
'use strict';
const list=document.getElementById('readiness-list');
const state=document.getElementById('readiness-state');
const summary=document.getElementById('readiness-summary');
const pill=document.getElementById('readiness-pill');
const statusEl=document.getElementById('readiness-status');
function status(message,error=false){statusEl.textContent=message||'';statusEl.dataset.error=error?'1':'0';}
async function api(url,options={}){const response=await fetch(url,{credentials:'same-origin',...options});let data={};try{data=await response.json();}catch(_e){}if(!response.ok)throw new Error(data.error||`Request failed (${response.status})`);return data;}
function renderCheck(check){const card=document.createElement('section');card.className='section-card';const row=document.createElement('div');row.className='toolbar';const title=document.createElement('strong');title.textContent=check.label||check.id;const badge=document.createElement('span');badge.className='pill';badge.textContent=check.status==='pass'?'Pass':check.status==='warn'?'Warning':'Blocked';row.append(title,badge);card.append(row);const message=document.createElement('p');message.className='muted';message.textContent=check.message||'';card.append(message);const meta=document.createElement('small');meta.textContent=`${check.scope||'core'} · ${check.blocking?'blocking':'advisory'} · ${check.id||''}`;card.append(meta);return card;}
async function load(){status('Checking…');try{const data=await api('/api/cms-readiness.php');const report=data.readiness||{};list.replaceChildren();for(const check of report.checks||[])list.append(renderCheck(check));const s=report.summary||{};state.textContent=report.ready?'Ready for production':'Action required';pill.textContent=report.ready?'Ready':'Blocked';summary.textContent=`${s.pass||0} passed · ${s.warn||0} warnings · ${s.blockingFailures||0} blocking failures`;status(report.generatedAt?`Checked ${new Date(report.generatedAt).toLocaleString()}`:'');}catch(e){state.textContent='Check failed';pill.textContent='Unavailable';status(e.message,true);}}
document.getElementById('refresh-readiness')?.addEventListener('click',load);
document.getElementById('logout')?.addEventListener('click',async()=>{try{const csrf=document.querySelector('meta[name="cms-csrf"]')?.content||'';await api('/api/cms-auth.php',{method:'POST',headers:{'Content-Type':'application/json','X-CMS-CSRF':csrf},body:JSON.stringify({action:'logout'})});}finally{location.href='/cms/';}});
load();
})();
