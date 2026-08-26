<?php
declare(strict_types=1);
require_once __DIR__.'/content-authority.php';

/** Three-way repository reconciliation plus immutable compare-and-swap update sets. */

function contentSyncUpdateSets(string $root): array {
    $dir=$root.'/database/content-updates';if(!is_dir($dir))return [];$sets=[];$files=glob($dir.'/*.php')?:[];sort($files,SORT_STRING);
    foreach($files as $file){$set=require $file;if(!is_array($set))throw new RuntimeException('Content update file must return an array: '.$file);$sets[]=$set;}return $sets;
}
function contentSyncStandingSets(string $root): array {return array_values(array_filter(contentSyncUpdateSets($root),fn($set)=>is_array($set)&&($set['standing']??false)===true));}
function contentSyncTransformBlockCandidate(array $sets,string $page,string $block,string $html): string {
    foreach($sets as $set)foreach(($set['changes']??[]) as $change){if(!is_array($change)||($change['kind']??'')!=='block'||($change['page']??'')!==$page||($change['block']??'')!==$block)continue;$old=(string)($change['old']??'');$new=(string)($change['new']??'');if($old!==''&&$html===$old)$html=$new;}
    return $html;
}
function contentSyncTransformDocumentCandidate(array $sets,string $key,string $content): string {
    foreach($sets as $set)foreach(($set['changes']??[]) as $change){if(!is_array($change)||($change['kind']??'')!=='document-replace'||($change['key']??'')!==$key)continue;$old=(string)($change['old']??'');$new=(string)($change['new']??'');if($old===''||$old===$new)continue;$count=substr_count($content,$old);if($count===1)$content=str_replace($old,$new,$content);elseif($count>1)throw new RuntimeException('Standing document replacement became ambiguous for '.$key);}
    return $content;
}

