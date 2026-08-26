<?php
declare(strict_types=1);
require_once __DIR__.'/content-core.php';
require_once __DIR__.'/post-store.php';

/** Safe Markdown subset and deterministic static long-form projection. */

function postEscape(string $value): string {return htmlspecialchars($value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function postMarkdownSlug(string $value): string {$value=strtolower($value);$value=preg_replace('/[^a-z0-9]+/','-',$value)??'';return trim($value,'-')?:'section';}

function postMarkdownInline(string $value): string {
    $value=postEscape($value);
    $value=preg_replace('/`([^`]+)`/','<code>$1</code>',$value)??$value;
    $value=preg_replace('/\*\*([^*]+)\*\*/','<strong>$1</strong>',$value)??$value;
    $value=preg_replace('/\*([^*]+)\*/','<em>$1</em>',$value)??$value;
    $value=preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/',function($m){
        $url=cmsSafeInlineHref(html_entity_decode($m[2],ENT_QUOTES|ENT_HTML5,'UTF-8'));if($url===null)return $m[1];
        $href=postEscape($url);$scheme=strtolower((string)(parse_url($url,PHP_URL_SCHEME)??''));
        return in_array($scheme,['http','https'],true)?'<a href="'.$href.'" target="_blank" rel="noopener noreferrer">'.$m[1].'</a>':'<a href="'.$href.'">'.$m[1].'</a>';
    },$value)??$value;
    return $value;
}

function renderPostMarkdown(string $markdown): string {
    $lines=preg_split('/\r?\n/',$markdown)?:[];$out=[];$paragraph=[];$ul=[];$ol=[];$quote=[];$headingCounts=[];$code=[];$inCode=false;
    $flush=function()use(&$out,&$paragraph,&$ul,&$ol,&$quote){
        if($paragraph){$out[]='<p>'.postMarkdownInline(implode(' ',$paragraph)).'</p>';$paragraph=[];}
        if($ul){$out[]='<ul>'.implode('',array_map(fn($v)=>'<li>'.postMarkdownInline($v).'</li>',$ul)).'</ul>';$ul=[];}
        if($ol){$out[]='<ol>'.implode('',array_map(fn($v)=>'<li>'.postMarkdownInline($v).'</li>',$ol)).'</ol>';$ol=[];}
        if($quote){$out[]='<blockquote><p>'.postMarkdownInline(implode(' ',$quote)).'</p></blockquote>';$quote=[];}
    };
    foreach($lines as $line){$trim=trim((string)$line);
        if(str_starts_with($trim,'```')){
            if($inCode){$out[]='<pre><code>'.postEscape(implode("\n",$code)).'</code></pre>';$code=[];$inCode=false;}else{$flush();$inCode=true;}
            continue;
        }
        if($inCode){$code[]=(string)$line;continue;}
        if($trim===''){$flush();continue;}
        if($trim==='---'){$flush();$out[]='<hr>';continue;}
        if(preg_match('/^(#{2,4})\s+(.+)$/',$trim,$m)){$flush();$level=strlen($m[1]);$text=$m[2];$base=postMarkdownSlug($text);$headingCounts[$base]=($headingCounts[$base]??0)+1;$id=$headingCounts[$base]===1?$base:$base.'-'.$headingCounts[$base];$out[]='<h'.$level.' id="'.postEscape($id).'">'.postMarkdownInline($text).'</h'.$level.'>';continue;}
        if(str_starts_with($trim,'> ')){$quote[]=substr($trim,2);continue;}
        if(preg_match('/^[-*]\s+(.+)$/',$trim,$m)){$ul[]=$m[1];continue;}
        if(preg_match('/^\d+\.\s+(.+)$/',$trim,$m)){$ol[]=$m[1];continue;}
        $paragraph[]=$trim;
    }
    if($inCode)$out[]='<pre><code>'.postEscape(implode("\n",$code)).'</code></pre>';
    $flush();return implode("\n",$out);
}

function postWordCount(string $body): int {$plain=preg_replace('/\[([^\]]+)\]\([^)]+\)/','$1',$body)??$body;$plain=preg_replace('/[#>*_`\-]+/',' ',$plain)??$plain;return str_word_count(strip_tags($plain));}
function postReadingMinutes(string $body): int {return max(1,(int)round(postWordCount($body)/220));}

function writingConfig(string $key,mixed $default=null): mixed {return siteConfigValue('writing',$key,$default);}
function writingRouteRoot(): string {$root=trim((string)writingConfig('route_root','writing'),'/');return preg_match('/^[A-Za-z0-9._-]+(?:\/[A-Za-z0-9._-]+)*$/',$root)?$root:'writing';}
function writingIndexPath(): string {$path=trim((string)writingConfig('index_path','content/posts/index.json'),'/');return preg_match('/^[A-Za-z0-9._\/-]+$/',$path)?$path:'content/posts/index.json';}
function writingArticlePath(string $slug): string {return writingRouteRoot().'/'.cleanSlug($slug).'/index.html';}
function writingCanonicalUrl(string $slug): string {$base=rtrim((string)siteConfigValue('site','base_url',''),'/');return $base.'/'.$root=writingRouteRoot().'/'.rawurlencode(cleanSlug($slug)).'/';}

