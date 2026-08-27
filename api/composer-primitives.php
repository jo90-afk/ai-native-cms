<?php
declare(strict_types=1);

/** Governed semantic primitives for reusable block presets. Browser clients submit data, never structural HTML or CSS. */
function composerPrimitiveCatalog(): array {
    return [
        'layouts'=>[
            ['key'=>'stack','label'=>'Stack','description'=>'One reading column.','columns'=>1],
            ['key'=>'split','label'=>'Split','description'=>'Two responsive columns.','columns'=>2],
            ['key'=>'grid3','label'=>'Three columns','description'=>'Three responsive columns.','columns'=>3],
        ],
        'surfaces'=>[
            ['key'=>'plain','label'=>'Plain'],['key'=>'soft','label'=>'Soft band'],['key'=>'card','label'=>'Card'],['key'=>'accent','label'=>'Accent'],['key'=>'dark','label'=>'Dark'],
        ],
        'widths'=>[
            ['key'=>'wide','label'=>'Wide'],['key'=>'medium','label'=>'Medium'],['key'=>'text','label'=>'Text'],
        ],
        'spacing'=>[
            ['key'=>'compact','label'=>'Compact'],['key'=>'standard','label'=>'Standard'],['key'=>'roomy','label'=>'Roomy'],
        ],
        'elements'=>[
            ['type'=>'eyebrow','label'=>'Eyebrow','description'=>'Short orienting label.','default'=>['html'=>'Section label']],
            ['type'=>'heading','label'=>'Heading','description'=>'Semantic H1, H2, or H3.','default'=>['html'=>'A clear heading','level'=>'h2','scale'=>'section']],
            ['type'=>'paragraph','label'=>'Paragraph','description'=>'Lead or body copy.','default'=>['html'=>'Add the copy this block needs.','style'=>'body']],
            ['type'=>'image','label'=>'Image','description'=>'First-party media with alt text.','default'=>['path'=>'','alt'=>'','caption'=>'']],
            ['type'=>'button','label'=>'Button','description'=>'Safe internal or external action.','default'=>['label'=>'Learn more','href'=>'','tone'=>'primary']],
            ['type'=>'quote','label'=>'Quote','description'=>'Quotation with optional attribution.','default'=>['html'=>'A concise quotation.','cite'=>'']],
            ['type'=>'metric','label'=>'Metric','description'=>'A value and explanatory label.','default'=>['value'=>'10×','label'=>'Example metric']],
            ['type'=>'divider','label'=>'Divider','description'=>'Structural separator.','default'=>[]],
        ],
        'limits'=>['elements'=>18,'images'=>6,'buttons'=>8,'h1'=>1],
    ];
}
function composerPrimitiveSubstr(string $value,int $length): string {return function_exists('mb_substr')?mb_substr($value,0,$length):substr($value,0,$length);}
function composerPrimitiveChoice(array $rows,string $value,string $fallback): string {foreach($rows as $row)if(is_array($row)&&($row['key']??null)===$value)return $value;return $fallback;}
function composerPrimitiveId(string $value,string $fallback): string {return preg_match('/^[A-Za-z0-9_-]{3,64}$/',$value)===1?$value:$fallback;}
function composerPrimitiveRich(string $value,int $limit=12000): string {return cmsSanitizeRichHtml(composerPrimitiveSubstr($value,$limit));}
function composerPrimitivePlain(string $value,int $limit=2000): string {return composerPrimitiveSubstr(trim(html_entity_decode(strip_tags($value),ENT_QUOTES|ENT_HTML5,'UTF-8')),$limit);}
function composerPrimitiveColumns(string $layout): int {return $layout==='split'?2:($layout==='grid3'?3:1);}
function composerPrimitiveDefault(string $type): array {foreach(composerPrimitiveCatalog()['elements'] as $row)if(($row['type']??'')===$type)return (array)($row['default']??[]);return [];}
function composerPrimitiveNormalizeElement(string $root,array $element,int $ordinal,int $columns): array {
    $allowed=['eyebrow','heading','paragraph','image','button','quote','metric','divider'];$type=in_array((string)($element['type']??''),$allowed,true)?(string)$element['type']:'paragraph';
    $fallback='el-'.str_pad((string)$ordinal,2,'0',STR_PAD_LEFT).'-'.substr(hash('sha256',json_encode($element).$ordinal),0,8);$id=composerPrimitiveId((string)($element['id']??''),$fallback);$base=['id'=>$id,'type'=>$type,'column'=>max(1,min($columns,(int)($element['column']??1)))];
    if($type==='eyebrow')return $base+['html'=>composerPrimitiveRich((string)($element['html']??'Section label'),2000)];
    if($type==='heading')return $base+['html'=>composerPrimitiveRich((string)($element['html']??'A clear heading'),6000),'level'=>in_array((string)($element['level']??'h2'),['h1','h2','h3'],true)?(string)$element['level']:'h2','scale'=>in_array((string)($element['scale']??'section'),['display','section','subhead'],true)?(string)$element['scale']:'section'];
    if($type==='paragraph')return $base+['html'=>composerPrimitiveRich((string)($element['html']??'Add the copy this block needs.'),12000),'style'=>in_array((string)($element['style']??'body'),['lead','body','small'],true)?(string)$element['style']:'body'];
    if($type==='image'){$path=trim((string)($element['path']??''));if($path!==''&&(!mediaAllowedPath($path)||cmsSafePublicFile($root,$path)===null))throw new RuntimeException('A selected block image is unavailable.');return $base+['path'=>$path,'alt'=>composerPrimitiveSubstr(trim((string)($element['alt']??'')),1000),'caption'=>composerPrimitiveRich((string)($element['caption']??''),4000)];}
    if($type==='button'){$href=trim((string)($element['href']??''));if(!composerSafeHref($href))throw new RuntimeException('A block button target is invalid.');return $base+['label'=>composerPrimitivePlain((string)($element['label']??'Learn more'),160),'href'=>composerPrimitiveSubstr($href,512),'tone'=>in_array((string)($element['tone']??'primary'),['primary','secondary','ghost'],true)?(string)$element['tone']:'primary'];}
    if($type==='quote')return $base+['html'=>composerPrimitiveRich((string)($element['html']??'A concise quotation.'),8000),'cite'=>composerPrimitiveRich((string)($element['cite']??''),2000)];
    if($type==='metric')return $base+['value'=>composerPrimitiveRich((string)($element['value']??'10×'),600),'label'=>composerPrimitiveRich((string)($element['label']??'Example metric'),1800)];
    return $base;
}
function composerPrimitiveNormalize(string $root,array $definition,string $instanceId): array {
    if(!composerSafeInstanceId($instanceId))throw new RuntimeException('Invalid designed-block instance identifier.');$catalog=composerPrimitiveCatalog();
    $layout=composerPrimitiveChoice($catalog['layouts'],(string)($definition['layout']??'stack'),'stack');$surface=composerPrimitiveChoice($catalog['surfaces'],(string)($definition['surface']??'plain'),'plain');$width=composerPrimitiveChoice($catalog['widths'],(string)($definition['width']??'wide'),'wide');$spacing=composerPrimitiveChoice($catalog['spacing'],(string)($definition['spacing']??'standard'),'standard');$label=composerPrimitivePlain((string)($definition['label']??'Designed block'),120)?:'Designed block';
    $input=$definition['elements']??[];if(!is_array($input)||count($input)>(int)$catalog['limits']['elements'])throw new RuntimeException('A designed block has too many elements.');$columns=composerPrimitiveColumns($layout);$elements=[];$ids=[];$images=0;$buttons=0;$h1=0;
    foreach(array_values($input) as $i=>$raw){if(!is_array($raw))continue;$el=composerPrimitiveNormalizeElement($root,$raw,$i+1,$columns);if(isset($ids[$el['id']]))$el['id'].='-'.($i+1);$ids[$el['id']]=true;if($el['type']==='image')$images++;if($el['type']==='button')$buttons++;if($el['type']==='heading'&&$el['level']==='h1')$h1++;$elements[]=$el;}
    if(!$elements)throw new RuntimeException('A designed block needs at least one primitive element.');if($images>6||$buttons>8||$h1>1)throw new RuntimeException('Designed block limits were exceeded.');return ['label'=>$label,'layout'=>$layout,'surface'=>$surface,'width'=>$width,'spacing'=>$spacing,'elements'=>$elements];
}
function composerPrimitiveVariables(array $definition): array {
    $vars=[];foreach($definition['elements']??[] as $el){if(!is_array($el)||empty($el['id'])||empty($el['type']))continue;$id=(string)$el['id'];$type=(string)$el['type'];$base='primitive-'.$id.'-';
        if(in_array($type,['eyebrow','heading','paragraph'],true))$vars[]=['key'=>$base.'html','type'=>'richtext','label'=>ucfirst($type).' copy','target'=>['kind'=>'primitive','id'=>$id,'field'=>'html'],'default'=>(string)($el['html']??'')];
        elseif($type==='image'){$vars[]=['key'=>$base.'media','type'=>'media','label'=>'Image','target'=>['kind'=>'primitive','id'=>$id,'field'=>'media'],'default'=>['path'=>(string)($el['path']??''),'alt'=>(string)($el['alt']??'')]];$vars[]=['key'=>$base.'caption','type'=>'richtext','label'=>'Image caption','target'=>['kind'=>'primitive','id'=>$id,'field'=>'caption'],'default'=>(string)($el['caption']??'')];}
        elseif($type==='button'){$vars[]=['key'=>$base.'label','type'=>'text','label'=>'Button label','target'=>['kind'=>'primitive','id'=>$id,'field'=>'label'],'default'=>(string)($el['label']??'')];$vars[]=['key'=>$base.'href','type'=>'link','label'=>'Button destination','target'=>['kind'=>'primitive','id'=>$id,'field'=>'href'],'default'=>(string)($el['href']??'')];}
        elseif($type==='quote'){$vars[]=['key'=>$base.'html','type'=>'richtext','label'=>'Quote','target'=>['kind'=>'primitive','id'=>$id,'field'=>'html'],'default'=>(string)($el['html']??'')];$vars[]=['key'=>$base.'cite','type'=>'richtext','label'=>'Attribution','target'=>['kind'=>'primitive','id'=>$id,'field'=>'cite'],'default'=>(string)($el['cite']??'')];}
        elseif($type==='metric'){$vars[]=['key'=>$base.'value','type'=>'richtext','label'=>'Metric value','target'=>['kind'=>'primitive','id'=>$id,'field'=>'value'],'default'=>(string)($el['value']??'')];$vars[]=['key'=>$base.'label','type'=>'richtext','label'=>'Metric label','target'=>['kind'=>'primitive','id'=>$id,'field'=>'label'],'default'=>(string)($el['label']??'')];}
    }return $vars;
}
function composerPrimitiveApplyValues(string $root,array $definition,array $variables,array $input): array {
    $normalized=composerNormalizeValues($root,$variables,$input);$byId=[];foreach($definition['elements']??[] as $i=>$el)if(is_array($el)&&isset($el['id']))$byId[(string)$el['id']]=$i;
    foreach($variables as $v){if(!is_array($v)||!is_array($v['target']??null)||($v['target']['kind']??'')!=='primitive')continue;$key=(string)($v['key']??'');$id=(string)($v['target']['id']??'');$field=(string)($v['target']['field']??'');if(!isset($byId[$id])||!array_key_exists($key,$normalized))continue;$i=$byId[$id];$value=$normalized[$key];if($field==='media'&&is_array($value)){$definition['elements'][$i]['path']=(string)($value['path']??'');$definition['elements'][$i]['alt']=(string)($value['alt']??'');}else $definition['elements'][$i][$field]=$value;}
    return ['definition'=>$definition,'values'=>$normalized];
}
function composerPrimitiveCmsId(string $instanceId,string $elementId,string $field): string {return substr('cmp-'.$instanceId.'-'.$elementId.'-'.$field,0,180);}
function composerPrimitiveAttr(string $v): string {return htmlspecialchars($v,ENT_QUOTES|ENT_HTML5,'UTF-8');}
function composerPrimitiveElementHtml(array $e,string $instanceId): string {
    $id=(string)$e['id'];$type=(string)$e['type'];$attr=' data-primitive-id="'.composerPrimitiveAttr($id).'"';$cid=fn(string $field)=>composerPrimitiveAttr(composerPrimitiveCmsId($instanceId,$id,$field));
    if($type==='eyebrow')return '<p class="cms-block-eyebrow"'.$attr.' data-cms-id="'.$cid('html').'">'.$e['html'].'</p>';
    if($type==='heading'){$tag=(string)$e['level'];return '<'.$tag.' class="cms-block-heading cms-scale-'.composerPrimitiveAttr((string)$e['scale']).'"'.$attr.' data-cms-id="'.$cid('html').'">'.$e['html'].'</'.$tag.'>';}
    if($type==='paragraph')return '<p class="cms-block-copy cms-copy-'.composerPrimitiveAttr((string)$e['style']).'"'.$attr.' data-cms-id="'.$cid('html').'">'.$e['html'].'</p>';
    if($type==='image'){if((string)$e['path']==='')return '<figure class="cms-block-image cms-block-image-empty"'.$attr.'></figure>';$cap=(string)$e['caption']!==''?'<figcaption data-cms-id="'.$cid('caption').'">'.$e['caption'].'</figcaption>':'';return '<figure class="cms-block-image"'.$attr.'><img src="'.composerPrimitiveAttr((string)$e['path']).'" alt="'.composerPrimitiveAttr((string)$e['alt']).'" loading="lazy">'.$cap.'</figure>';}
    if($type==='button')return '<p class="cms-block-action"'.$attr.'><a class="cms-block-button cms-button-'.composerPrimitiveAttr((string)$e['tone']).'" href="'.composerPrimitiveAttr((string)$e['href']).'">'.composerPrimitiveAttr((string)$e['label']).'</a></p>';
    if($type==='quote'){$cite=(string)$e['cite']!==''?'<footer data-cms-id="'.$cid('cite').'">'.$e['cite'].'</footer>':'';return '<blockquote class="cms-block-quote"'.$attr.'><p data-cms-id="'.$cid('html').'">'.$e['html'].'</p>'.$cite.'</blockquote>';}
    if($type==='metric')return '<div class="cms-block-metric"'.$attr.'><strong data-cms-id="'.$cid('value').'">'.$e['value'].'</strong><span data-cms-id="'.$cid('label').'">'.$e['label'].'</span></div>';
    return '<hr class="cms-block-divider"'.$attr.'>';
}
function composerPrimitiveRender(string $root,array $definition,string $instanceId): array {
    $definition=composerPrimitiveNormalize($root,$definition,$instanceId);$columns=composerPrimitiveColumns((string)$definition['layout']);$groups=array_fill(1,$columns,[]);foreach($definition['elements'] as $el)$groups[(int)$el['column']][]=$el;$cols=[];foreach($groups as $n=>$els)$cols[]='<div class="cms-block-column" data-column="'.$n.'">'.implode('',array_map(fn($e)=>composerPrimitiveElementHtml($e,$instanceId),$els)).'</div>';
    $classes=['cms-designed-block','cms-layout-'.$definition['layout'],'cms-surface-'.$definition['surface'],'cms-width-'.$definition['width'],'cms-spacing-'.$definition['spacing']];$html='<section class="'.implode(' ',$classes).'" data-composer-instance="'.composerPrimitiveAttr($instanceId).'"><div class="cms-designed-block-inner">'.implode('',$cols).'</div></section>';return ['definition'=>$definition,'html'=>$html];
}