function contentSyncRepository(string $root,string $sourceRef='repository'): array {
    dbRequireSchemaVersion(7);$pdo=db();$applied=0;$preserved=0;$baselined=0;$inserted=0;$standing=contentSyncStandingSets($root);$ownTx=!$pdo->inTransaction();if($ownTx)$pdo->beginTransaction();
    try{
        $select=$pdo->prepare('SELECT tag_name,html_content,content_sha256,source_sha256 FROM page_blocks WHERE page_path=? AND block_id=? FOR UPDATE');
        $insert=$pdo->prepare('INSERT INTO page_blocks (page_path,block_id,tag_name,html_content,content_sha256,source_sha256,source_ref,source_updated_at,updated_by,updated_at) VALUES (?,?,?,?,?,?,?,UTC_TIMESTAMP(),NULL,UTC_TIMESTAMP())');
        $accept=$pdo->prepare('UPDATE page_blocks SET tag_name=?,html_content=?,content_sha256=?,source_sha256=?,source_ref=?,source_updated_at=UTC_TIMESTAMP(),updated_by=NULL,updated_at=UTC_TIMESTAMP() WHERE page_path=? AND block_id=?');
        $track=$pdo->prepare('UPDATE page_blocks SET tag_name=?,source_sha256=?,source_ref=?,source_updated_at=UTC_TIMESTAMP() WHERE page_path=? AND block_id=?');
        foreach(contentAuthorityManagedPages($root) as $path=>$label){
            $file=cmsSafePublicFile($root,$path);if($file===null)continue;
            foreach(cmsExtractEditableBlocks((string)file_get_contents($file)) as $block){
                $block['html']=contentSyncTransformBlockCandidate($standing,$path,(string)$block['id'],(string)$block['html']);$block['hash']=hash('sha256',(string)$block['html']);$select->execute([$path,$block['id']]);$row=$select->fetch();$target=$path.'#'.$block['id'];
                if(!$row){$insert->execute([$path,$block['id'],$block['tag'],$block['html'],$block['hash'],$block['hash'],$sourceRef]);contentAuthorityLogChange($pdo,'page_block',$target,'repository',$sourceRef,'applied','',$block['hash'],$block['hash'],'New effective repository block entered canonical SQL.');$inserted++;continue;}
                $canonical=(string)$row['content_sha256'];$previousSource=(string)($row['source_sha256']??'');
                if($previousSource===''){$track->execute([$block['tag'],$block['hash'],$sourceRef,$path,$block['id']]);contentAuthorityLogChange($pdo,'page_block',$target,'repository',$sourceRef,'already_current',$canonical,$canonical,$block['hash'],'Effective repository source baseline recorded without changing canonical SQL.');$baselined++;continue;}
                if(hash_equals($previousSource,$block['hash']))continue;
                if(hash_equals($canonical,$previousSource)){$accept->execute([$block['tag'],$block['html'],$block['hash'],$block['hash'],$sourceRef,$path,$block['id']]);contentAuthorityLogChange($pdo,'page_block',$target,'repository',$sourceRef,'applied',$canonical,$block['hash'],$block['hash'],'Effective repository source advanced and canonical SQL still matched the previous source.');$applied++;}
                else{$track->execute([$block['tag'],$block['hash'],$sourceRef,$path,$block['id']]);contentAuthorityLogChange($pdo,'page_block',$target,'repository',$sourceRef,'preserved_newer',$canonical,$canonical,$block['hash'],'Effective repository source advanced but canonical SQL had diverged; SQL was preserved.');$preserved++;}
            }
        }
        $selectDoc=$pdo->prepare('SELECT content_sha256,source_sha256 FROM content_documents WHERE document_key=? FOR UPDATE');
        $insertDoc=$pdo->prepare('INSERT INTO content_documents (document_key,document_type,label,source_format,content,content_sha256,source_sha256,source_ref,source_updated_at,updated_by,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,UTC_TIMESTAMP(),NULL,UTC_TIMESTAMP(),UTC_TIMESTAMP())');
        $acceptDoc=$pdo->prepare('UPDATE content_documents SET document_type=?,label=?,source_format=?,content=?,content_sha256=?,source_sha256=?,source_ref=?,source_updated_at=UTC_TIMESTAMP(),updated_by=NULL,updated_at=UTC_TIMESTAMP() WHERE document_key=?');
        $trackDoc=$pdo->prepare('UPDATE content_documents SET document_type=?,label=?,source_format=?,source_sha256=?,source_ref=?,source_updated_at=UTC_TIMESTAMP() WHERE document_key=?');
        foreach(contentAuthorityDocumentSpecs($root) as $key=>$spec){
            $sourcePath=cmsSafePublicFile($root,$spec['sourcePath']);if($sourcePath===null)continue;$content=contentSyncTransformDocumentCandidate($standing,(string)$key,(string)file_get_contents($sourcePath));$hash=hash('sha256',$content);$selectDoc->execute([$key]);$row=$selectDoc->fetch();
            if(!$row){$insertDoc->execute([$key,$spec['type'],$spec['label'],$spec['format'],$content,$hash,$hash,$sourceRef]);contentAuthorityLogChange($pdo,'document',(string)$key,'repository',$sourceRef,'applied','',$hash,$hash,'New effective repository document entered canonical SQL.');$inserted++;continue;}
            $canonical=(string)$row['content_sha256'];$previousSource=(string)($row['source_sha256']??'');
            if($previousSource===''){$trackDoc->execute([$spec['type'],$spec['label'],$spec['format'],$hash,$sourceRef,$key]);contentAuthorityLogChange($pdo,'document',(string)$key,'repository',$sourceRef,'already_current',$canonical,$canonical,$hash,'Effective repository source baseline recorded without changing canonical SQL.');$baselined++;continue;}
            if(hash_equals($previousSource,$hash))continue;
            if(hash_equals($canonical,$previousSource)){$acceptDoc->execute([$spec['type'],$spec['label'],$spec['format'],$content,$hash,$hash,$sourceRef,$key]);contentAuthorityLogChange($pdo,'document',(string)$key,'repository',$sourceRef,'applied',$canonical,$hash,$hash,'Effective repository source advanced and canonical SQL still matched the previous source.');$applied++;}
            else{$trackDoc->execute([$spec['type'],$spec['label'],$spec['format'],$hash,$sourceRef,$key]);contentAuthorityLogChange($pdo,'document',(string)$key,'repository',$sourceRef,'preserved_newer',$canonical,$canonical,$hash,'Effective repository source advanced but canonical SQL had diverged; SQL was preserved.');$preserved++;}
        }
        if($ownTx)$pdo->commit();return ['applied'=>$applied,'preserved'=>$preserved,'baselined'=>$baselined,'inserted'=>$inserted,'standingSets'=>count($standing),'sourceRef'=>$sourceRef];
    }catch(Throwable $e){if($ownTx&&$pdo->inTransaction())$pdo->rollBack();throw $e;}
}

function contentSyncUpdateSetHash(array $set): string {$json=json_encode($set,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRESERVE_ZERO_FRACTION);if(!is_string($json))throw new RuntimeException('Could not encode content update set.');return hash('sha256',$json);}

