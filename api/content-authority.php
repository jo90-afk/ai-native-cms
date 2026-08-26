<?php
declare(strict_types=1);
require_once __DIR__.'/content-core.php';

/** Canonical SQL content authority and deterministic static projection. */

function contentAuthorityManagedPages(string $root): array { return cmsManagedPages($root); }
function contentAuthorityPageSourceKey(string $path): string { return '@page/'.$path; }

function contentAuthorityDocumentSpecs(string $root): array {
    $specs=cmsConfiguredDocuments($root);
    foreach(contentAuthorityManagedPages($root) as $path=>$label){
        $specs[contentAuthorityPageSourceKey($path)]=[
            'sourcePath'=>$path,'targetPath'=>$path,'type'=>'page-source','label'=>$label.' source','format'=>'html'
        ];
    }
    ksort($specs,SORT_STRING);return $specs;
}

function contentAuthorityLogChange(PDO $pdo,string $targetType,string $targetKey,string $origin,string $originRef,string $outcome,string $before,string $after,string $source='',string $message=''): void {
    $stmt=$pdo->prepare('INSERT INTO content_change_log (target_type,target_key,origin,origin_ref,outcome,before_sha256,after_sha256,source_sha256,message,created_at) VALUES (?,?,?,?,?,?,?,?,?,UTC_TIMESTAMP())');
    $stmt->execute([$targetType,$targetKey,$origin,$originRef,$outcome,$before,$after,$source,$message]);
}

function contentAuthorityImportPageBlocks(string $root,bool $overwrite=false,string $sourceRef='repository'): array {
    $pdo=db();dbRequireSchemaVersion(7);$userId=(int)($_SESSION['cms_user_id']??0);$pages=0;$blocks=0;
    $sql=$overwrite
      ? 'INSERT INTO page_blocks (page_path,block_id,tag_name,html_content,content_sha256,source_sha256,source_ref,source_updated_at,updated_by,updated_at) VALUES (?,?,?,?,?,?,?,UTC_TIMESTAMP(),NULLIF(?,0),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE tag_name=VALUES(tag_name),html_content=VALUES(html_content),content_sha256=VALUES(content_sha256),source_sha256=VALUES(source_sha256),source_ref=VALUES(source_ref),source_updated_at=UTC_TIMESTAMP(),updated_by=VALUES(updated_by),updated_at=UTC_TIMESTAMP()'
      : 'INSERT IGNORE INTO page_blocks (page_path,block_id,tag_name,html_content,content_sha256,source_sha256,source_ref,source_updated_at,updated_by,updated_at) VALUES (?,?,?,?,?,?,?,UTC_TIMESTAMP(),NULLIF(?,0),UTC_TIMESTAMP())';
    $stmt=$pdo->prepare($sql);$baseline=$pdo->prepare("UPDATE page_blocks SET source_sha256=?,source_ref=?,source_updated_at=UTC_TIMESTAMP() WHERE page_path=? AND block_id=? AND source_sha256=''");
    foreach(contentAuthorityManagedPages($root) as $path=>$label){
        $file=cmsSafePublicFile($root,$path);if($file===null)continue;
        foreach(cmsExtractEditableBlocks((string)file_get_contents($file)) as $block){
            $stmt->execute([$path,$block['id'],$block['tag'],$block['html'],$block['hash'],$block['hash'],$sourceRef,$userId]);
            if(!$overwrite)$baseline->execute([$block['hash'],$sourceRef,$path,$block['id']]);$blocks++;
        }
        $pages++;
    }
    return ['pages'=>$pages,'blocks'=>$blocks];
}

