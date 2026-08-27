(()=>{
'use strict';
const nav=document.querySelector('.cms-nav');if(nav&&!nav.querySelector('a[href="/cms/audience.php"]')){const link=document.createElement('a');link.href='/cms/audience.php';link.textContent='Audience';const seo=nav.querySelector('a[href="/cms/seo.php"]');nav.insertBefore(link,seo||null);}
const button=document.getElementById('bootstrap-presets'),status=document.getElementById('composer-status'),csrf=document.querySelector('meta[name="cms-csrf"]')?.content||'';
if(!button)return;
button.addEventListener('click',async()=>{
  button.disabled=true;if(status)status.textContent='Importing governed repository blocks…';
  try{
    const response=await fetch('/api/cms-composer.php',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CMS-CSRF':csrf},body:JSON.stringify({action:'bootstrapPresets'})});
    const data=await response.json().catch(()=>({}));if(!response.ok)throw new Error(data.error||`Request failed (${response.status})`);
    if(status)status.textContent=`Saved block library ready${data.result?.created?` · ${data.result.created} imported`:''}.`;
    location.reload();
  }catch(error){button.disabled=false;if(status){status.textContent=error.message;status.dataset.error='1';}}
});
})();
