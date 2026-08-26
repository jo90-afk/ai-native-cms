<?php
declare(strict_types=1);
require_once __DIR__.'/presentation-core.php';

/** Bounded site identity and adopter-declared CSS custom-property tokens. */

function brandingDefinitions(): array {
    $configured=siteConfigValue('branding','tokens',[]);if(!is_array($configured))return [];$out=[];
    foreach($configured as $key=>$spec){if(!is_array($spec)||!preg_match('/^[A-Za-z0-9._-]{1,80}$/',(string)$key))continue;$css=trim((string)($spec['css']??''));$type=(string)($spec['type']??'color');if(!preg_match('/^--[A-Za-z0-9_-]{1,80}$/',$css)||!in_array($type,['color','number','length'],true))continue;
        $out[(string)$key]=['css'=>$css,'type'=>$type,'default'=>$spec['default']??($type==='color'?'#000000':0),'min'=>(float)($spec['min']??0),'max'=>(float)($spec['max']??100),'unit'=>preg_match('/^[A-Za-z%]{0,8}$/',(string)($spec['unit']??''))?(string)($spec['unit']??''):''];
    }
    ksort($out,SORT_STRING);return $out;
}
function brandingDefaultIdentity(): array {
    $name=trim((string)siteConfigValue('site','name','Site'))?:'Site';$mark=trim((string)siteConfigValue('branding','mark',''));
    if($mark===''){$parts=preg_split('/\s+/u',$name)?:[];$letters='';foreach(array_slice($parts,0,2) as $part)$letters.=function_exists('mb_substr')?mb_substr($part,0,1):substr($part,0,1);$mark=strtoupper($letters!==''?$letters:substr($name,0,2));}
    return ['mark'=>substr($mark,0,8),'name'=>substr($name,0,80)];
}
function brandingValidateToken(mixed $value,array $definition): string|float|int {
    $type=$definition['type'];if($type==='color'){$v=strtolower(trim((string)$value));if(!preg_match('/^#[0-9a-f]{6}$/',$v))throw new RuntimeException('Brand color values must use six-digit hex notation.');return $v;}
    if(!is_numeric($value))throw new RuntimeException('Brand numeric values must be numbers.');$v=(float)$value;$v=max((float)$definition['min'],min((float)$definition['max'],$v));return fmod($v,1.0)===0.0?(int)$v:$v;
}
function brandingValidate(array $input): array {
    $defaultIdentity=brandingDefaultIdentity();$identity=is_array($input['identity']??null)?$input['identity']:[];$mark=trim((string)($identity['mark']??$defaultIdentity['mark']));$name=trim((string)($identity['name']??$defaultIdentity['name']));if($mark===''||$name==='')throw new RuntimeException('Brand mark and name are required.');
    $settings=['identity'=>['mark'=>substr($mark,0,8),'name'=>substr($name,0,80)],'tokens'=>[]];$values=is_array($input['tokens']??null)?$input['tokens']:[];
    foreach(brandingDefinitions() as $key=>$definition)$settings['tokens'][$key]=brandingValidateToken(array_key_exists($key,$values)?$values[$key]:$definition['default'],$definition);return $settings;
}
function brandingDefaults(): array {$tokens=[];foreach(brandingDefinitions() as $key=>$definition)$tokens[$key]=brandingValidateToken($definition['default'],$definition);return ['identity'=>brandingDefaultIdentity(),'tokens'=>$tokens];}
function brandingState(): array {
    $stmt=db()->query('SELECT settings_json,updated_at FROM site_branding WHERE id=1');$row=$stmt->fetch();$configured=is_array($row);$settings=$configured?brandingValidate(dbJsonDecode((string)$row['settings_json'])):brandingDefaults();return ['settings'=>$settings,'definitions'=>brandingDefinitions(),'configured'=>$configured,'hash'=>presentationHash($settings),'updatedAt'=>$configured?dbIso((string)$row['updated_at']):null];
}
function brandingClass(string $key,string $fallback): string {$classes=siteConfigValue('branding','identity_classes',[]);$value=is_array($classes)?trim((string)($classes[$key]??$fallback)):$fallback;return preg_match('/^[A-Za-z_][A-Za-z0-9_-]{0,80}$/',$value)?$value:$fallback;}
function brandingApplyIdentity(string $html,array $settings): string {
    $mark=htmlspecialchars((string)$settings['identity']['mark'],ENT_QUOTES|ENT_HTML5,'UTF-8');$name=htmlspecialchars((string)$settings['identity']['name'],ENT_QUOTES|ENT_HTML5,'UTF-8');$markClass=preg_quote(brandingClass('mark','brand-mark'),'/');$nameClass=preg_quote(brandingClass('name','brand-name'),'/');
    $html=preg_replace_callback('/(<[^>]+class=["\'][^"\']*\b'.$markClass.'\b[^"\']*["\'][^>]*>).*?(<\/[^>]+>)/is',fn($m)=>$m[1].$mark.$m[2],$html)??$html;
    return preg_replace_callback('/(<[^>]+class=["\'][^"\']*\b'.$nameClass.'\b[^"\']*["\'][^>]*>).*?(<\/[^>]+>)/is',fn($m)=>$m[1].$name.$m[2],$html)??$html;
}
function brandingCss(array $settings): string {
    $definitions=brandingDefinitions();if(!$definitions)return '';$lines=['/* AINCMS BRAND OVERRIDES START */',':root{'];
    foreach($definitions as $key=>$definition){$value=$settings['tokens'][$key]??$definition['default'];$suffix=$definition['type']==='color'?'':(string)$definition['unit'];$lines[]='  '.$definition['css'].':'.$value.$suffix.';';}$lines[]='}';$lines[]='/* AINCMS BRAND OVERRIDES END */';return implode("\n",$lines)."\n";
}
function brandingProject(string $root): array {
    $state=brandingState();$changed=0;$pages=0;foreach(presentationPublicHtmlFiles($root) as $path=>$full){$html=(string)file_get_contents($full);$next=brandingApplyIdentity($html,$state['settings']);if($next!==$html){cmsAtomicWrite($full,$next);$changed++;}$pages++;}
    $cssChanged=false;$cssHash='';$compiled=brandingCss($state['settings']);$stylesheet=trim((string)siteConfigValue('branding','stylesheet',''));
    if($compiled!==''&&$stylesheet!==''){$full=cmsSafePublicFile($root,$stylesheet);if($full===null)throw new RuntimeException('Configured branding stylesheet is unavailable.');$css=(string)file_get_contents($full);$pattern='/\/\* AINCMS BRAND OVERRIDES START \*\/.*?\/\* AINCMS BRAND OVERRIDES END \*\/\s*/s';$next=preg_match($pattern,$css)?(preg_replace($pattern,$compiled,$css,1)??$css):rtrim($css)."\n\n".$compiled;if($next!==$css){cmsAtomicWrite($full,$next);$cssChanged=true;}$cssHash=hash('sha256',$compiled);}
    return ['pages'=>$pages,'changed'=>$changed,'cssChanged'=>$cssChanged,'cssSha256'=>$cssHash,'hash'=>$state['hash']];
}
function brandingSave(string $root,array $input,string $expectedHash): array {
    $current=brandingState();if($expectedHash===''||!hash_equals($expectedHash,$current['hash']))throw new RuntimeException('Branding changed since it was opened. Refresh before saving.');$settings=brandingValidate($input);if($current['configured'])presentationRevision('@branding','branding',$current['settings']);$userId=(int)($_SESSION['cms_user_id']??0);$css=brandingCss($settings);
    $stmt=db()->prepare('INSERT INTO site_branding (id,settings_json,css_sha256,updated_by,updated_at) VALUES (1,?,?,NULLIF(?,0),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE settings_json=VALUES(settings_json),css_sha256=VALUES(css_sha256),updated_by=VALUES(updated_by),updated_at=UTC_TIMESTAMP()');$stmt->execute([dbJsonEncode($settings),hash('sha256',$css),$userId]);$projected=brandingProject($root);cmsAudit('branding','Updated site branding',['changedPages'=>$projected['changed'],'cssChanged'=>$projected['cssChanged']],$root);return brandingState()+['projected'=>$projected];
}