function contentAuthorityUpsertDocument(string $key,string $type,string $label,string $format,string $content,bool $overwrite=false,string $sourceRef='repository'): void {
    $userId=(int)($_SESSION['cms_user_id']??0);$hash=hash('sha256',$content);
    $sql=$overwrite
      ? 'INSERT INTO content_documents (document_key,document_type,label,source_format,content,content_sha256,source_sha256,source_ref,source_updated_at,updated_by,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,UTC_TIMESTAMP(),NULLIF(?,0),UTC_TIMESTAMP(),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE document_type=VALUES(document_type),label=VALUES(label),source_format=VALUES(source_format),content=VALUES(content),content_sha256=VALUES(content_sha256),source_sha256=VALUES(source_sha256),source_ref=VALUES(source_ref),source_updated_at=UTC_TIMESTAMP(),updated_by=VALUES(updated_by),updated_at=UTC_TIMESTAMP()'
      : 'INSERT IGNORE INTO content_documents (document_key,document_type,label,source_format,content,content_sha256,source_sha256,source_ref,source_updated_at,updated_by,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,UTC_TIMESTAMP(),NULLIF(?,0),UTC_TIMESTAMP(),UTC_TIMESTAMP())';
    $stmt=db()->prepare($sql);$stmt->execute([$key,$type,$label,$format,$content,$hash,$hash,$sourceRef,$userId]);
    if(!$overwrite){$baseline=db()->prepare("UPDATE content_documents SET source_sha256=?,source_ref=?,source_updated_at=UTC_TIMESTAMP() WHERE document_key=? AND source_sha256=''");$baseline->execute([$hash,$sourceRef,$key]);}
}

function contentAuthorityImportDocuments(string $root,bool $overwrite=false,string $sourceRef='repository'): int {
    $count=0;
    foreach(contentAuthorityDocumentSpecs($root) as $key=>$spec){
        $path=cmsSafePublicFile($root,$spec['sourcePath']);if($path===null)continue;
        contentAuthorityUpsertDocument((string)$key,$spec['type'],$spec['label'],$spec['format'],(string)file_get_contents($path),$overwrite,$sourceRef);$count++;
    }
    return $count;
}

function contentAuthorityImport(string $root,bool $overwrite=false,string $sourceRef='repository'): array {
    dbRequireSchemaVersion(7);$documents=contentAuthorityImportDocuments($root,$overwrite,$sourceRef);$pages=contentAuthorityImportPageBlocks($root,$overwrite,$sourceRef);
    return ['pages'=>$pages['pages'],'blocks'=>$pages['blocks'],'documents'=>$documents,'schemaVersion'=>7];
}

function contentAuthorityPageBlocks(string $path): array {
    $stmt=db()->prepare('SELECT block_id,tag_name,html_content,content_sha256,source_sha256,source_ref,updated_at FROM page_blocks WHERE page_path=? ORDER BY block_id');$stmt->execute([$path]);$out=[];
    foreach($stmt->fetchAll() as $row)$out[(string)$row['block_id']]=['id'=>(string)$row['block_id'],'tag'=>(string)$row['tag_name'],'html'=>(string)$row['html_content'],'hash'=>(string)$row['content_sha256'],'sourceHash'=>(string)($row['source_sha256']??''),'sourceRef'=>(string)($row['source_ref']??''),'updatedAt'=>dbIso((string)$row['updated_at'])];
    return $out;
}

function contentAuthorityOverlayBlocks(string $html,string $path): string {
    foreach(contentAuthorityPageBlocks($path) as $id=>$block){
        $qid=preg_quote($id,'/');$pattern='/<(?P<tag>h[1-4]|p|figcaption|td|th|dd)\b(?P<attrs>[^>]*\bdata-cms-id=["\']'.$qid.'["\'][^>]*)>(?P<inner>.*?)<\/\1>/is';
        $html=preg_replace_callback($pattern,fn($m)=>'<'.$m['tag'].$m['attrs'].'>'.$block['html'].'</'.$m['tag'].'>',$html,1)??$html;
    }
    return $html;
}

