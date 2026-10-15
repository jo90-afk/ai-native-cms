<?php
declare(strict_types=1);
require_once __DIR__.'/runtime.php';

/**
 * Private publication coordination shared by authenticated writers and host
 * adapters. Canonical state may commit before a public projection succeeds;
 * this boundary makes that distinction explicit without making filesystem
 * markers a second content authority.
 */
function publicationStateRoot(string $root): string {
    $configured=trim(siteSecret('AINCMS_PUBLICATION_STATE_ROOT'));
    if($configured!==''){
        if(!str_starts_with($configured,'/'))throw new RuntimeException('Publication state directory must be an absolute path.');
        return rtrim($configured,'/');
    }
    $resolved=realpath($root);
    if($resolved===false)throw new RuntimeException('Public site root is unavailable.');
    return dirname($resolved).'/.ai-native-cms';
}
function publicationEnsureStateRoot(string $root): string {
    $dir=publicationStateRoot($root);
    if(!is_dir($dir)&&!mkdir($dir,0700,true)&&!is_dir($dir))throw new RuntimeException('Could not create the private publication-state directory.');
    $real=realpath($dir);
    if($real===false||pathInside($real,$root)||pathInside($root,$real))throw new RuntimeException('Publication state directory must be outside the public site.');
    @chmod($real,0700);
    if(!is_writable($real))throw new RuntimeException('The private publication-state directory is not writable by the CMS process.');
    return $real;
}
function publicationPendingKind(string $state): string {
    $path=rtrim($state,'/').'/public-update-pending/kind';
    if(!is_file($path)||is_link($path))return '';
    $kind=trim((string)@file_get_contents($path));
    return preg_match('/^[a-z0-9-]{1,64}$/',$kind)===1?$kind:'';
}

final class PublicationConflict extends RuntimeException {
    public function __construct(private bool $pending=false,private string $kind=''){parent::__construct($this->publicMessage());}
    public function publicMessage(): string {
        if($this->pending&&$this->kind==='cms-publication')return 'Previously saved changes are awaiting publication recovery. No further changes were saved.';
        if($this->pending)return 'An interrupted update needs recovery. Your changes were not saved; try again after recovery finishes.';
        return 'Publishing is busy. Your changes were not saved; try again shortly.';
    }
}

final class PublicationOutcome {
    private static ?array $saved=null;
    public static function saved(string $root,array $context=[]): void {
        self::$saved=['state'=>publicationEnsureStateRoot($root),'context'=>self::cleanContext($context)];
    }
    public static function hasSavedMutation(): bool {return self::$saved!==null;}
    public static function published(): void {self::$saved=null;}
    public static function recordFailure(Throwable $error,string $incident): bool {
        if(self::$saved===null)return false;
        $saved=self::$saved;self::$saved=null;$state=(string)$saved['state'];$pending=$state.'/public-update-pending';
        clearstatcache(true,$pending);
        if(file_exists($pending)||is_link($pending))return publicationPendingKind($state)==='cms-publication';
        $tmp=$state.'/public-update-pending.cms.'.bin2hex(random_bytes(6));
        if(!mkdir($tmp,0700))return false;
        try{
            $record=['kind'=>'cms-publication','createdAt'=>gmdate('c'),'incident'=>$incident,'errorClass'=>get_class($error),'context'=>$saved['context']];
            $json=json_encode($record,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)."\n";
            if(file_put_contents($tmp.'/kind',"cms-publication\n",LOCK_EX)===false||file_put_contents($tmp.'/record.json',$json,LOCK_EX)===false)throw new RuntimeException('Could not write pending publication record.');
            @chmod($tmp.'/kind',0600);@chmod($tmp.'/record.json',0600);
            if(!rename($tmp,$pending))throw new RuntimeException('Could not install pending publication record.');
            return true;
        }catch(Throwable $recordError){
            @unlink($tmp.'/kind');@unlink($tmp.'/record.json');@rmdir($tmp);
            error_log('[ai-native-cms publication outcome] '.$recordError->getMessage());
            return false;
        }
    }
    private static function cleanContext(array $context): array {
        $out=[];foreach($context as $key=>$value){$name=preg_replace('/[^A-Za-z0-9_.-]/','',(string)$key)??'';if($name==='')continue;if(is_bool($value)||is_int($value)||is_float($value)||$value===null)$out[$name]=$value;elseif(is_string($value))$out[$name]=substr($value,0,512);}return $out;
    }
}

function runtimePublicationError(Throwable $e,string $unsavedMessage='The request could not be completed.',int $unsavedStatus=500): never {
    if(PublicationOutcome::hasSavedMutation()){
        $incident=bin2hex(random_bytes(6));error_log('[ai-native-cms '.$incident.'] saved publication failure '.get_class($e).': '.$e->getMessage());$recorded=PublicationOutcome::recordFailure($e,$incident);
        runtimeJson(['ok'=>false,'error'=>$recorded?'Your changes were saved, but publication did not complete. Further publishing is blocked until recovery finishes.':'Your changes were saved, but publication did not complete and recovery state could not be recorded. Stop publishing and recover the site before making more changes.','incident'=>$incident,'saved'=>true,'published'=>false,'pendingPublication'=>$recorded,'retryable'=>false],503);
    }
    runtimeError($e,$unsavedMessage,$unsavedStatus);
}

final class PublicationLock {
    private static array $locks=[];
    private static array $requests=[];
    public static function acquire(string $root) {
        $state=publicationEnsureStateRoot($root);
        if(isset(self::$locks[$state])){self::$locks[$state]['references']++;return self::$locks[$state]['handle'];}
        $path=$state.'/aincms-publication.lock';$handle=fopen($path,'c+');if($handle===false)throw new RuntimeException('Could not open the private publication lock.');@chmod($path,0600);
        if(!flock($handle,LOCK_EX|LOCK_NB)){fclose($handle);throw new PublicationConflict();}
        clearstatcache(true,$state.'/public-update-pending');
        if(file_exists($state.'/public-update-pending')||is_link($state.'/public-update-pending')){$kind=publicationPendingKind($state);flock($handle,LOCK_UN);fclose($handle);throw new PublicationConflict(true,$kind);}
        self::$locks[$state]=['handle'=>$handle,'references'=>1];return $handle;
    }
    public static function release($handle): void {
        if(!is_resource($handle))return;foreach(self::$locks as $state=>$entry){if($entry['handle']!==$handle)continue;if(--self::$locks[$state]['references']>0)return;unset(self::$locks[$state]);flock($handle,LOCK_UN);fclose($handle);return;}
    }
    public static function holdForRequest(string $root): void {
        $state=publicationEnsureStateRoot($root);if(isset(self::$requests[$state]))return;$handle=self::acquire($root);self::$requests[$state]=$handle;register_shutdown_function(static function()use($state,$handle):void{if((self::$requests[$state]??null)!==$handle)return;unset(self::$requests[$state]);self::release($handle);});
    }
    public static function releaseForRequest(string $root): void {
        $state=publicationEnsureStateRoot($root);$handle=self::$requests[$state]??null;if(!is_resource($handle))return;unset(self::$requests[$state]);self::release($handle);
    }
}

function requireCmsPublicationLock(string $root): void {
    try{PublicationLock::holdForRequest($root);}catch(PublicationConflict $e){runtimeJson(['ok'=>false,'error'=>$e->publicMessage()],409);}catch(Throwable $e){runtimeError($e,'Publishing is temporarily unavailable.',503);}
}
