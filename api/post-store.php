<?php
declare(strict_types=1);
require_once __DIR__.'/runtime.php';
require_once __DIR__.'/slug.php';

/** Canonical long-form post persistence and revision history. */

function postFromRow(array $row): array {
    $post=[
        'slug'=>(string)$row['slug'],
        'title'=>(string)$row['title'],
        'dek'=>(string)$row['dek'],
        'category'=>(string)$row['category'],
        'categoryLabel'=>(string)$row['category_label'],
        'date'=>(string)$row['published_date'],
        'status'=>(string)$row['status'],
        'featured'=>(bool)$row['featured'],
        'tags'=>dbJsonDecode((string)($row['tags_json']??'[]')),
        'thesis'=>(string)($row['thesis']??''),
        'related'=>dbJsonDecode((string)($row['related_json']??'[]')),
        'territoryImage'=>(string)($row['territory_image']??''),
        'featuredImage'=>(string)($row['featured_image']??''),
        'socialImage'=>(string)($row['social_image']??''),
        'imageAlt'=>(string)($row['image_alt']??''),
        'body'=>(string)$row['body'],
        'updatedAt'=>dbIso((string)$row['updated_at']),
    ];
    $post['revisionHash']=postRevisionHash($post);
    return $post;
}

function postRevisionPayload(array $post): array {
    return [
        'slug'=>(string)($post['slug']??''),'title'=>(string)($post['title']??''),'dek'=>(string)($post['dek']??''),
        'category'=>(string)($post['category']??''),'categoryLabel'=>(string)($post['categoryLabel']??''),'date'=>(string)($post['date']??''),
        'status'=>(string)($post['status']??'draft'),'featured'=>(bool)($post['featured']??false),'tags'=>array_values((array)($post['tags']??[])),
        'thesis'=>(string)($post['thesis']??''),'related'=>array_values((array)($post['related']??[])),
        'territoryImage'=>(string)($post['territoryImage']??''),'featuredImage'=>(string)($post['featuredImage']??''),'socialImage'=>(string)($post['socialImage']??''),'imageAlt'=>(string)($post['imageAlt']??''),
        'body'=>(string)($post['body']??''),
    ];
}

function postRevisionHash(array $post): string {
    $json=json_encode(postRevisionPayload($post),JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    if(!is_string($json))throw new RuntimeException('Could not encode post revision.');
    return hash('sha256',$json);
}

function postFind(string $slug): ?array {
    $stmt=db()->prepare('SELECT * FROM posts WHERE slug=? LIMIT 1');$stmt->execute([cleanSlug($slug)]);$row=$stmt->fetch();
    return is_array($row)?postFromRow($row):null;
}

function allPosts(): array {
    $rows=db()->query('SELECT * FROM posts ORDER BY updated_at DESC,id DESC')->fetchAll();
    return array_map('postFromRow',$rows);
}

function publishedPosts(): array {
    $rows=db()->query("SELECT * FROM posts WHERE status='published' ORDER BY published_date DESC,id DESC")->fetchAll();
    return array_map('postFromRow',$rows);
}

function normalizePost(array $post): array {
    $slug=cleanSlug((string)($post['slug']??''));
    $category=cleanSlug((string)($post['category']??'writing'))?:'writing';
    $status=((string)($post['status']??'draft'))==='published'?'published':'draft';
    $date=preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)($post['date']??''))?(string)$post['date']:gmdate('Y-m-d');
    $tags=[];foreach((array)($post['tags']??[]) as $tag){$tag=trim((string)$tag);if($tag!==''&&!in_array($tag,$tags,true))$tags[]=$tag;}
    $related=[];foreach((array)($post['related']??[]) as $item){$value=cleanSlug((string)$item);if($value!==''&&!in_array($value,$related,true))$related[]=$value;}
    return [
        'slug'=>$slug,'title'=>trim((string)($post['title']??'')),'dek'=>trim((string)($post['dek']??'')),
        'category'=>$category,'categoryLabel'=>trim((string)($post['categoryLabel']??''))?:ucwords(str_replace('-',' ',$category)),
        'date'=>$date,'status'=>$status,'featured'=>(bool)($post['featured']??false),'tags'=>$tags,
        'thesis'=>trim((string)($post['thesis']??'')),'related'=>$related,
        'territoryImage'=>trim((string)($post['territoryImage']??'')),'featuredImage'=>trim((string)($post['featuredImage']??'')),
        'socialImage'=>trim((string)($post['socialImage']??'')),'imageAlt'=>trim((string)($post['imageAlt']??'')),
        'body'=>(string)($post['body']??''),
    ];
}

