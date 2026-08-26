<?php
declare(strict_types=1);
require_once __DIR__.'/composer.php';
require_once __DIR__.'/content-authority.php';

/** Canonical page compositions for already-configured pages. New-page hierarchy is a later capability. */

function compositionHashValue(?array $record): string {
    if($record===null)return 'none';
    $payload=['path'=>$record['path'],'label'=>$record['label'],'shellPath'=>$record['shellPath'],'blocks'=>$record['blocks']];
    $json=json_encode($payload,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRESERVE_ZERO_FRACTION);
    if(!is_string($json))throw new RuntimeException('Could not encode page composition.');
    return hash('sha256',$json);
}

function compositionRecord(string $path): ?array {
    $stmt=db()->prepare('SELECT page_path,label,title,shell_path,parent_path,blocks_json,updated_at FROM page_compositions WHERE page_path=?');$stmt->execute([$path]);$row=$stmt->fetch();if(!is_array($row))return null;
    $blocks=dbJsonDecode((string)$row['blocks_json']);
    $record=['path'=>(string)$row['page_path'],'label'=>(string)$row['label'],'title'=>(string)$row['title'],'shellPath'=>(string)$row['shell_path'],'parentPath'=>$row['parent_path']!==null?(string)$row['parent_path']:null,'blocks'=>$blocks,'updatedAt'=>dbIso((string)$row['updated_at'])];
    $record['hash']=compositionHashValue($record);return $record;
}
function compositionExists(string $path): bool {return compositionRecord($path)!==null;}
function compositionList(): array {
    $rows=db()->query('SELECT page_path FROM page_compositions ORDER BY page_path')->fetchAll();$out=[];
    foreach($rows as $row){$record=compositionRecord((string)$row['page_path']);if($record)$out[]=$record;}
    return $out;
}

function compositionRenderBlock(string $root,array $item): array {
    $key=trim((string)($item['templateKey']??''));$instance=trim((string)($item['instanceId']??''));
    if($key===''||!composerSafeInstanceId($instance))throw new RuntimeException('Invalid composition block identity.');
    $template=composerTemplateRecord($key);if(!$template)throw new RuntimeException('A referenced page template is unavailable: '.$key);
    $values=composerNormalizeValues($root,(array)$template['variables'],is_array($item['values']??null)?$item['values']:[]);
    $html=composerApplyValues($root,(string)$template['html'],(array)$template['variables'],$values);
    $html=composerRekeyEditableIds($html,$instance);$html=composerAnnotateBlock($html,$instance);
    return ['templateKey'=>$key,'instanceId'=>$instance,'values'=>$values,'html'=>$html];
}

function compositionShellHtml(string $root,string $path): string {
    $doc=contentAuthorityReadDocument(contentAuthorityPageSourceKey($path));if($doc)return (string)$doc['content'];
    $full=cmsSafePublicFile($root,$path);if($full===null)throw new RuntimeException('Page shell is unavailable.');return (string)file_get_contents($full);
}

function compositionStructuralHtml(string $root,string $path,array $blocks): string {
    $rendered=[];$seen=[];
    if(!$blocks||count($blocks)>80)throw new RuntimeException('A composition needs between 1 and 80 blocks.');
    foreach($blocks as $item){if(!is_array($item))throw new RuntimeException('Invalid composition block.');$row=compositionRenderBlock($root,$item);if(isset($seen[$row['instanceId']]))throw new RuntimeException('Duplicate composition block identity.');$seen[$row['instanceId']]=true;$rendered[]=$row['html'];}
    $html=composerReplaceMain(compositionShellHtml($root,$path),$rendered);$h1=preg_match_all('/<h1\b/i',$html,$m);if($h1!==1)throw new RuntimeException('A composed public page must contain exactly one H1 heading.');return $html;
}

function compositionSyncCanonicalBlocks(string $path,string $structural): int {
    $next=cmsExtractEditableBlocks($structural);$current=contentAuthorityPageBlocks($path);$pdo=db();$userId=(int)($_SESSION['cms_user_id']??0);
    $pdo->prepare('DELETE FROM page_blocks WHERE page_path=?')->execute([$path]);
    $insert=$pdo->prepare('INSERT INTO page_blocks (page_path,block_id,tag_name,html_content,content_sha256,source_sha256,source_ref,source_updated_at,updated_by,updated_at) VALUES (?,?,?,?,?,?,?,UTC_TIMESTAMP(),NULLIF(?,0),UTC_TIMESTAMP())');
    foreach($next as $block){$id=(string)$block['id'];$html=isset($current[$id])?(string)$current[$id]['html']:(string)$block['html'];$insert->execute([$path,$id,$block['tag'],$html,hash('sha256',$html),(string)$block['hash'],'composition',$userId]);}
    return count($next);
}

