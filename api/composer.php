<?php
declare(strict_types=1);
require_once __DIR__.'/content-core.php';
require_once __DIR__.'/media.php';

/** Server-owned structural templates with typed copy/media/link values. */

function composerMainParts(string $html): array {
    if(!preg_match('/<main\b[^>]*>/i',$html,$m,PREG_OFFSET_CAPTURE))throw new RuntimeException('Page shell has no main element.');$open=(string)$m[0][0];$innerStart=(int)$m[0][1]+strlen($open);$closeStart=stripos($html,'</main>',$innerStart);if($closeStart===false)throw new RuntimeException('Page shell main element is not closed.');
    return ['prefix'=>substr($html,0,$innerStart),'inner'=>substr($html,$innerStart,$closeStart-$innerStart),'suffix'=>substr($html,$closeStart)];
}
function composerReplaceMain(string $html,array $blocks): string {$parts=composerMainParts($html);return $parts['prefix']."\n".implode("\n",$blocks)."\n".$parts['suffix'];}
function composerExtractTopBlocks(string $html): array {
    $inner=composerMainParts($html)['inner'];$blocks=[];
    if(class_exists('DOMDocument')){$dom=new DOMDocument('1.0','UTF-8');$previous=libxml_use_internal_errors(true);$wrapped='<!doctype html><html><body><div id="__aincms_composer_root__">'.$inner.'</div></body></html>';$dom->loadHTML('<?xml encoding="utf-8" ?>'.$wrapped,LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);$root=$dom->getElementById('__aincms_composer_root__');if($root)foreach($root->childNodes as $node)if($node instanceof DOMElement){$chunk=$dom->saveHTML($node);if(is_string($chunk)&&trim($chunk)!=='')$blocks[]=trim($chunk);}libxml_clear_errors();libxml_use_internal_errors($previous);}
    if($blocks)return $blocks;if(preg_match_all('/<(section|article|div|header|figure)\b[^>]*>.*?<\/\1>/is',$inner,$m))foreach($m[0] as $chunk)$blocks[]=trim((string)$chunk);return $blocks;
}
function composerAttr(string $html,string $name): string {$q=preg_quote($name,'/');return preg_match('/\b'.$q.'=["\']([^"\']*)["\']/i',$html,$m)?html_entity_decode((string)$m[1],ENT_QUOTES|ENT_HTML5,'UTF-8'):'';}
function composerText(string $html): string {return trim(preg_replace('/\s+/u',' ',html_entity_decode(strip_tags($html),ENT_QUOTES|ENT_HTML5,'UTF-8'))??'');}
function composerSubstr(string $value,int $length): string {return function_exists('mb_substr')?mb_substr($value,0,$length):substr($value,0,$length);}
function composerTemplateKey(string $path,int $ordinal): string {$slug=cleanSlug(pathinfo($path,PATHINFO_FILENAME))?:'page';return substr($slug.'-'.str_pad((string)($ordinal+1),2,'0',STR_PAD_LEFT).'-'.substr(hash('sha256',$path.'|'.$ordinal),0,16),0,191);}
function composerVariableKey(string $prefix,string $seed,int $index): string {$seed=cleanSlug($seed);return substr($prefix.'-'.($seed!==''?$seed:(string)$index),0,100);}
function composerTemplateLabel(string $html,int $ordinal): string {if(preg_match('/<h[1-3]\b[^>]*>(.*?)<\/h[1-3]>/is',$html,$m)){$text=composerText($m[1]);if($text!=='')return composerSubstr($text,120);}return 'Page block '.($ordinal+1);}
function composerTemplateCategory(string $html): string {$head=strtolower(substr($html,0,500));foreach(['hero'=>'Hero','contact'=>'Contact','writing'=>'Writing','article'=>'Writing','project'=>'Work','metric'=>'Metrics','quote'=>'Quote','grid'=>'Grid','band'=>'Band','cta'=>'Call to action'] as $needle=>$label)if(str_contains($head,$needle))return $label;return 'Section';}

