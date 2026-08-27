(()=>{
'use strict';
const routeForKey=value=>{
  const key=String(value||'').replace(/^\/+/, '');
  if(!key||key==='index.html')return '/';
  if(key.endsWith('/index.html'))return `/${key.slice(0,-10)}`;
  if(key.endsWith('.html'))return `/${key.slice(0,-5)}/`;
  return `/${key.replace(/^\/+|\/+$/g,'')}/`;
};
const renderPathText=value=>String(value||'').replace(/(^|\s)([a-z0-9][a-z0-9/-]*\.html)(?=\s|$|·|—)/gi,(match,prefix,key)=>`${prefix}${routeForKey(key)}`);
function refreshLabels(){
  document.querySelectorAll('#composer-pages button[data-path] span').forEach(el=>{const next=renderPathText(el.textContent);if(next!==el.textContent)el.textContent=next;});
}
const observer=new MutationObserver(refreshLabels);observer.observe(document.getElementById('composer-pages')||document.body,{childList:true,subtree:true});refreshLabels();

const routeInput=document.getElementById('new-path'),create=document.getElementById('create-page');
if(routeInput){
  routeInput.addEventListener('input',()=>{
    routeInput.value=routeInput.value.toLowerCase().replace(/\.html$/,'').replace(/^\/+|\/+$/g,'').replace(/[^a-z0-9-]/g,'-').replace(/-+/g,'-').replace(/^-|-$/g,'');
  });
}
if(routeInput&&create){
  create.addEventListener('click',()=>{
    const slug=routeInput.value.trim().replace(/^\/+|\/+$/g,'').replace(/\.html$/,'');
    if(!slug)return;
    routeInput.value=`${slug}.html`;
    queueMicrotask(()=>{if(routeInput.value===`${slug}.html`)routeInput.value=slug;});
  },true);
}
})();
