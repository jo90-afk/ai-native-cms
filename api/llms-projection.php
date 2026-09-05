<?php
declare(strict_types=1);
require_once __DIR__.'/discovery-projection.php';
require_once __DIR__.'/markdown-projection.php';

/** Compact agent-routing surface derived only from public discovery state. */
function llmsProjectReadIndex(string $root): array {
    $path=$root.'/site-index.json';if(!is_file($path))throw new RuntimeException('Structured public discovery index is unavailable for llms.txt projection.');$decoded=json_decode((string)file_get_contents($path),true);if(!is_array($decoded))throw new RuntimeException('Structured public discovery index is invalid.');return $decoded;
}
function llmsProjectText(mixed $value): string {return discoveryOneLine($value);}
function llmsProjectLabel(string $value): string {return str_replace(['[',']'],['(',')'],llmsProjectText($value));}
function llmsProjectEntry(string $title,string $url,string $description=''): string {$description=llmsProjectText($description);return '- ['.llmsProjectLabel($title).']('.trim($url).')'.($description!==''?': '.$description:'')."\n";}
function llmsProjectBuild(array $index,string $root,array $preferredUrls=[]): string {
    $site=is_array($index['site']??null)?$index['site']:[];$name=llmsProjectText($site['name']??'Site')?:'Site';$description=llmsProjectText($site['description']??'');$base=rtrim(llmsProjectText($site['baseUrl']??''),'/');if($base===''||filter_var($base,FILTER_VALIDATE_URL)===false)throw new RuntimeException('Public discovery index has no valid base URL.');$pages=is_array($index['pages']??null)?$index['pages']:[];$writingRoot=trim((string)siteConfigValue('writing','route_root','writing'),'/');
    $out='# '.$name."\n\n> ".($description!==''?$description:'Public pages and published material for '.$name.'.')."\n\n";
    $out.="Use this file as a compact routing index. Follow only the links needed for the question and prefer linked Markdown alternates for clean retrieval. Published HTML is the reader-facing source of truth; Markdown alternates, site-index.json, sitemaps, feeds, llms.txt, and llms-full.txt are replaceable discovery projections. Private CMS state, subscriber data, credentials, drafts, and non-public APIs are excluded.\n\n";
    $home=[];$writing=[];$other=[];foreach($pages as $page){if(!is_array($page))continue;$url=trim((string)($page['url']??''));if($url==='')continue;if(rtrim($url,'/')===$base)$home[]=$page;elseif($writingRoot!==''&&str_contains((string)parse_url($url,PHP_URL_PATH),'/'.$writingRoot.'/'))$writing[]=$page;else$other[]=$page;}
    if($home){$out.="## Start here\n\n";foreach($home as $page)$out.=llmsProjectEntry((string)($page['title']??'Home'),$preferredUrls[$page['url']]??(string)$page['url'],(string)($page['description']??''));$out.="\n";}
    if($other){$out.="## Pages\n\n";foreach($other as $page)$out.=llmsProjectEntry((string)($page['title']??'Page'),$preferredUrls[$page['url']]??(string)$page['url'],(string)($page['description']??''));$out.="\n";}
    if($writing){$out.="## Writing\n\n";foreach($writing as $page)$out.=llmsProjectEntry((string)($page['title']??'Writing'),$preferredUrls[$page['url']]??(string)$page['url'],(string)($page['description']??''));$out.="\n";}
    $out.="## Machine-readable sources\n\n";$out.=llmsProjectEntry('Structured site index',$base.'/site-index.json','JSON inventory of the public pages represented by this projection.');$out.=llmsProjectEntry('XML sitemap',$base.'/sitemap.xml','Canonical public URL inventory.');$out.=llmsProjectEntry('Plain-text sitemap',$base.'/sitemap.txt','Canonical public URLs, one per line.');if(is_file($root.'/feed.xml'))$out.=llmsProjectEntry('RSS feed',$base.'/feed.xml','Published feed when the site provides one.');if(is_file($root.'/llms-full.txt'))$out.=llmsProjectEntry('Expanded LLM context',$base.'/llms-full.txt','Optional expanded public context; use selectively because it is larger than this index.');return rtrim($out)."\n";
}
function llmsProjectReadFull(string $root): ?array {
    $path=$root.'/llms-full.txt';if(!file_exists($path)&&!is_link($path))return null;
    if(is_link($path)||!is_file($path))throw new RuntimeException('Optional expanded LLM context must be a regular local file.');
    $existing=file_get_contents($path);if($existing===false)throw new RuntimeException('Optional expanded LLM context is unreadable.');
    if(!preg_match('/\\r?\\n---\\r?\\n\\r?\\n/',$existing,$match,PREG_OFFSET_CAPTURE))throw new RuntimeException('Optional expanded LLM context lacks its corpus separator and cannot be synchronized.');
    return ['existing'=>$existing,'corpus'=>substr($existing,$match[0][1])];
}
function llmsProjectSyncFullPrefix(string $root,string $llms): bool {
    $full=llmsProjectReadFull($root);if($full===null)return false;
    $next=rtrim($llms)."\n".$full['corpus'];if($next!==$full['existing'])pageProjectionWrite($root.'/llms-full.txt',$next);return true;
}
function llmsProjectInjectDiscoveryLinks(string $root,?string $base=null): int {
    $changed=0;$tag='<link rel="describedby" href="/llms.txt">';
    $base=$base??discoveryBaseUrl();$href=rtrim((string)(parse_url($base,PHP_URL_PATH)??''),'/').'/llms.txt';
    if($href!=='/llms.txt')$tag='<link rel="describedby" href="'.htmlspecialchars($href,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8').'">';
    foreach(discoveryPublicHtmlFiles($root) as $relative=>$full){
        $before=(string)file_get_contents($full);$canonical=discoveryCanonical($before)?:discoveryUrlForRelative($base,$relative);
        $eligible=discoverySameSite($base,$canonical)&&!preg_match('/(?:^|[,\\s])noindex(?:$|[,\\s])/i',discoveryMetaContent($before,'robots'));
        $inserted=false;$after=preg_replace_callback('~<link\\b[^>]*>~i',function($match)use($href,$tag,$eligible,&$inserted){
            $rel=preg_split('/\\s+/',strtolower(discoveryHtmlAttribute($match[0],'rel')))?:[];$target=discoveryHtmlAttribute($match[0],'href');
            if(in_array('describedby',$rel,true)&&in_array($target,[$href,'/llms.txt'],true)){if($inserted||!$eligible)return '';$inserted=true;return $tag;}return $match[0];
        },$before)??$before;
        if(!$inserted&&$eligible&&stripos($after,'</head>')!==false)$after=preg_replace('/<\/head>/i',$tag.'</head>',$after,1)??$after;
        if($after!==$before){pageProjectionWrite($full,$after);$changed++;}
    }
    return $changed;
}
function llmsProject(string $root): array {
    $root=realpath($root)?:$root;$index=llmsProjectReadIndex($root);
    llmsProjectReadFull($root); // Missing is optional; present-but-invalid fails before projection writes.
    $markdown=markdownProject($root,$index);$index['pages']=$markdown['pages'];
    $llms=llmsProjectBuild($index,$root,$markdown['preferredUrls']);$full=llmsProjectSyncFullPrefix($root,$llms);
    pageProjectionWrite($root.'/llms.txt',$llms);$html=llmsProjectInjectDiscoveryLinks($root,discoveryBaseUrl(['base_url'=>$index['site']['baseUrl']??'']));
    unset($markdown['pages'],$markdown['preferredUrls']);
    return ['bytes'=>strlen($llms),'htmlDiscoveryLinks'=>$html,'llmsFullSynchronized'=>$full,'markdown'=>$markdown];
}