function composerTemplateVariables(string $html): array {
    $vars=[];$seen=[];
    if(preg_match_all('/<(?P<tag>h[1-4]|p|figcaption|td|th|dd)\b(?P<attrs>[^>]*\bdata-cms-id=["\'](?P<id>[^"\']+)["\'][^>]*)>(?P<inner>.*?)<\/\1>/is',$html,$matches,PREG_SET_ORDER))foreach($matches as $i=>$m){$key=composerVariableKey('copy',(string)$m['id'],$i+1);if(isset($seen[$key]))$key.='-'.($i+1);$seen[$key]=true;$vars[]=['key'=>$key,'type'=>'richtext','label'=>ucfirst((string)$m['tag']).' · '.composerSubstr(composerText((string)$m['inner']),70),'target'=>['kind'=>'cms','id'=>(string)$m['id']],'default'=>(string)$m['inner']];}
    if(preg_match_all('/<img\b[^>]*>/i',$html,$images))foreach($images[0] as $i=>$tag)$vars[]=['key'=>'media-'.($i+1),'type'=>'media','label'=>'Image '.($i+1),'target'=>['kind'=>'image','index'=>$i],'default'=>['path'=>composerAttr($tag,'src'),'alt'=>composerAttr($tag,'alt')]];
    if(preg_match_all('/<a\b[^>]*\bhref=["\'][^"\']*["\'][^>]*>/i',$html,$links))foreach($links[0] as $i=>$tag)$vars[]=['key'=>'link-'.($i+1),'type'=>'link','label'=>'Link '.($i+1),'target'=>['kind'=>'link','index'=>$i],'default'=>composerAttr($tag,'href')];
    return $vars;
}
function composerTemplateDefaults(array $variables): array {$out=[];foreach($variables as $v)if(is_array($v)&&isset($v['key']))$out[(string)$v['key']]=$v['default']??'';return $out;}
function composerSafeHref(string $href): bool {$href=trim($href);return $href===''||cmsSafeInlineHref($href)!==null;}
function composerNormalizeValues(string $root,array $variables,array $input): array {
    $out=[];
    foreach($variables as $v){if(!is_array($v)||empty($v['key']))continue;$key=(string)$v['key'];$type=(string)($v['type']??'text');$value=array_key_exists($key,$input)?$input[$key]:($v['default']??'');
        if($type==='richtext')$out[$key]=cmsSanitizeRichHtml((string)$value);
        elseif($type==='media'){$default=is_array($v['default']??null)?$v['default']:['path'=>'','alt'=>''];$value=is_array($value)?$value:[];$path=trim((string)($value['path']??$default['path']??''));$alt=composerSubstr(trim((string)($value['alt']??$default['alt']??'')),1000);if($path!==''&&(!mediaAllowedPath($path)||cmsSafePublicFile($root,$path)===null))throw new RuntimeException('A selected template image is unavailable.');$out[$key]=['path'=>$path,'alt'=>$alt];}
        elseif($type==='link'){$href=trim((string)$value);if(!composerSafeHref($href))throw new RuntimeException('A template link target is invalid.');$out[$key]=composerSubstr($href,512);}
        else $out[$key]=composerSubstr(trim((string)$value),2000);
    }
    return $out;
}
function composerSetTagAttribute(string $tag,string $name,string $value): string {$encoded=htmlspecialchars($value,ENT_QUOTES|ENT_HTML5,'UTF-8');$q=preg_quote($name,'/');if(preg_match('/\b'.$q.'=["\'][^"\']*["\']/i',$tag))return preg_replace('/\b'.$q.'=["\'][^"\']*["\']/i',$name.'="'.$encoded.'"',$tag,1)??$tag;return preg_replace('/\s*\/?>$/',' '.$name.'="'.$encoded.'"'.(str_ends_with(trim($tag),'/>')?'/>':'>'),$tag,1)??$tag;}
function composerApplyValues(string $root,string $html,array $variables,array $values): string {
    foreach($variables as $v){if(!is_array($v)||empty($v['key'])||!is_array($v['target']??null))continue;$key=(string)$v['key'];$target=$v['target'];if(!array_key_exists($key,$values))continue;if(($target['kind']??'')==='cms'){$id=preg_quote((string)$target['id'],'/');$replacement=(string)$values[$key];$html=preg_replace_callback('/(<(?P<tag>h[1-4]|p|figcaption|td|th|dd)\b[^>]*\bdata-cms-id=["\']'.$id.'["\'][^>]*>).*?(<\/\k<tag>>)/is',fn($m)=>$m[1].$replacement.$m[3],$html,1)??$html;}}
    $media=[];$links=[];foreach($variables as $v){if(!is_array($v)||empty($v['key'])||!is_array($v['target']??null))continue;$kind=(string)($v['target']['kind']??'');$idx=(int)($v['target']['index']??-1);if($kind==='image')$media[$idx]=$values[(string)$v['key']]??$v['default'];if($kind==='link')$links[$idx]=$values[(string)$v['key']]??$v['default'];}
    if($media){$n=-1;$html=preg_replace_callback('/<img\b[^>]*>/i',function($m)use(&$n,$media){$n++;if(!isset($media[$n])||!is_array($media[$n]))return $m[0];$tag=composerSetTagAttribute($m[0],'src',(string)($media[$n]['path']??''));return composerSetTagAttribute($tag,'alt',(string)($media[$n]['alt']??''));},$html)??$html;}
    if($links){$n=-1;$html=preg_replace_callback('/<a\b[^>]*>/i',function($m)use(&$n,$links){$n++;return array_key_exists($n,$links)?composerSetTagAttribute($m[0],'href',(string)$links[$n]):$m[0];},$html)??$html;}
    return $html;
}
function composerSafeInstanceId(string $id): bool {return preg_match('/^[A-Za-z0-9_-]{3,80}$/',$id)===1;}
function composerRekeyEditableIds(string $html,string $instanceId): string {$safe=preg_replace('/[^A-Za-z0-9_-]/','-',$instanceId)??'instance';return preg_replace_callback('/\bdata-cms-id=(["\'])([^"\']+)\1/i',function($m)use($safe){$base=preg_replace('/[^A-Za-z0-9._:-]/','-',(string)$m[2])??'block';$id=substr('cmp-'.$safe.'-'.substr($base,0,90),0,180);return 'data-cms-id='.$m[1].$id.$m[1];},$html)??$html;}
function composerAnnotateBlock(string $html,string $instanceId): string {return preg_replace('/^<([A-Za-z][A-Za-z0-9:-]*)\b/','<$1 data-composer-instance="'.htmlspecialchars($instanceId,ENT_QUOTES|ENT_HTML5,'UTF-8').'"',$html,1)??$html;}

