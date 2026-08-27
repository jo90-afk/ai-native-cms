(()=>{
'use strict';
if(window.parent===window)return;
const origin=window.location.origin,openKey=document.body.dataset.blockOpenKey||'',newOnLoad=document.body.dataset.blockNewOnLoad==='1',status=document.getElementById('block-status'),list=document.getElementById('block-list');
let opened=false,lastSavedKey='';
function openInitial(){if(opened)return true;if(newOnLoad){const button=document.getElementById('new-block');if(button){button.click();opened=true;return true;}return false;}if(openKey){const button=[...document.querySelectorAll('#block-list [data-key]')].find(el=>el.dataset.key===openKey);if(button){button.click();opened=true;return true;}return false;}opened=true;return true;}
let tries=0;const timer=setInterval(()=>{tries++;if(openInitial()||tries>100)clearInterval(timer);},50);
if(status&&list){const observer=new MutationObserver(()=>{if(!/preset saved/i.test(status.textContent||''))return;const active=list.querySelector('[data-key].active')||list.querySelector('[data-key][aria-current="true"]');const key=active?.dataset.key||'';if(!key||key===lastSavedKey)return;lastSavedKey=key;window.parent.postMessage({type:'aincms:block-preset-saved',preset:{key}},origin);});observer.observe(status,{childList:true,subtree:true,characterData:true});}
})();
