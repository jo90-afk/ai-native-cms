<?php
declare(strict_types=1);
require_once __DIR__.'/runtime.php';

/** First-party media catalog. File bytes remain adopter-owned; metadata is canonical SQL. */

function mediaConfig(string $key,mixed $default=null): mixed {return siteConfigValue('media',$key,$default);}
function mediaRoots(): array {
    $roots=mediaConfig('public_roots',['assets']);if(!is_array($roots))$roots=['assets'];$out=[];
    foreach($roots as $root){$root=trim(str_replace('\\','/',(string)$root),'/');if($root!==''&&preg_match('#^[A-Za-z0-9._/-]+$#',$root)&&!str_contains($root,'..'))$out[]=$root;}
    return array_values(array_unique($out?:['assets']));
}
function mediaAllowedPath(string $path): bool {
    $path=trim(str_replace('\\','/',$path),'/');if($path===''||str_contains($path,'..')||!preg_match('/\.(?:jpe?g|png|webp|gif|svg)$/i',$path))return false;
    foreach(mediaRoots() as $root)if($path===$root||str_starts_with($path,$root.'/'))return true;return false;
}
function mediaKey(string $path): string {return 'asset-'.substr(hash('sha256',$path),0,32);}
function mediaSubstr(string $value,int $length): string {return function_exists('mb_substr')?mb_substr($value,0,$length):substr($value,0,$length);}

function mediaInfo(string $root,string $path): array {
    if(!mediaAllowedPath($path))throw new RuntimeException('Invalid public media path.');$full=realpath(rtrim($root,'/\\').'/'.$path);
    if($full===false||!is_file($full)||!pathInside($full,$root))throw new RuntimeException('Media file is missing or outside the public root.');
    $ext=strtolower(pathinfo($full,PATHINFO_EXTENSION));$mime=match($ext){'jpg','jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp','gif'=>'image/gif','svg'=>'image/svg+xml',default=>'application/octet-stream'};$width=0;$height=0;
    if($ext!=='svg'){$info=@getimagesize($full);if(is_array($info)){$width=(int)($info[0]??0);$height=(int)($info[1]??0);if(!empty($info['mime']))$mime=(string)$info['mime'];}}
    $title=trim(preg_replace('/[-_]+/',' ',pathinfo($path,PATHINFO_FILENAME))??'');
    return ['key'=>mediaKey($path),'path'=>$path,'sha256'=>hash_file('sha256',$full)?:'','mime'=>$mime,'width'=>$width,'height'=>$height,'title'=>ucwords($title)];
}

function mediaUpsertRecord(string $root,string $path,string $title='',string $alt='',string $caption='',string $source='site'): array {
    $info=mediaInfo($root,$path);$userId=(int)($_SESSION['cms_user_id']??0);$stmt=db()->prepare('INSERT INTO media_library (asset_key,public_path,file_sha256,mime_type,width,height,title,alt_text,caption,source_kind,updated_by,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,NULLIF(?,0),UTC_TIMESTAMP(),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE file_sha256=VALUES(file_sha256),mime_type=VALUES(mime_type),width=VALUES(width),height=VALUES(height),title=IF(VALUES(title)="",title,VALUES(title)),alt_text=IF(VALUES(alt_text)="",alt_text,VALUES(alt_text)),caption=IF(VALUES(caption)="",caption,VALUES(caption)),source_kind=VALUES(source_kind),updated_by=VALUES(updated_by),updated_at=UTC_TIMESTAMP()');
    $stmt->execute([$info['key'],$path,$info['sha256'],$info['mime'],$info['width'],$info['height'],mediaSubstr(trim($title)!==''?trim($title):$info['title'],191),mediaSubstr(trim($alt),1000),trim($caption),substr($source,0,32),$userId]);return mediaByKey($root,$info['key'])??$info;
}

function mediaRefreshLibrary(string $root): array {
    $count=0;
    foreach(mediaRoots() as $relativeRoot){$real=realpath(rtrim($root,'/\\').'/'.$relativeRoot);if($real===false||!is_dir($real)||!pathInside($real,$root))continue;$iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($real,FilesystemIterator::SKIP_DOTS));
        foreach($iterator as $file){if(!$file->isFile())continue;$rel=$relativeRoot.'/'.ltrim(str_replace('\\','/',substr($file->getPathname(),strlen($real))),'/');if(!mediaAllowedPath($rel))continue;mediaUpsertRecord($root,$rel,'','','','site');$count++;}}
    return ['assets'=>$count];
}