function postPublicMeta(array $post): array {
    $keys=['slug','title','dek','category','categoryLabel','date','status','featured','tags','thesis','related','territoryImage','featuredImage','socialImage','imageAlt','updatedAt'];$out=[];
    foreach($keys as $key)$out[$key]=$post[$key]??null;$out['wordCount']=postWordCount((string)($post['body']??''));$out['readingMinutes']=postReadingMinutes((string)($post['body']??''));return $out;
}

function ensureGeneratedDirectory(string $root,string $relativeDir): string {
    $relativeDir=trim(str_replace('\\','/',$relativeDir),'/');if($relativeDir===''||str_contains($relativeDir,'..'))throw new RuntimeException('Generated directory path is invalid.');
    $target=rtrim($root,'/\\').'/'.$relativeDir;if(!is_dir($target)&&!mkdir($target,0775,true)&&!is_dir($target))throw new RuntimeException('Could not create generated directory.');
    $real=realpath($target);if($real===false||!pathInside($real,$root))throw new RuntimeException('Generated directory escaped the public root.');return $real;
}

function renderPostPage(string $root,array $post): string {
    $siteName=(string)siteConfigValue('site','name','Site');$base=rtrim((string)siteConfigValue('site','base_url',''),'/');$route=writingRouteRoot();$canonical=$base.'/'.$route.'/'.rawurlencode($post['slug']).'/';$body=renderPostMarkdown((string)$post['body']);
    $templatePath=trim((string)writingConfig('article_template',''));
    $values=[
        '{{site_name}}'=>postEscape($siteName),'{{title}}'=>postEscape((string)$post['title']),'{{dek}}'=>postEscape((string)$post['dek']),
        '{{category}}'=>postEscape((string)$post['categoryLabel']),'{{date}}'=>postEscape((string)$post['date']),'{{canonical}}'=>postEscape($canonical),
        '{{reading_minutes}}'=>(string)postReadingMinutes((string)$post['body']),'{{body_html}}'=>$body,
    ];
    if($templatePath!==''){
        $full=cmsSafePublicFile($root,$templatePath);if($full===null)throw new RuntimeException('Configured article template is unavailable.');$template=(string)file_get_contents($full);return strtr($template,$values);
    }
    return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.postEscape((string)$post['title']).' — '.postEscape($siteName).'</title><meta name="description" content="'.postEscape((string)$post['dek']).'"><meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1"><link rel="canonical" href="'.postEscape($canonical).'"><meta property="og:type" content="article"><meta property="og:title" content="'.postEscape((string)$post['title']).'"><meta property="og:description" content="'.postEscape((string)$post['dek']).'"><meta property="og:url" content="'.postEscape($canonical).'"><meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="'.postEscape((string)$post['title']).'"><meta name="twitter:description" content="'.postEscape((string)$post['dek']).'"></head><body><main><article><header><p>'.postEscape((string)$post['categoryLabel']).' · '.postEscape((string)$post['date']).' · '.postReadingMinutes((string)$post['body']).' min</p><h1>'.postEscape((string)$post['title']).'</h1><p>'.postEscape((string)$post['dek']).'</p></header><div class="article-body">'.$body.'</div></article></main></body></html>';
}

function projectPost(string $root,array $post): string {
    if(($post['status']??'draft')!=='published')throw new RuntimeException('Only published posts can be projected.');$dir=ensureGeneratedDirectory($root,writingRouteRoot().'/'.$post['slug']);$target=$dir.'/index.html';cmsAtomicWrite($target,renderPostPage($root,$post));return writingArticlePath($post['slug']);
}

function removePostProjection(string $root,string $slug): bool {
    $slug=cleanSlug($slug);if($slug==='')return false;$dir=rtrim($root,'/\\').'/'.writingRouteRoot().'/'.$slug;$target=$dir.'/index.html';
    if(!is_file($target))return false;$real=realpath($target);if($real===false||!pathInside($real,$root))throw new RuntimeException('Generated post path escaped the public root.');if(!unlink($real))throw new RuntimeException('Could not remove unpublished projection.');@rmdir($dir);return true;
}

function rebuildPostIndex(string $root): int {
    $posts=array_map('postPublicMeta',publishedPosts());$relative=writingIndexPath();$dir=ensureGeneratedDirectory($root,dirname($relative));$target=$dir.'/'.basename($relative);$json=json_encode(['version'=>1,'generatedAt'=>gmdate('c'),'source'=>'mysql','posts'=>$posts],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);if(!is_string($json))throw new RuntimeException('Could not encode post index.');cmsAtomicWrite($target,$json.PHP_EOL);return count($posts);
}

function projectPublishedPosts(string $root): array {
    $posts=publishedPosts();$paths=[];foreach($posts as $post)$paths[]=projectPost($root,$post);$indexCount=rebuildPostIndex($root);return ['posts'=>count($paths),'indexPosts'=>$indexCount,'paths'=>$paths];
}
