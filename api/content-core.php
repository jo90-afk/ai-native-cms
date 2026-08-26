<?php
declare(strict_types=1);
require_once __DIR__.'/database.php';

/** Generic filesystem/content primitives shared by the CMS authority layer. */

function cmsConfiguredPages(string $root): array {
    $configured=siteConfigValue('cms','editable_pages',[]);
    if(!is_array($configured)) return [];
    $out=[];
    foreach($configured as $path=>$label){
        $path=str_replace('\\','/',trim((string)$path));
        if($path===''||str_starts_with($path,'/')||str_contains($path,"\0")) continue;
        $parts=explode('/',$path);$valid=true;
        foreach($parts as $part) if($part===''||$part==='.'||$part==='..'){$valid=false;break;}
        if(!$valid) continue;
        $out[$path]=trim((string)$label)!==''?(string)$label:$path;
    }
    ksort($out,SORT_STRING);return $out;
}

function cmsManagedPages(string $root): array {
    $out=cmsConfiguredPages($root);
    if(dbConfigured()){
        try{
            $stmt=db()->query('SELECT page_path,label FROM page_compositions ORDER BY page_path');
            foreach($stmt->fetchAll() as $row){
                $path=str_replace('\\','/',trim((string)($row['page_path']??'')));if($path===''||str_starts_with($path,'/')||str_contains($path,"\0"))continue;$valid=true;
                foreach(explode('/',$path) as $part)if($part===''||$part==='.'||$part==='..'){$valid=false;break;}if(!$valid)continue;
                $label=trim((string)($row['label']??''));$out[$path]=$label!==''?$label:$path;
            }
        }catch(Throwable $e){/* Dynamic pages are unavailable until the canonical schema exists. */}
    }
    ksort($out,SORT_STRING);return $out;
}

function cmsConfiguredDocuments(string $root): array {
    $configured=siteConfigValue('cms','documents',[]);
    if(!is_array($configured)) return [];
    $out=[];
    foreach($configured as $path=>$spec){
        if(!is_array($spec)) continue;
        $path=str_replace('\\','/',trim((string)$path));
        if($path===''||str_starts_with($path,'/')||str_contains($path,"\0")) continue;
        $parts=explode('/',$path);$valid=true;
        foreach($parts as $part) if($part===''||$part==='.'||$part==='..'){$valid=false;break;}
        if(!$valid) continue;
        $out[$path]=[
            'sourcePath'=>$path,
            'targetPath'=>$path,
            'type'=>substr(trim((string)($spec['type']??'document')),0,64)?:'document',
            'label'=>substr(trim((string)($spec['label']??$path)),0,191)?:$path,
            'format'=>substr(trim((string)($spec['format']??pathinfo($path,PATHINFO_EXTENSION)?:'text')),0,32),
        ];
    }
    ksort($out,SORT_STRING);return $out;
}

function cmsSafeRelativePath(string $path): ?string {
    $path=str_replace('\\','/',trim($path));if($path===''||str_starts_with($path,'/')||str_contains($path,"\0"))return null;
    foreach(explode('/',$path) as $part)if($part===''||$part==='.'||$part==='..')return null;
    return $path;
}

function cmsSafePublicFile(string $root,string $path): ?string {
    $path=cmsSafeRelativePath($path);if($path===null)return null;
    $full=rtrim($root,'/\\').'/'.$path;$real=realpath($full);
    if($real===false||!is_file($real)||!pathInside($real,$root)) return null;
    return $real;
}

function cmsSafePublicTarget(string $root,string $path): ?string {
    $path=cmsSafeRelativePath($path);if($path===null)return null;$full=rtrim($root,'/\\').'/'.$path;$parent=realpath(dirname($full));
    if($parent===false||!is_dir($parent)||!pathInside($parent,$root))return null;return $full;
}

function cmsAtomicWrite(string $path,string $contents): void {
    $parent=dirname($path);$realParent=realpath($parent);
    if($realParent===false||!is_dir($realParent)) throw new RuntimeException('Target directory is unavailable.');
    $tmp=$path.'.tmp.'.bin2hex(random_bytes(4));
    if(file_put_contents($tmp,$contents,LOCK_EX)===false) throw new RuntimeException('Could not write temporary file.');
    if(!rename($tmp,$path)){@unlink($tmp);throw new RuntimeException('Could not replace file.');}
}