function contentSyncApplyDocumentReplace(PDO $pdo,array $change,string $origin,string $originRef): string {
    $key=(string)($change['key']??'');$old=(string)($change['old']??'');$new=(string)($change['new']??'');if($key===''||$old===''||$old===$new)throw new RuntimeException('Invalid document replacement update.');$doc=contentAuthorityReadDocument($key);
    if(!$doc){contentAuthorityLogChange($pdo,'document',$key,$origin,$originRef,'conflict','','','','Update target document is missing.');return 'conflict';}
    $before=(string)$doc['hash'];$content=(string)$doc['content'];$oldCount=substr_count($content,$old);$newCount=substr_count($content,$new);
    if($oldCount===0&&$newCount>0){contentAuthorityLogChange($pdo,'document',$key,$origin,$originRef,'already_current',$before,$before,'','Replacement is already present.');return 'already_current';}
    if($oldCount!==1){contentAuthorityLogChange($pdo,'document',$key,$origin,$originRef,'preserved_newer',$before,$before,'','Expected superseded text was not uniquely present; canonical SQL was preserved.');return 'preserved_newer';}
    $next=str_replace($old,$new,$content,$count);if($count!==1)throw new RuntimeException('Could not apply document replacement.');return contentAuthorityCommitDocument($key,$next,$origin,$originRef,$before);
}

function contentSyncApplyUpdateSet(string $root,array $set,string $defaultRef=''): array {
    $id=trim((string)($set['id']??''));$origin=trim((string)($set['origin']??'release'));$originRef=trim((string)($set['originRef']??$defaultRef));$changes=$set['changes']??null;
    if($id===''||!preg_match('/^[A-Za-z0-9._:-]{3,191}$/',$id)||!is_array($changes))throw new RuntimeException('Invalid content update set.');$pdo=db();$hash=contentSyncUpdateSetHash($set);$seen=$pdo->prepare('SELECT update_sha256,applied_count,preserved_count FROM content_update_sets WHERE update_id=?');$seen->execute([$id]);$row=$seen->fetch();
    if($row){if(!hash_equals((string)$row['update_sha256'],$hash))throw new RuntimeException('Content update ID was reused with different contents: '.$id);return ['id'=>$id,'alreadyApplied'=>true,'applied'=>(int)$row['applied_count'],'preserved'=>(int)$row['preserved_count']];}
    $applied=0;$already=0;$preserved=0;$pdo->beginTransaction();
    try{
        foreach($changes as $change){if(!is_array($change))throw new RuntimeException('Invalid content update change.');$kind=(string)($change['kind']??'');
            if($kind==='block'){$page=(string)($change['page']??'');$block=(string)($change['block']??'');$new=(string)($change['new']??'');$expected=array_key_exists('old',$change)?hash('sha256',(string)$change['old']):(isset($change['expectedHash'])?(string)$change['expectedHash']:null);$outcome=contentAuthorityCommitBlock($page,$block,$new,$origin,$originRef,$expected);}
            elseif($kind==='document'){$key=(string)($change['key']??'');$new=(string)($change['new']??'');$expected=array_key_exists('old',$change)?hash('sha256',(string)$change['old']):(isset($change['expectedHash'])?(string)$change['expectedHash']:null);$outcome=contentAuthorityCommitDocument($key,$new,$origin,$originRef,$expected);}
            elseif($kind==='document-replace')$outcome=contentSyncApplyDocumentReplace($pdo,$change,$origin,$originRef);else throw new RuntimeException('Unsupported content update kind: '.$kind);
            if($outcome==='applied')$applied++;elseif($outcome==='already_current')$already++;elseif($outcome==='preserved_newer')$preserved++;else throw new RuntimeException('Content update target could not be resolved for '.$id.'.');
        }
        $insert=$pdo->prepare('INSERT INTO content_update_sets (update_id,origin,origin_ref,update_sha256,applied_count,preserved_count,applied_at) VALUES (?,?,?,?,?,?,UTC_TIMESTAMP())');$insert->execute([$id,$origin,$originRef,$hash,$applied,$preserved]);$pdo->commit();return ['id'=>$id,'alreadyApplied'=>false,'applied'=>$applied,'alreadyCurrent'=>$already,'preserved'=>$preserved];
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
}
function contentSyncApplyUpdateDirectory(string $root,string $defaultRef=''): array {$results=[];foreach(contentSyncUpdateSets($root) as $set)$results[]=contentSyncApplyUpdateSet($root,$set,$defaultRef);return ['sets'=>count($results),'results'=>$results];}