function composerSourceHtml(string $root,string $path): string {
    $stmt=db()->prepare('SELECT content FROM content_documents WHERE document_key=?');$stmt->execute(['@page/'.$path]);$content=$stmt->fetchColumn();if(is_string($content)&&$content!=='')return $content;$full=cmsSafePublicFile($root,$path);if($full===null)throw new RuntimeException('Managed page source is unavailable.');return (string)file_get_contents($full);
}
function composerReferencedTemplateKeys(): array {$out=[];foreach(db()->query('SELECT blocks_json FROM page_compositions')->fetchAll() as $row){$blocks=dbJsonDecode((string)$row['blocks_json']);foreach($blocks as $block)if(is_array($block)&&!empty($block['templateKey']))$out[(string)$block['templateKey']]=true;}return $out;}

function composerRefreshTemplates(string $root): array {
    $pdo=db();$rows=$pdo->query('SELECT template_key,source_page,source_ordinal,source_hash FROM page_block_templates')->fetchAll();$bySlot=[];$byHash=[];$all=[];
    foreach($rows as $row){$key=(string)$row['template_key'];$bySlot[(string)$row['source_page'].'|'.(int)$row['source_ordinal']]=$row;$hash=(string)$row['source_hash'];if($hash!=='')$byHash[$hash][]=$key;$all[$key]=true;}
    $referenced=composerReferencedTemplateKeys();$used=[];$count=0;$pdo->beginTransaction();
    try{$upsert=$pdo->prepare('INSERT INTO page_block_templates (template_key,label,category,source_page,source_ordinal,source_hash,html_content,variables_json,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE label=VALUES(label),category=VALUES(category),source_page=VALUES(source_page),source_ordinal=VALUES(source_ordinal),source_hash=VALUES(source_hash),html_content=VALUES(html_content),variables_json=VALUES(variables_json),updated_at=UTC_TIMESTAMP()');
        foreach(cmsConfiguredPages($root) as $path=>$label){$html=composerSourceHtml($root,$path);foreach(composerExtractTopBlocks($html) as $i=>$block){$hash=hash('sha256',$block);$slot=$path.'|'.$i;$slotRow=$bySlot[$slot]??null;$key=null;if(is_array($slotRow)&&(string)$slotRow['source_hash']===$hash&&!isset($used[(string)$slotRow['template_key']]))$key=(string)$slotRow['template_key'];if($key===null&&isset($byHash[$hash]))foreach($byHash[$hash] as $candidate)if(!isset($used[$candidate])){$key=$candidate;break;}if($key===null&&is_array($slotRow)&&!isset($used[(string)$slotRow['template_key']))$key=(string)$slotRow['template_key'];if($key===null){$base=composerTemplateKey($path,$i);$key=$base;$n=0;while(isset($used[$key])||isset($all[$key])){$n++;$key=substr($base,0,174).'-'.substr(hash('sha256',$hash.'|'.$n),0,12);}}
            $variables=composerTemplateVariables($block);$upsert->execute([$key,composerTemplateLabel($block,$i),composerTemplateCategory($block),$path,$i,$hash,$block,dbJsonEncode($variables)]);$used[$key]=true;$all[$key]=true;$count++;}}
        $delete=$pdo->prepare('DELETE FROM page_block_templates WHERE template_key=?');foreach($rows as $row){$key=(string)$row['template_key'];if(!isset($used[$key])&&!isset($referenced[$key]))$delete->execute([$key]);}$pdo->commit();return ['templates'=>$count];
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
}
function composerTemplates(): array {$rows=db()->query('SELECT template_key,label,category,source_page,source_ordinal,source_hash,variables_json,updated_at FROM page_block_templates ORDER BY category,label,template_key')->fetchAll();$out=[];foreach($rows as $row){$variables=dbJsonDecode((string)$row['variables_json']);$out[]=['key'=>(string)$row['template_key'],'label'=>(string)$row['label'],'category'=>(string)$row['category'],'sourcePage'=>(string)$row['source_page'],'sourceOrdinal'=>(int)$row['source_ordinal'],'sourceHash'=>(string)$row['source_hash'],'variables'=>$variables,'defaults'=>composerTemplateDefaults($variables),'updatedAt'=>dbIso((string)$row['updated_at'])];}return $out;}
function composerTemplateRecord(string $key): ?array {$stmt=db()->prepare('SELECT template_key,label,category,html_content,variables_json FROM page_block_templates WHERE template_key=?');$stmt->execute([$key]);$row=$stmt->fetch();if(!is_array($row))return null;$variables=dbJsonDecode((string)$row['variables_json']);return ['key'=>(string)$row['template_key'],'label'=>(string)$row['label'],'category'=>(string)$row['category'],'html'=>(string)$row['html_content'],'variables'=>$variables,'defaults'=>composerTemplateDefaults($variables)];}
function composerRenderTemplate(string $root,string $key,array $input,string $instanceId): array {if(!composerSafeInstanceId($instanceId))throw new RuntimeException('Invalid block instance identifier.');$record=composerTemplateRecord($key);if(!$record)throw new RuntimeException('Template not found.');$values=composerNormalizeValues($root,$record['variables'],$input);$html=composerApplyValues($root,$record['html'],$record['variables'],$values);$html=composerRekeyEditableIds($html,$instanceId);$html=composerAnnotateBlock($html,$instanceId);return ['html'=>$html,'values'=>$values,'template'=>$record];}
