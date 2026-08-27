<?php
declare(strict_types=1);
require_once __DIR__.'/discovery-projection.php';

/** Compact agent-routing surface derived only from public discovery state. */
function llmsProjectReadIndex(string $root): array {
    $path=$root.'/site-index.json';if(!is_file($path))throw new RuntimeException('Structured public discovery index is unavailable for llms.txt projection.');$decoded=json_decode((string)file_get_contents($path),true);if(!is_array($decoded))throw new RuntimeException('Structured public discovery index is invalid.');return $decoded;
}
function llmsProjectText(mixed $value): string {return discoveryOneLine($value);}
function llmsProjectLabel(string $value): string {return str_replace(['[',']'],['(',')'],llmsProjectText($value));}
function llmsProjectEntry(string $title,string $url,string $description=''): string {$description=llmsProjectText($description);return '- ['.llmsProjectLabel($title).']('.trim($url).')'.($description!==''?': '.$description:'')."\n";}
function llmsProjectBuild(array $index,string $root): string {
    $site=is_array($index['site']??null)?$index['site']:[];$name=llmsProjectText($site['name']??'Site')?:'Site';$description=llmsProjectText($site['description']??'');$base=rtrim(llmsProjectText($site['baseUrl']??''),'/');if($base===''||filter_var($base,FILTER_VALIDATE_URL)===false)throw new RuntimeException('Public discovery index has no valid base URL.');$pages=is_array($index['pages']??null)?$index['pages']:[];$writingRoot=trim((string)siteConfigValue('writing','route_root','writing'),'/');
    $out='# '.$name."\n\n> ".($description!==''?$description:'Public pages and published material for '.$name.'.')."\n\n";
    $out.="Use this file as a compact routing index. Published HTML is the reader-facing source of truth; site-index.json, sitemaps, feeds, llms.txt, and llms-full.txt are replaceable discovery projections. Private CMS state, subscriber data, credentials, drafts, and non-public APIs are excluded.\n\n";
    $home=[];$writing=[];$other=[];foreach($pages as $page){if(!is_array($page))continue;$url=trim((string)($page['url']??''));if($url==='')continue;if(rtrim($url,'/')===$base)$home[]=$page;elseif($writingRoot!==''&&str_contains((string)parse_url($url,PHP_URL_PATH),'/'.$writingRoot.'/'))$writing[]=$page;else$other[]=$page;}
    if($home){$out.="## Start here\n\n";foreach($home as $page)$out.=llmsProjectEntry((string)($page['title']??'Home'),(string)$page['url'],(string)($page['description']??''));$out.="\n";}
    if($other){$out.="## Pages\n\n";foreach($other as $page)$out.=llmsProjectEntry((string)($page['title']??'Page'),(string)$page['url'],(string)($page['description']??''));$out.="\n";}
    if($writing){$out.="## Writing\n\n";foreach($writing as $page)$out.=llmsProjectEntry((string)($page['title']??'Writing'),(string)$page['url'],(string)($page['description']??''));$out.="\n";}
    $out.="## Machine-readable sources\n\n";$out.=llmsProjectEntry('Structured site index',$base.'/site-index.json','JSON inventory of the public pages represented by this projection.');$out.=llmsProjectEntry('XML sitemap',$base.'/sitemap.xml','Canonical public URL inventory.');$out.=llmsProjectEntry('Plain-text sitemap',$base.'/sitemap.txt','Canonical public URLs, one per line.');if(is_file($root.'/feed.xml'))$out.=llmsProjectEntry('RSS feed',$base.'/feed.xml','Published feed when the site provides one.');if(is_file($root.'/llms-full.txt'))$out.=llmsProjectEntry('Expanded LLM context',$base.'/llms-full.txt','Optional expanded public context; use selectively because it is larger than this index.');return rtrim($out)."\n";
}
function llmsProjectSyncFullPrefix(string $root,string $llms): bool {
    $path=$root.'/llms-full.txt';if(!is_file($path))return false;$existing=(string)file_get_contents($path);$marker="\n---\n\n# Expanded public context";$pos=strpos($existing,$marker);if($pos===false)return false;$next=rtrim($llms)."\n".substr($existing,$pos);if($next===$existing)return true;pageProjectionWrite($path,$next);return true;
}
function llmsProjectInjectDiscoveryLinks(string $root): int {
    $changed=0;$tag='<link rel="describedby" href="/llms.txt">';foreach(presentationPublicHtmlFiles($root) as $full){$before=(string)file_get_contents($full);if(preg_match('/<link\b[^>]*\brel=["\']describedby["\'][^>]*>/i',$before))continue;if(stripos($before,'</head>')===false)continue;$after=preg_replace('/<\/head>/i',$tag.'</head>',$before,1)??$before;if($after!==$before){pageProjectionWrite($full,$after);$changed++;}}return $changed;
}
function llmsProject(string $root): array {$index=llmsProjectReadIndex($root);$llms=llmsProjectBuild($index,$root);pageProjectionWrite($root.'/llms.txt',$llms);$full=llmsProjectSyncFullPrefix($root,$llms);$html=llmsProjectInjectDiscoveryLinks($root);return ['bytes'=>strlen($llms),'htmlDiscoveryLinks'=>$html,'llmsFullSynchronized'=>$full];}
