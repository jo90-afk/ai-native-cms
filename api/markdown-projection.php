<?php
declare(strict_types=1);
require_once __DIR__.'/discovery-projection.php';

/** Disposable public HTML derivatives; never an input to canonical content. */
const MARKDOWN_PROJECTION_MARKER = '<!-- AI Native CMS public Markdown projection v1 -->';

function markdownProjectOwned(string $text): bool {
    return str_ends_with($text,MARKDOWN_PROJECTION_MARKER."\n");
}
function markdownProjectEscape(string $text): string {
    return str_replace(['\\','`','*','_','[',']'],['\\\\','\\`','\\*','\\_','\\[','\\]'],$text);
}
function markdownProjectAbsoluteHref(string $base,string $canonical,string $href): string {
    $href=trim(html_entity_decode($href,ENT_QUOTES|ENT_HTML5,'UTF-8'));
    if($href===''||preg_match('/[\\x00-\\x20\\x7f]/',$href))return '';
    if(preg_match('~^[a-z][a-z0-9+.-]*:~i',$href)){
        if(!preg_match('~^(?:https?://|mailto:|tel:)~i',$href))return '';
        $url=$href;
    } else {
        $origin=parse_url($base);if(!is_array($origin))return '';
        $host=$origin['scheme'].'://'.$origin['host'].(isset($origin['port'])?':'.$origin['port']:'');
        if(str_starts_with($href,'//'))$url=$origin['scheme'].':'.$href;
        elseif(str_starts_with($href,'#')||str_starts_with($href,'?'))$url=$canonical.$href;
        else {
            $parts=parse_url($href);if(!is_array($parts))return '';
            $path=(string)($parts['path']??'');$current=(string)(parse_url($canonical,PHP_URL_PATH)??'/');
            $directory=str_ends_with($current,'/')?$current:dirname($current).'/';
            $joined=str_starts_with($path,'/')?$path:$directory.$path;$segments=[];
            foreach(explode('/',$joined) as $part){if($part===''||$part==='.')continue;if($part==='..'){array_pop($segments);continue;}$segments[]=$part;}
            $url=$host.'/'.implode('/',$segments).(str_ends_with($joined,'/')&&$segments?'/':'');
            if(isset($parts['query']))$url.='?'.$parts['query'];if(isset($parts['fragment']))$url.='#'.$parts['fragment'];
        }
    }
    $parts=parse_url($url);if(!is_array($parts)||isset($parts['user'])||isset($parts['pass']))return '';
    if(strtolower((string)($parts['host']??''))===strtolower((string)parse_url($base,PHP_URL_HOST))){
        $prefix=rtrim((string)(parse_url($base,PHP_URL_PATH)??''),'/');$path=(string)($parts['path']??'/');
        if($prefix===''||str_starts_with($path,$prefix.'/')){$relative=trim(substr($path,strlen($prefix)),'/');if($relative!==''&&!discoveryPublicRelativePath($relative))return '';}
    }
    return str_replace(['\\','(',')','<','>'],['%5C','%28','%29','%3C','%3E'],$url);
}
function markdownProjectHidden(string $tag): bool {
    $attributes=preg_replace('/^<[^\\s>]+/','',$tag)??$tag;
    if(preg_match('/(?:^|\\s)hidden(?:\\s|=|\\/?$|>)/i',$attributes))return true;
    if(strtolower(discoveryHtmlAttribute($tag,'aria-hidden'))==='true')return true;
    return (bool)preg_match('/(?:^|;)\\s*(?:display\\s*:\\s*none|visibility\\s*:\\s*hidden)\\b/i',discoveryHtmlAttribute($tag,'style'));
}
function markdownProjectRenderFrame(array $frame,string $base,string $canonical): string {
    if($frame['skip'])return '';
    $tag=$frame['tag'];$body=$frame['body'];$trim=trim($body);
    if(preg_match('/^h([1-6])$/',$tag,$m))return "\n\n".str_repeat('#',(int)$m[1]).' '.$trim."\n\n";
    if(in_array($tag,['strong','b'],true))return '**'.$trim.'**';
    if(in_array($tag,['em','i'],true))return '*'.$trim.'*';
    if($tag==='a'){$href=markdownProjectAbsoluteHref($base,$canonical,discoveryHtmlAttribute($frame['open'],'href'));return $href!==''&&$trim!==''?'['.$trim.']('.$href.')':$body;}
    if($tag==='code'){
        if($frame['pre'])return $body;
        preg_match_all('/`+/',$body,$matches);$length=1;foreach($matches[0] as $match)$length=max($length,strlen($match)+1);
        $fence=str_repeat('`',$length);return $fence.' '.$trim.' '.$fence;
    }
    if($tag==='pre'){
        preg_match_all('/`+/',$body,$matches);$length=3;foreach($matches[0] as $match)$length=max($length,strlen($match)+1);
        $fence=str_repeat('`',$length);return "\n\n".$fence."\n".rtrim($body)."\n".$fence."\n\n";
    }
    if($tag==='blockquote')return "\n\n> ".str_replace("\n","\n> ",$trim)."\n\n";
    if($tag==='li')return "\n".$frame['prefix'].str_replace("\n","\n  ",$trim)."\n";
    if($tag==='td'||$tag==='th')return $trim.' | ';
    if($tag==='tr')return "\n| ".$trim."\n";
    if(in_array($tag,['p','div','section','article','main','body','figure','figcaption','ul','ol','table','dl','dt','dd'],true))return "\n\n".$trim."\n\n";
    return $body;
}
/** Small dependency-free serializer for published HTML, with nested exclusions. */
function markdownProjectHtmlToMarkdown(string $html,string $base,string $canonical): string {
    $tokens=preg_split('~(<!--.*?-->|<(?:[^>"\']+|"[^"]*"|\'[^\']*\')*>)~s',$html,-1,PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY)?:[];
    $frames=[['tag'=>'root','open'=>'','body'=>'','skip'=>false,'pre'=>false,'code'=>false,'prefix'=>'','next'=>1]];
    $sections=['main'=>[],'article'=>[],'body'=>[]];
    $void=['area','base','br','col','embed','hr','img','input','link','meta','param','source','track','wbr'];
    $excluded=['head','script','style','template','svg','header','nav','footer','aside','form','button','input','select','textarea','iframe','object','embed','noscript'];
    foreach($tokens as $token){
        $last=count($frames)-1;
        if(str_starts_with($token,'<!--')||str_starts_with($token,'<!'))continue;
        if(preg_match('~^</([a-z][a-z0-9:-]*)\\s*>$~i',$token,$match)){
            $tag=strtolower($match[1]);$found=null;for($i=$last;$i>0;$i--)if($frames[$i]['tag']===$tag){$found=$i;break;}
            if($found!==null)while(count($frames)-1>=$found){$frame=array_pop($frames);$rendered=markdownProjectRenderFrame($frame,$base,$canonical);if(!$frame['skip']&&isset($sections[$frame['tag']]))$sections[$frame['tag']][]=$rendered;$frames[count($frames)-1]['body'].=$rendered;}
            continue;
        }
        if(preg_match('~^<([a-z][a-z0-9:-]*)\\b~i',$token,$match)){
            $tag=strtolower($match[1]);$skip=$frames[$last]['skip']||in_array($tag,$excluded,true)||markdownProjectHidden($token);
            if(in_array($tag,$void,true)||str_ends_with($token,'/>')){
                if(!$skip&&$tag==='br')$frames[$last]['body'].="\n";
                if(!$skip&&$tag==='hr')$frames[$last]['body'].="\n\n---\n\n";
                if(!$skip&&$tag==='img')$frames[$last]['body'].=markdownProjectEscape(discoveryHtmlAttribute($token,'alt'));
                continue;
            }
            $prefix='- ';if($tag==='li'&&$frames[$last]['tag']==='ol')$prefix=(string)$frames[$last]['next']++.'. ';
            $frames[]=['tag'=>$tag,'open'=>$token,'body'=>'','skip'=>$skip,'pre'=>$tag==='pre'||$frames[$last]['pre'],'code'=>$tag==='code'||$frames[$last]['code'],'prefix'=>$prefix,'next'=>1];
            continue;
        }
        if($frames[$last]['skip'])continue;
        $text=html_entity_decode($token,ENT_QUOTES|ENT_HTML5,'UTF-8');
        if(!$frames[$last]['pre'])$text=preg_replace('/\\s+/u',' ',$text)??$text;
        $frames[$last]['body'].=$frames[$last]['pre']||$frames[$last]['code']?$text:markdownProjectEscape($text);
    }
    while(count($frames)>1){$frame=array_pop($frames);$rendered=markdownProjectRenderFrame($frame,$base,$canonical);if(!$frame['skip']&&isset($sections[$frame['tag']]))$sections[$frame['tag']][]=$rendered;$frames[count($frames)-1]['body'].=$rendered;}
    $body=$frames[0]['body'];foreach($sections as $section)if($section){$body=implode("\n\n",$section);break;}
    return trim(preg_replace('/\\n[ \\t]*\\n(?:[ \\t]*\\n)+/',"\n\n",$body)??$body);
}
function markdownProjectUpdateAlternate(string $html,?string $href): string {
    $replacement=$href===null?'':'<link rel="alternate" type="text/markdown" href="'.htmlspecialchars($href,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8').'" data-aincms-projection="markdown">';$inserted=false;
    $next=preg_replace_callback('~<link\\b[^>]*>~i',function($match)use($replacement,$href,&$inserted){
        $tag=$match[0];$rel=preg_split('/\\s+/',strtolower(discoveryHtmlAttribute($tag,'rel')))?:[];
        $owned=discoveryHtmlAttribute($tag,'data-aincms-projection')==='markdown';$alternate=in_array('alternate',$rel,true)&&strtolower(discoveryHtmlAttribute($tag,'type'))==='text/markdown';
        if(!$owned&&$alternate){if($href===null)return $tag;if(discoveryHtmlAttribute($tag,'href')!==$href)throw new RuntimeException('Markdown projection would replace an authored alternate link.');}
        if($owned||$alternate){if($inserted)return '';$inserted=true;return $replacement;}return $tag;
    },$html)??$html;
    if(!$inserted&&$replacement!==''&&stripos($next,'</head>')!==false)$next=preg_replace('~</head>~i',$replacement.'</head>',$next,1)??$next;
    return $next;
}
function markdownProject(string $root,array $index): array {
    $base=discoveryBaseUrl(['base_url'=>$index['site']['baseUrl']??'']);
    // Re-derive eligible files: a stale/edited JSON sourcePath is never a read capability.
    $pages=discoveryPublicPages($root,$base);$documents=[];$preferred=[];
    foreach($pages as $page){
        $relative=(string)$page['sourcePath'];$source=discoveryContainedFile($root,$relative);if($source===null)continue;
        $markdown=substr($relative,0,-5).'.md';$destination=$root.'/'.$markdown;
        if(is_link($destination)||is_dir($destination))throw new RuntimeException('Unsafe Markdown projection destination: '.$markdown);
        if(is_file($destination)&&!markdownProjectOwned((string)file_get_contents($destination)))throw new RuntimeException('Markdown projection would overwrite an authored file: '.$markdown);
        $html=file_get_contents($source);if($html===false)throw new RuntimeException('Public HTML is unreadable: '.$relative);
        $body=markdownProjectHtmlToMarkdown($html,$base,(string)$page['url']);
        if(!str_starts_with(ltrim($body),'# '))$body='# '.markdownProjectEscape((string)$page['title'])."\n\n".$body;
        $documents[$markdown]=rtrim($body)."\n\nCanonical: ".$page['url']."\n\n".MARKDOWN_PROJECTION_MARKER."\n";
        $preferred[$page['url']]=$base.'/'.$markdown;
    }
    // Resolve every HTML metadata collision before changing any projection file.
    $htmlUpdates=[];$prefix=(string)(parse_url($base,PHP_URL_PATH)??'');
    foreach(discoveryPublicHtmlFiles($root) as $relative=>$path){
        $html=(string)file_get_contents($path);$canonical=discoveryCanonical($html)?:discoveryUrlForRelative($base,$relative);
        $noindex=preg_match('/(?:^|[,\\s])noindex(?:$|[,\\s])/i',discoveryMetaContent($html,'robots'));
        $url=!$noindex?($preferred[$canonical]??null):null;$href=$url===null?null:substr($url,strlen($base)-strlen($prefix));
        $next=markdownProjectUpdateAlternate($html,$href);if($next!==$html)$htmlUpdates[$path]=$next;
    }
    $removed=0;$iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS));
    foreach($iterator as $file){
        if(!$file->isFile()||strtolower($file->getExtension())!=='md')continue;
        $relative=ltrim(substr($file->getPathname(),strlen(rtrim($root,'/'))),'/');
        $path=discoveryContainedFile($root,$relative);if($path===null||isset($documents[$relative]))continue;
        if(markdownProjectOwned((string)file_get_contents($path))){if(!unlink($path))throw new RuntimeException('Could not remove stale Markdown projection: '.$relative);$removed++;}
    }
    foreach($documents as $relative=>$document){$path=$root.'/'.$relative;if(!is_file($path)||file_get_contents($path)!==$document)pageProjectionWrite($path,$document);}
    foreach($htmlUpdates as $path=>$html)pageProjectionWrite($path,$html);
    $alternates=array_keys($documents);sort($alternates,SORT_STRING);
    return ['generated'=>count($documents),'removed'=>$removed,'htmlAlternateLinks'=>count($htmlUpdates),'alternates'=>$alternates,'preferredUrls'=>$preferred,'pages'=>$pages];
}