function contentAuthorityCommitBlock(string $path,string $id,string $html,string $origin,string $originRef='',?string $expectedHash=null): string {
    $pdo=db();$stmt=$pdo->prepare('SELECT html_content,content_sha256 FROM page_blocks WHERE page_path=? AND block_id=? FOR UPDATE');$stmt->execute([$path,$id]);$row=$stmt->fetch();$target=$path.'#'.$id;$newHash=hash('sha256',$html);
    if(!$row){contentAuthorityLogChange($pdo,'page_block',$target,$origin,$originRef,'conflict','',$newHash,'','Canonical block is missing.');return 'conflict';}
    $before=(string)$row['content_sha256'];
    if($before===$newHash){contentAuthorityLogChange($pdo,'page_block',$target,$origin,$originRef,'already_current',$before,$before,'','Canonical block already matches the proposed value.');return 'already_current';}
    if($expectedHash!==null&&!hash_equals($expectedHash,$before)){contentAuthorityLogChange($pdo,'page_block',$target,$origin,$originRef,'preserved_newer',$before,$before,'','Expected predecessor no longer matches; canonical SQL value was preserved.');return 'preserved_newer';}
    $userId=$origin==='cms'?(int)($_SESSION['cms_user_id']??0):0;$update=$pdo->prepare('UPDATE page_blocks SET html_content=?,content_sha256=?,updated_by=NULLIF(?,0),updated_at=UTC_TIMESTAMP() WHERE page_path=? AND block_id=?');$update->execute([$html,$newHash,$userId,$path,$id]);
    contentAuthorityLogChange($pdo,'page_block',$target,$origin,$originRef,'applied',$before,$newHash,'','Canonical SQL block updated.');return 'applied';
}

function contentAuthorityStoreBlockChanges(string $path,array $changes): int {
    $existing=contentAuthorityPageBlocks($path);$changed=0;$pdo=db();$ownTx=!$pdo->inTransaction();if($ownTx)$pdo->beginTransaction();
    try{
        foreach($changes as $change){
            $id=(string)$change['id'];if(!isset($existing[$id]))throw new RuntimeException('Editable block is missing from canonical database content: '.$id);
            $expected=(string)($change['hash']??'');if($expected!==''&&!hash_equals($expected,$existing[$id]['hash']))throw new RuntimeException('Page changed since it was opened. Refresh before saving.');
            $clean=cmsSanitizeRichHtml((string)$change['html']);$outcome=contentAuthorityCommitBlock($path,$id,$clean,'cms','browser',$expected!==''?$expected:null);
            if($outcome==='preserved_newer'||$outcome==='conflict')throw new RuntimeException('Page changed since it was opened. Refresh before saving.');if($outcome==='applied')$changed++;
        }
        if($ownTx)$pdo->commit();return $changed;
    }catch(Throwable $e){if($ownTx&&$pdo->inTransaction())$pdo->rollBack();throw $e;}
}

function contentAuthorityReadDocument(string $key): ?array {
    $stmt=db()->prepare('SELECT document_key,document_type,label,source_format,content,content_sha256,source_sha256,source_ref,updated_at FROM content_documents WHERE document_key=?');$stmt->execute([$key]);$row=$stmt->fetch();if(!$row)return null;
    return ['key'=>(string)$row['document_key'],'type'=>(string)$row['document_type'],'label'=>(string)$row['label'],'format'=>(string)$row['source_format'],'content'=>(string)$row['content'],'hash'=>(string)$row['content_sha256'],'sourceHash'=>(string)($row['source_sha256']??''),'sourceRef'=>(string)($row['source_ref']??''),'updatedAt'=>dbIso((string)$row['updated_at'])];
}