function mediaItems(string $root): array {
    $rows=db()->query('SELECT asset_key,public_path,mime_type,width,height,title,alt_text,caption,source_kind,updated_at FROM media_library ORDER BY updated_at DESC,title,public_path')->fetchAll();$out=[];
    foreach($rows as $row){$path=(string)$row['public_path'];if(!mediaAllowedPath($path)||cmsSafePublicFile($root,$path)===null)continue;$out[]=['key'=>(string)$row['asset_key'],'path'=>$path,'mime'=>(string)$row['mime_type'],'width'=>(int)$row['width'],'height'=>(int)$row['height'],'title'=>(string)$row['title'],'alt'=>(string)$row['alt_text'],'caption'=>(string)$row['caption'],'source'=>(string)$row['source_kind'],'updatedAt'=>dbIso((string)$row['updated_at'])];}
    return $out;
}
function mediaByKey(string $root,string $key): ?array {$stmt=db()->prepare('SELECT asset_key,public_path,mime_type,width,height,title,alt_text,caption,source_kind,updated_at FROM media_library WHERE asset_key=?');$stmt->execute([$key]);$row=$stmt->fetch();if(!is_array($row))return null;$path=(string)$row['public_path'];if(!mediaAllowedPath($path)||cmsSafePublicFile($root,$path)===null)return null;return ['key'=>(string)$row['asset_key'],'path'=>$path,'mime'=>(string)$row['mime_type'],'width'=>(int)$row['width'],'height'=>(int)$row['height'],'title'=>(string)$row['title'],'alt'=>(string)$row['alt_text'],'caption'=>(string)$row['caption'],'source'=>(string)$row['source_kind'],'updatedAt'=>dbIso((string)$row['updated_at'])];}

function mediaUpload(string $root,array $file,array $meta=[]): array {
    if(!isset($file['error'])||(int)$file['error']!==UPLOAD_ERR_OK||!isset($file['tmp_name'])||!is_uploaded_file((string)$file['tmp_name']))throw new RuntimeException('Media upload was not received safely.');
    $max=max(1024,(int)mediaConfig('max_upload_bytes',8388608));if((int)($file['size']??0)>$max)throw new RuntimeException('Media upload is too large.');
    $tmp=(string)$file['tmp_name'];$finfo=new finfo(FILEINFO_MIME_TYPE);$mime=(string)$finfo->file($tmp);$extensions=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];if(!isset($extensions[$mime]))throw new RuntimeException('Only JPEG, PNG, WebP, and GIF uploads are accepted.');
    $info=@getimagesize($tmp);if(!is_array($info)||($info[0]??0)<1||($info[1]??0)<1)throw new RuntimeException('Uploaded image could not be validated.');
    $uploadRoot=trim(str_replace('\\','/',(string)mediaConfig('upload_root','assets/uploads')),'/');if($uploadRoot===''||!mediaAllowedPath($uploadRoot.'/x.'.$extensions[$mime]))throw new RuntimeException('Configured media upload root is invalid.');
    $dir=rtrim($root,'/\\').'/'.$uploadRoot;if(!is_dir($dir)&&!mkdir($dir,0775,true)&&!is_dir($dir))throw new RuntimeException('Could not create media upload directory.');$realDir=realpath($dir);if($realDir===false||!pathInside($realDir,$root))throw new RuntimeException('Media upload directory escaped the public root.');
    $base=cleanSlug(pathinfo((string)($file['name']??'image'),PATHINFO_FILENAME))?:'image';$name=$base.'-'.substr(bin2hex(random_bytes(6)),0,10).'.'.$extensions[$mime];$target=$realDir.'/'.$name;
    if(!move_uploaded_file($tmp,$target))throw new RuntimeException('Could not store uploaded image.');$relative=$uploadRoot.'/'.$name;
    try{return mediaUpsertRecord($root,$relative,(string)($meta['title']??''),(string)($meta['alt']??''),(string)($meta['caption']??''),'upload');}catch(Throwable $e){@unlink($target);throw $e;}
}
