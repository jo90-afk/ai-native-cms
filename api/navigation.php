<?php
declare(strict_types=1);
require_once __DIR__.'/presentation-core.php';
require_once __DIR__.'/composition-store.php';
require_once __DIR__.'/page-routes.php';

/** Canonical ordered site navigation, independent of adopter page markup except #site-nav. */

function navigationDefaultItems(string $root): array {
    $configured=siteConfigValue('navigation','primary',null);if(is_array($configured)&&$configured)return navigationValidate($configured);
    $items=[];foreach(cmsConfiguredPages($root) as $path=>$label){$href=pageRoutesEnabled()?pageRouteKeyToRoute($path):($path==='index.html'?'/':'/'.$path);$items[]=['id'=>'page-'.substr(hash('sha256',$path),0,10),'label'=>$label,'href'=>$href,'enabled'=>true];if(count($items)>=12)break;}
    return $items?:[['id'=>'home','label'=>'Home','href'=>'/','enabled'=>true]];
}
function navigationSafeHref(string $href): bool {
    $href=trim($href);if($href==='')return false;if(str_starts_with($href,'#'))return !str_contains($href,"\0");
    if(str_starts_with($href,'/'))return !str_starts_with($href,'//')&&!str_contains($href,'..')&&!preg_match('/[\x00-\x20\x7f]/',$href);
    return preg_match('#^https://[^\s]+$#i',$href)===1;
}
function navigationValidate(array $items): array {
    if(count($items)>24)throw new RuntimeException('Primary navigation may contain at most 24 items.');$out=[];$ids=[];
    foreach($items as $i=>$item){if(!is_array($item))continue;$label=trim((string)($item['label']??''));$href=trim((string)($item['href']??''));$id=trim((string)($item['id']??''));
        if($id===''||!preg_match('/^[A-Za-z0-9_-]{2,80}$/',$id))$id='nav-'.($i+1).'-'.substr(hash('sha256',$label.'|'.$href),0,8);if(isset($ids[$id]))throw new RuntimeException('Navigation item IDs must be unique.');$ids[$id]=true;
        if($label===''||strlen($label)>80||strlen($href)>512||!navigationSafeHref($href))throw new RuntimeException('Navigation contains an invalid label or destination.');$out[]=['id'=>$id,'label'=>$label,'href'=>$href,'enabled'=>!array_key_exists('enabled',$item)||(bool)$item['enabled']];
    }
    if(!$out)throw new RuntimeException('Primary navigation needs at least one valid item.');return $out;
}
function navigationState(string $root): array {
    $stmt=db()->prepare('SELECT items_json,updated_at FROM site_navigation WHERE nav_key=?');$stmt->execute(['primary']);$row=$stmt->fetch();$configured=is_array($row);$items=$configured?navigationValidate(dbJsonDecode((string)$row['items_json'])):navigationDefaultItems($root);$hash=presentationHash($items);
    return ['items'=>$items,'configured'=>$configured,'hash'=>$hash,'updatedAt'=>$configured?dbIso((string)$row['updated_at']):null];
}
function navigationPathFromHref(string $href,array $managedKeys=[]): ?string {
    if(!str_starts_with($href,'/'))return null;$fragment=parse_url($href,PHP_URL_FRAGMENT);if(is_string($fragment)&&$fragment!=='')return null;$path=(string)(parse_url($href,PHP_URL_PATH)??'');if($path===''||$path==='/')return 'index.html';if($managedKeys){$managed=pageRoutePathToManagedKey($path,$managedKeys);if($managed!==null)return $managed;}return ltrim($path,'/');
}
function navigationCandidates(string $path,array $parents=[]): array {
    $out=[$path];$seen=[$path=>true];$cursor=$parents[$path]??null;
    while(is_string($cursor)&&$cursor!==''&&!isset($seen[$cursor])){$seen[$cursor]=true;$out[]=$cursor;$cursor=$parents[$cursor]??null;}return $out;
}
function navigationActiveId(string $path,array $items,array $parents=[],array $managedKeys=[]): ?string {
    foreach(navigationCandidates($path,$parents) as $candidate)foreach($items as $item){if(empty($item['enabled']))continue;$target=navigationPathFromHref((string)$item['href'],$managedKeys);if($target!==null&&$target===$candidate)return (string)$item['id'];}return null;
}
function navigationHtml(array $items,?string $activeId=null): string {
    $html='';foreach($items as $item){if(empty($item['enabled']))continue;$label=htmlspecialchars((string)$item['label'],ENT_QUOTES|ENT_HTML5,'UTF-8');$href=htmlspecialchars((string)$item['href'],ENT_QUOTES|ENT_HTML5,'UTF-8');$external=preg_match('#^https://#i',(string)$item['href'])===1;$active=$activeId!==null&&hash_equals($activeId,(string)$item['id']);$html.='<a'.($active?' class="active" aria-current="page"':'').' href="'.$href.'"'.($external?' target="_blank" rel="noopener noreferrer"':'').'>'.$label.'</a>';}
    return $html;
}
function navigationApply(string $html,array $items,string $path,array $parents=[],array $managedKeys=[]): string {
    $active=navigationActiveId($path,$items,$parents,$managedKeys);$nav=navigationHtml($items,$active);return preg_replace_callback('/(<nav\b(?=[^>]*\bid=["\']site-nav["\'])[^>]*>).*?(<\/nav>)/is',fn($m)=>$m[1].$nav.$m[2],$html,1)??$html;
}
function navigationProject(string $root): array {
    $state=navigationState($root);$parents=compositionParentMap();$managedKeys=pageRoutesEnabled()?pageRouteManagedKeySet(array_keys(cmsManagedPages($root))):[];$changed=0;$pages=0;foreach(presentationPublicHtmlFiles($root) as $path=>$full){$html=(string)file_get_contents($full);$next=navigationApply($html,$state['items'],$path,$parents,$managedKeys);if($next!==$html){cmsAtomicWrite($full,$next);$changed++;}$pages++;}return ['pages'=>$pages,'changed'=>$changed,'hash'=>$state['hash']];
}
function navigationSave(string $root,array $items,string $expectedHash): array {
    $current=navigationState($root);if($expectedHash===''||!hash_equals($expectedHash,$current['hash']))throw new RuntimeException('Navigation changed since it was opened. Refresh before saving.');$items=navigationValidate($items);if($current['configured'])presentationRevision('@navigation','navigation',$current['items']);
    $userId=(int)($_SESSION['cms_user_id']??0);$stmt=db()->prepare('INSERT INTO site_navigation (nav_key,items_json,updated_by,updated_at) VALUES (?,?,NULLIF(?,0),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE items_json=VALUES(items_json),updated_by=VALUES(updated_by),updated_at=UTC_TIMESTAMP()');$stmt->execute(['primary',dbJsonEncode($items),$userId]);$projected=navigationProject($root);cmsAudit('navigation','Updated primary navigation',['items'=>count($items),'changedPages'=>$projected['changed']],$root);return navigationState($root)+['projected'=>$projected];
}
function navigationAddPage(string $root,string $path,string $label): array {
    $state=navigationState($root);$href=pageRoutesEnabled()?pageRouteKeyToRoute($path):'/'.ltrim($path,'/');foreach($state['items'] as $item)if((string)$item['href']===$href)return $state;$items=$state['items'];$items[]=['id'=>'page-'.substr(hash('sha256',$path),0,10),'label'=>substr(trim($label),0,80),'href'=>$href,'enabled'=>true];return navigationSave($root,$items,$state['hash']);
}
