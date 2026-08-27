<?php
declare(strict_types=1);

/**
 * Typed-value hydration for the live Page Composer.
 *
 * Public HTML is an interaction/read surface only. These helpers recover the
 * already-governed preset values from canonical projected markup so browser
 * editing can stay typed and server-rendered without accepting structural HTML.
 */
function compositionValueRenderedCmsId(string $instanceId,string $baseId): string {
    $safe=preg_replace('/[^A-Za-z0-9_-]/','-',$instanceId)??'instance';
    $base=preg_replace('/[^A-Za-z0-9._:-]/','-',$baseId)??'block';
    return substr('cmp-'.$safe.'-'.substr($base,0,90),0,180);
}

function compositionValueCmsInner(string $html,string $id): ?string {
    $qid=preg_quote($id,'/');
    $pattern='/<(?P<tag>h[1-4]|p|figcaption|td|th|dd|footer|strong|span)\b(?P<attrs>[^>]*\bdata-cms-id=["\']'.$qid.'["\'][^>]*)>(?P<inner>.*?)<\/\k<tag>>/is';
    return preg_match($pattern,$html,$m)?(string)$m['inner']:null;
}

function compositionValueTag(string $html,string $tag,int $index): ?string {
    $q=preg_quote($tag,'/');
    if(!preg_match_all('/<'.$q.'\b[^>]*>/i',$html,$m))return null;
    return isset($m[0][$index])?(string)$m[0][$index]:null;
}

function compositionValueTagInner(string $html,string $tag,int $index): ?string {
    $q=preg_quote($tag,'/');
    if(!preg_match_all('/<'.$q.'\b[^>]*>(.*?)<\/'.$q.'>/is',$html,$m))return null;
    return isset($m[1][$index])?(string)$m[1][$index]:null;
}

function compositionValueNormalizeMediaPath(string $path): string {
    $path=trim(html_entity_decode($path,ENT_QUOTES|ENT_HTML5,'UTF-8'));
    return ltrim($path,'/');
}

function compositionHydratePresetValues(string $root,array $preset,string $html,string $instanceId,array $fallback=[],bool $sourceIds=false): array {
    $variables=(array)($preset['variables']??[]);
    $values=composerTemplateDefaults($variables);
    foreach($fallback as $key=>$value)$values[(string)$key]=$value;
    $mediaIndex=0;$linkIndex=0;$textLinkIndex=0;
    foreach($variables as $variable){
        if(!is_array($variable)||empty($variable['key']))continue;
        $key=(string)$variable['key'];$type=(string)($variable['type']??'text');$target=is_array($variable['target']??null)?$variable['target']:[];
        if($type==='richtext'){
            $id='';
            if(($target['kind']??'')==='cms'){
                $base=(string)($target['id']??'');
                $id=$sourceIds?$base:compositionValueRenderedCmsId($instanceId,$base);
                $inner=$id!==''?compositionValueCmsInner($html,$id):null;
                if($inner===null&&!$sourceIds&&$base!=='')$inner=compositionValueCmsInner($html,$base);
            } elseif(($target['kind']??'')==='primitive') {
                $id=composerPrimitiveCmsId($instanceId,(string)($target['id']??''),(string)($target['field']??'html'));
                $inner=compositionValueCmsInner($html,$id);
            } else $inner=null;
            if($inner!==null)$values[$key]=$inner;
            continue;
        }
        if($type==='media'){
            $tag=compositionValueTag($html,'img',$mediaIndex++);
            if($tag!==null){
                $path=compositionValueNormalizeMediaPath(composerAttr($tag,'src'));$alt=composerAttr($tag,'alt');
                if($path===''||mediaAllowedPath($path))$values[$key]=['path'=>$path,'alt'=>$alt];
            }
            continue;
        }
        if($type==='link'){
            $tag=compositionValueTag($html,'a',$linkIndex++);
            if($tag!==null)$values[$key]=composerAttr($tag,'href');
            continue;
        }
        if($type==='text'){
            $inner=compositionValueTagInner($html,'a',$textLinkIndex++);
            if($inner!==null)$values[$key]=composerText($inner);
        }
    }
    return composerNormalizeValues($root,$variables,$values);
}

