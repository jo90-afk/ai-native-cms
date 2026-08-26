<?php
declare(strict_types=1);
require_once __DIR__.'/content-core.php';

function presentationPublicHtmlFiles(string $root): array {
    $real=realpath($root);if($real===false)throw new RuntimeException('Public site root is unavailable.');$real=str_replace('\\','/',$real);$out=[];
    $skip=['cms','api','setup','database','tests','dist','.git','.github','.lattice'];$iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($real,FilesystemIterator::SKIP_DOTS));
    foreach($iterator as $file){if(!$file->isFile()||strtolower($file->getExtension())!=='html')continue;$full=str_replace('\\','/',$file->getPathname());$rel=ltrim(substr($full,strlen($real)),'/');$top=explode('/',$rel,2)[0]??'';if(in_array($top,$skip,true))continue;$out[$rel]=$full;}
    ksort($out,SORT_STRING);return $out;
}

function presentationRevision(string $key,string $kind,array $value): void {
    $json=json_encode($value,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRESERVE_ZERO_FRACTION);if(!is_string($json))throw new RuntimeException('Could not preserve site-wide revision.');$userId=(int)($_SESSION['cms_user_id']??0);
    $stmt=db()->prepare('INSERT INTO page_revisions (user_id,page_path,revision_kind,content_sha256,html_content,created_at) VALUES (NULLIF(?,0),?,?,?,?,UTC_TIMESTAMP())');$stmt->execute([$userId,$key,$kind,hash('sha256',$json),$json]);
}

function presentationHash(array $value): string {$json=json_encode($value,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRESERVE_ZERO_FRACTION);if(!is_string($json))throw new RuntimeException('Could not encode site-wide state.');return hash('sha256',$json);}