function validatePost(array $post): void {
    if($post['slug']===''||strlen($post['slug'])>191)throw new RuntimeException('A valid slug is required.');
    if($post['title']===''||strlen($post['title'])>512)throw new RuntimeException('A title is required and must be 512 characters or fewer.');
    if(strlen($post['dek'])>4000||strlen($post['thesis'])>10000||strlen($post['body'])>4000000)throw new RuntimeException('One or more post fields are too large.');
    if(strlen($post['category'])>100||strlen($post['categoryLabel'])>191)throw new RuntimeException('Post category is too long.');
    if(count($post['tags'])>100||count($post['related'])>100)throw new RuntimeException('Too many post relationships were supplied.');
    foreach($post['tags'] as $tag)if(strlen((string)$tag)>191)throw new RuntimeException('A post tag is too long.');
    foreach(['territoryImage','featuredImage','socialImage'] as $key)if(strlen((string)$post[$key])>512)throw new RuntimeException('An image path is too long.');
    if(strlen((string)$post['imageAlt'])>1000)throw new RuntimeException('Image alt text is too long.');
}

function savePostToDatabase(array $input,string $originalSlug='',?string $expectedHash=null): array {
    $post=normalizePost($input);validatePost($post);$pdo=db();$originalSlug=cleanSlug($originalSlug!==''?$originalSlug:$post['slug']);$pdo->beginTransaction();
    try{
        $existing=null;
        if($originalSlug!==''){$stmt=$pdo->prepare('SELECT * FROM posts WHERE slug=? FOR UPDATE');$stmt->execute([$originalSlug]);$row=$stmt->fetch();if(is_array($row))$existing=$row;}
        if($existing&&$expectedHash!==null&&$expectedHash!==''&&!hash_equals($expectedHash,postRevisionHash(postFromRow($existing))))throw new RuntimeException('Post changed since it was opened. Refresh before saving.');
        if($originalSlug!==$post['slug']){$check=$pdo->prepare('SELECT id FROM posts WHERE slug=? LIMIT 1');$check->execute([$post['slug']]);if($check->fetchColumn())throw new RuntimeException('That slug is already in use.');}
        $userId=(int)($_SESSION['cms_user_id']??0);
        if($existing){
            $snapshot=postFromRow($existing);$rev=$pdo->prepare('INSERT INTO post_revisions (post_id,user_id,original_slug,snapshot_json,created_at) VALUES (?,NULLIF(?,0),?,?,UTC_TIMESTAMP())');$rev->execute([(int)$existing['id'],$userId,$originalSlug,dbJsonEncode(postRevisionPayload($snapshot))]);
            $stmt=$pdo->prepare('UPDATE posts SET slug=?,title=?,dek=?,category=?,category_label=?,published_date=?,status=?,featured=?,tags_json=?,thesis=?,related_json=?,territory_image=?,featured_image=?,social_image=?,image_alt=?,body=?,updated_at=UTC_TIMESTAMP() WHERE id=?');
            $stmt->execute([$post['slug'],$post['title'],$post['dek'],$post['category'],$post['categoryLabel'],$post['date'],$post['status'],$post['featured']?1:0,dbJsonEncode($post['tags']),$post['thesis'],dbJsonEncode($post['related']),$post['territoryImage'],$post['featuredImage'],$post['socialImage'],$post['imageAlt'],$post['body'],(int)$existing['id']]);
        }else{
            $stmt=$pdo->prepare('INSERT INTO posts (slug,title,dek,category,category_label,published_date,status,featured,tags_json,thesis,related_json,territory_image,featured_image,social_image,image_alt,body,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP())');
            $stmt->execute([$post['slug'],$post['title'],$post['dek'],$post['category'],$post['categoryLabel'],$post['date'],$post['status'],$post['featured']?1:0,dbJsonEncode($post['tags']),$post['thesis'],dbJsonEncode($post['related']),$post['territoryImage'],$post['featuredImage'],$post['socialImage'],$post['imageAlt'],$post['body']]);
        }
        $saved=postFind($post['slug']);if(!$saved)throw new RuntimeException('Saved post could not be reloaded.');$pdo->commit();return $saved;
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
}

function postRevisions(string $slug,int $limit=30): array {
    $post=postFind($slug);if(!$post)return [];$limit=max(1,min(100,$limit));
    $stmt=db()->prepare('SELECT id,original_slug,snapshot_json,created_at FROM post_revisions WHERE post_id=(SELECT id FROM posts WHERE slug=? LIMIT 1) ORDER BY id DESC LIMIT '.(int)$limit);$stmt->execute([$post['slug']]);$out=[];
    foreach($stmt->fetchAll() as $row){$snapshot=dbJsonDecode((string)$row['snapshot_json']);$out[]=['id'=>(int)$row['id'],'originalSlug'=>(string)$row['original_slug'],'createdAt'=>dbIso((string)$row['created_at']),'snapshot'=>$snapshot,'revisionHash'=>postRevisionHash($snapshot)];}
    return $out;
}

function restorePostRevision(string $slug,int $revisionId,string $expectedHash): array {
    $current=postFind($slug);if(!$current)throw new RuntimeException('Post not found.');
    $stmt=db()->prepare('SELECT snapshot_json FROM post_revisions WHERE id=? AND post_id=(SELECT id FROM posts WHERE slug=? LIMIT 1)');$stmt->execute([$revisionId,$current['slug']]);$raw=$stmt->fetchColumn();if(!is_string($raw))throw new RuntimeException('Revision not found.');
    $snapshot=dbJsonDecode($raw);return savePostToDatabase($snapshot,$current['slug'],$expectedHash);
}