function compositionProjectPage(string $root,string $path): array {
    $record=compositionRecord($path);if(!$record)throw new RuntimeException('Page has no canonical composition.');
    $structural=compositionStructuralHtml($root,$path,(array)$record['blocks']);$leaves=compositionSyncCanonicalBlocks($path,$structural);$html=contentAuthorityOverlayBlocks($structural,$path);
    $target=cmsSafePublicFile($root,$path);if($target===null)throw new RuntimeException('Composed page target is unavailable.');cmsAtomicWrite($target,$html);
    return ['path'=>$path,'hash'=>hash('sha256',$html),'blocks'=>count((array)$record['blocks']),'editableBlocks'=>$leaves];
}
function compositionProjectAll(string $root): array {$items=[];foreach(compositionList() as $record)$items[]=compositionProjectPage($root,(string)$record['path']);return ['pages'=>count($items),'items'=>$items];}
function projectCmsPage(string $root,string $path): void {if(compositionExists($path))compositionProjectPage($root,$path);else contentAuthorityProjectPage($root,$path);}

function compositionSave(string $root,string $path,array $items,string $expectedHash): array {
    $pages=cmsManagedPages($root);if(!isset($pages[$path]))throw new RuntimeException('Only configured pages can be composed in this milestone.');
    $current=compositionRecord($path);$actual=compositionHashValue($current);if($expectedHash===''||!hash_equals($actual,$expectedHash))throw new RuntimeException('Page composition changed since it was opened. Refresh before saving.');
    $normalized=[];$seen=[];
    foreach($items as $item){if(!is_array($item))throw new RuntimeException('Invalid composition block.');$rendered=compositionRenderBlock($root,$item);if(isset($seen[$rendered['instanceId']]))throw new RuntimeException('Duplicate composition block identity.');$seen[$rendered['instanceId']]=true;$normalized[]=['templateKey'=>$rendered['templateKey'],'instanceId'=>$rendered['instanceId'],'values'=>$rendered['values']];}
    $structural=compositionStructuralHtml($root,$path,$normalized);$target=cmsSafePublicFile($root,$path);if($target===null)throw new RuntimeException('Configured page target is unavailable.');$before=(string)file_get_contents($target);$label=(string)$pages[$path];$userId=(int)($_SESSION['cms_user_id']??0);$pdo=db();$pdo->beginTransaction();$wrote=false;
    try{
        contentAuthorityBackupPage($path,$before,'composition');
        $stmt=$pdo->prepare('INSERT INTO page_compositions (page_path,label,title,shell_path,parent_path,blocks_json,updated_by,created_at,updated_at) VALUES (?,?,?,?,NULL,?,NULLIF(?,0),UTC_TIMESTAMP(),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE label=VALUES(label),shell_path=VALUES(shell_path),blocks_json=VALUES(blocks_json),updated_by=VALUES(updated_by),updated_at=UTC_TIMESTAMP()');
        $stmt->execute([$path,$label,'',$path,dbJsonEncode($normalized),$userId]);$leaves=compositionSyncCanonicalBlocks($path,$structural);$html=contentAuthorityOverlayBlocks($structural,$path);cmsAtomicWrite($target,$html);$wrote=true;$pdo->commit();
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();if($wrote)try{cmsAtomicWrite($target,$before);}catch(Throwable $ignored){}throw $e;}
    $saved=compositionRecord($path);if(!$saved)throw new RuntimeException('Saved composition could not be reloaded.');cmsAudit('composer','Saved typed page composition',['page'=>$path,'blocks'=>count($normalized),'editableBlocks'=>$leaves],$root);return $saved;
}

function compositionInitialItems(string $root,string $path): array {
    $record=compositionRecord($path);if($record)return (array)$record['blocks'];
    $items=[];foreach(composerTemplates() as $template)if((string)$template['sourcePage']===$path)$items[]=['templateKey'=>$template['key'],'instanceId'=>'blk-'.str_pad((string)((int)$template['sourceOrdinal']+1),2,'0',STR_PAD_LEFT),'values'=>$template['defaults']];
    return $items;
}

function compositionPayload(string $root,string $path): array {
    if(!isset(cmsManagedPages($root)[$path]))throw new RuntimeException('Page is not configured.');$record=compositionRecord($path);$items=compositionInitialItems($root,$path);$blocks=[];
    foreach($items as $item){$template=composerTemplateRecord((string)$item['templateKey']);if(!$template)continue;$blocks[]=['templateKey'=>$item['templateKey'],'instanceId'=>$item['instanceId'],'values'=>$item['values'],'label'=>$template['label'],'category'=>$template['category'],'variables'=>$template['variables']];}
    return ['path'=>$path,'label'=>cmsManagedPages($root)[$path],'composed'=>$record!==null,'hash'=>$record['hash']??'none','blocks'=>$blocks];
}