function cmsExtractEditableBlocks(string $html): array {
    $blocks=[];
    if(preg_match_all('/<(?P<tag>h[1-4]|p|figcaption|td|th|dd)\b(?P<attrs>[^>]*\bdata-cms-id=["\'](?P<id>[^"\']+)["\'][^>]*)>(?P<inner>.*?)<\/\1>/is',$html,$matches,PREG_SET_ORDER)){
        foreach($matches as $m)$blocks[]=['id'=>$m['id'],'tag'=>strtolower($m['tag']),'html'=>$m['inner'],'hash'=>hash('sha256',$m['inner'])];
    }
    return $blocks;
}

function cmsSafeInlineHref(string $raw): ?string {
    $value=trim(html_entity_decode($raw,ENT_QUOTES|ENT_HTML5,'UTF-8'));
    $value=preg_replace('/[\x00-\x20\x7f]+/u','',$value)??'';
    if($value==='') return null;
    if(preg_match('#^(?:/(?!/)|\./|\.\./|\#)#',$value)) return $value;
    $scheme=strtolower((string)(parse_url($value,PHP_URL_SCHEME)??''));
    return in_array($scheme,['http','https','mailto','tel'],true)?$value:null;
}

function cmsSanitizeRichHtml(string $html): string {
    $html=strip_tags($html,'<strong><b><em><i><a><code><br><span>');
    $html=preg_replace_callback('/<\s*(strong|b|em|i|a|code|br|span)\b([^>]*)>/i',function($m){
        $tag=strtolower($m[1]);$attrs=(string)$m[2];
        if($tag==='b')$tag='strong';if($tag==='i')$tag='em';if($tag==='br')return '<br>';
        if($tag==='a'){
            $href=null;
            if(preg_match('/\bhref\s*=\s*(["\'])(.*?)\1/is',$attrs,$hm))$href=cmsSafeInlineHref($hm[2]);
            elseif(preg_match('/\bhref\s*=\s*([^\s>]+)/i',$attrs,$hm))$href=cmsSafeInlineHref($hm[1]);
            return $href===null?'<a>':'<a href="'.htmlspecialchars($href,ENT_QUOTES|ENT_HTML5,'UTF-8').'">';
        }
        if($tag==='span') return '<span>';
        return '<'.$tag.'>';
    },$html)??'';
    return trim($html);
}

function cmsReplaceEditableBlock(string $html,string $id,string $replacement,?string $expectedHash=null): string {
    $qid=preg_quote($id,'/');
    $pattern='/<(?P<tag>h[1-4]|p|figcaption|td|th|dd)\b(?P<attrs>[^>]*\bdata-cms-id=["\']'.$qid.'["\'][^>]*)>(?P<inner>.*?)<\/\1>/is';
    if(!preg_match($pattern,$html,$m)) throw new RuntimeException('Editable block no longer exists: '.$id);
    if($expectedHash!==null&&$expectedHash!==''&&!hash_equals($expectedHash,hash('sha256',$m['inner']))) throw new RuntimeException('Page changed since it was opened. Refresh before saving.');
    $new='<'.$m['tag'].$m['attrs'].'>'.cmsSanitizeRichHtml($replacement).'</'.$m['tag'].'>';
    return preg_replace_callback($pattern,fn()=>$new,$html,1)??$html;
}

function cmsPageTitle(string $html): string {
    return preg_match('/<title>(.*?)<\/title>/is',$html,$m)?trim(html_entity_decode(strip_tags($m[1]),ENT_QUOTES|ENT_HTML5,'UTF-8')):'';
}

function cmsPublicPageInfo(string $root,string $path,string $label): array {
    $full=cmsSafePublicFile($root,$path);if($full===null)throw new RuntimeException('Page path is outside the public site boundary.');
    $html=(string)file_get_contents($full);preg_match_all('/\bdata-cms-id=["\'][^"\']+["\']/i',$html,$ids);
    return ['path'=>$path,'label'=>$label,'title'=>cmsPageTitle($html),'modified'=>date(DATE_ATOM,filemtime($full)?:time()),'editableCount'=>count($ids[0])];
}