/**
 * Convert a browser interaction snapshot back into the preset's typed value
 * schema. The browser may submit copy/media/link field values, but never block
 * HTML or CSS. Unknown leaf identities and out-of-range indexed fields fail
 * closed so a stale or tampered DOM cannot become reusable preset authority.
 */
function compositionTypedSnapshotValues(string $root,array $preset,string $instanceId,array $fallback,array $leafValues,array $mediaValues,array $linkValues): array {
    if(!composerSafeInstanceId($instanceId))throw new RuntimeException('Invalid page block instance identifier.');
    if(count($leafValues)>120||count($mediaValues)>30||count($linkValues)>60)throw new RuntimeException('A page block contains too many editable fields.');
    $variables=(array)($preset['variables']??[]);$values=composerTemplateDefaults($variables);foreach($fallback as $key=>$value)$values[(string)$key]=$value;
    $mediaRows=[];foreach($mediaValues as $row){if(!is_array($row))throw new RuntimeException('Invalid page block image field.');$index=(int)($row['index']??-1);if($index<0||$index>200||isset($mediaRows[$index]))throw new RuntimeException('Invalid page block image field.');$mediaRows[$index]=['path'=>compositionValueNormalizeMediaPath((string)($row['path']??'')),'alt'=>(string)($row['alt']??'')];}
    $linkRows=[];foreach($linkValues as $row){if(!is_array($row))throw new RuntimeException('Invalid page block link field.');$index=(int)($row['index']??-1);if($index<0||$index>300||isset($linkRows[$index]))throw new RuntimeException('Invalid page block link field.');$linkRows[$index]=['href'=>(string)($row['href']??''),'text'=>(string)($row['text']??'')];}
    $allowedLeaf=[];$mediaIndex=0;$linkIndex=0;$textIndex=0;
    foreach($variables as $variable){if(!is_array($variable)||empty($variable['key']))continue;$key=(string)$variable['key'];$type=(string)($variable['type']??'text');$target=is_array($variable['target']??null)?$variable['target']:[];
        if($type==='richtext'){$id='';if(($target['kind']??'')==='cms')$id=compositionValueRenderedCmsId($instanceId,(string)($target['id']??''));elseif(($target['kind']??'')==='primitive')$id=composerPrimitiveCmsId($instanceId,(string)($target['id']??''),(string)($target['field']??'html'));if($id!==''){$allowedLeaf[$id]=true;if(array_key_exists($id,$leafValues))$values[$key]=(string)$leafValues[$id];}continue;}
        if($type==='media'){if(isset($mediaRows[$mediaIndex]))$values[$key]=$mediaRows[$mediaIndex];$mediaIndex++;continue;}
        if($type==='link'){if(isset($linkRows[$linkIndex]))$values[$key]=$linkRows[$linkIndex]['href'];$linkIndex++;continue;}
        if($type==='text'){if(isset($linkRows[$textIndex]))$values[$key]=$linkRows[$textIndex]['text'];$textIndex++;}
    }
    foreach($leafValues as $id=>$value){$id=(string)$id;if(!isset($allowedLeaf[$id]))throw new RuntimeException('Page block editable fields changed since selection. Refresh Page Composer and try again.');}
    foreach(array_keys($mediaRows) as $index)if($index>=$mediaIndex)throw new RuntimeException('Page block image fields changed since selection. Refresh Page Composer and try again.');
    $linkLimit=max($linkIndex,$textIndex);foreach(array_keys($linkRows) as $index)if($index>=$linkLimit)throw new RuntimeException('Page block link fields changed since selection. Refresh Page Composer and try again.');
    return composerNormalizeValues($root,$variables,$values);
}