function contentAuthorityCommitDocument(string $key,string $content,string $origin,string $originRef='',?string $expectedHash=null): string {
    $pdo=db();$stmt=$pdo->prepare('SELECT content_sha256 FROM content_documents WHERE document_key=? FOR UPDATE');$stmt->execute([$key]);$row=$stmt->fetch();$newHash=hash('sha256',$content);
    if(!$row){contentAuthorityLogChange($pdo,'document',$key,$origin,$originRef,'conflict','',$newHash,'','Canonical document is missing.');return 'conflict';}
    $before=(string)$row['content_sha256'];if($before===$newHash){contentAuthorityLogChange($pdo,'document',$key,$origin,$originRef,'already_current',$before,$before,'','Canonical document already matches the proposed value.');return 'already_current';}
    if($expectedHash!==null&&!hash_equals($expectedHash,$before)){contentAuthorityLogChange($pdo,'document',$key,$origin,$originRef,'preserved_newer',$before,$before,'','Expected predecessor no longer matches; canonical SQL value was preserved.');return 'preserved_newer';}
    $userId=$origin==='cms'?(int)($_SESSION['cms_user_id']??0):0;$update=$pdo->prepare('UPDATE content_documents SET content=?,content_sha256=?,updated_by=NULLIF(?,0),updated_at=UTC_TIMESTAMP() WHERE document_key=?');$update->execute([$content,$newHash,$userId,$key]);
    contentAuthorityLogChange($pdo,'document',$key,$origin,$originRef,'applied',$before,$newHash,'','Canonical SQL document updated.');return 'applied';
}

function contentAuthorityBackupPage(string $path,string $html,string $kind='content'): string {
    $userId=(int)($_SESSION['cms_user_id']??0);$stmt=db()->prepare('INSERT INTO page_revisions (user_id,page_path,revision_kind,content_sha256,html_content,created_at) VALUES (NULLIF(?,0),?,?,?,?,UTC_TIMESTAMP())');
    $stmt->execute([$userId,$path,$kind,hash('sha256',$html),$html]);return 'mysql:page_revisions/'.(string)db()->lastInsertId();
}

function contentAuthorityDocumentTarget(string $key): string { return str_starts_with($key,'@page/')?substr($key,6):$key; }

function contentAuthorityProjectConfiguredDocuments(string $root): int {
    $count=0;
    foreach(contentAuthorityDocumentSpecs($root) as $key=>$spec){
        if(str_starts_with((string)$key,'@page/')) continue;$doc=contentAuthorityReadDocument((string)$key);if(!$doc)continue;
        $target=cmsSafePublicFile($root,$spec['targetPath']);if($target===null)continue;cmsAtomicWrite($target,(string)$doc['content']);$count++;
    }
    return $count;
}

function contentAuthorityProjectPage(string $root,string $path): void {
    if(!isset(contentAuthorityManagedPages($root)[$path]))throw new RuntimeException('Page is not configured as CMS-managed.');
    $target=cmsSafePublicFile($root,$path);if($target===null)throw new RuntimeException('Public page template is missing.');
    $source=contentAuthorityReadDocument(contentAuthorityPageSourceKey($path));$html=$source?(string)$source['content']:(string)file_get_contents($target);
    $html=contentAuthorityOverlayBlocks($html,$path);cmsAtomicWrite($target,$html);
}

function contentAuthorityProjectPages(string $root): int { $count=0;foreach(contentAuthorityManagedPages($root) as $path=>$label){contentAuthorityProjectPage($root,$path);$count++;}return $count; }

function contentAuthorityStatus(string $root): array {
    $version=(int)db()->query('SELECT schema_version FROM app_meta WHERE id=1')->fetchColumn();$expectedPages=count(contentAuthorityManagedPages($root));$storedPages=0;$blocks=0;$documents=0;$pageSources=0;
    if($version>=7){
        $storedPages=(int)db()->query('SELECT COUNT(DISTINCT page_path) FROM page_blocks')->fetchColumn();$blocks=(int)db()->query('SELECT COUNT(*) FROM page_blocks')->fetchColumn();$documents=(int)db()->query('SELECT COUNT(*) FROM content_documents')->fetchColumn();
        foreach(contentAuthorityManagedPages($root) as $path=>$label){$stmt=db()->prepare('SELECT 1 FROM content_documents WHERE document_key=?');$stmt->execute([contentAuthorityPageSourceKey($path)]);if($stmt->fetchColumn())$pageSources++;}
    }
    return ['schemaVersion'=>$version,'pages'=>$storedPages,'expectedPages'=>$expectedPages,'blocks'=>$blocks,'documents'=>$documents,'pageSources'=>$pageSources,'ready'=>$version>=7&&$storedPages===$expectedPages&&$pageSources===$expectedPages];
}
