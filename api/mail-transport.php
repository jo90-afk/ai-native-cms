<?php
declare(strict_types=1);
require_once __DIR__.'/runtime.php';

/** Replaceable transactional-mail transport. Credentials are read only from private runtime configuration. */
function mailTransportConfig(): array {
    $transport=strtolower(trim(siteSecret('AINCMS_MAIL_TRANSPORT','')));$security=strtolower(trim(siteSecret('AINCMS_MAIL_SECURITY','ssl')));
    return ['transport'=>$transport,'host'=>trim(siteSecret('AINCMS_MAIL_HOST','')),'port'=>(int)siteSecret('AINCMS_MAIL_PORT','0'),'security'=>$security,'username'=>trim(siteSecret('AINCMS_MAIL_USERNAME','')),'password'=>siteSecret('AINCMS_MAIL_PASSWORD',''),'from'=>trim(siteSecret('AINCMS_MAIL_FROM','')),'fromName'=>trim(siteSecret('AINCMS_MAIL_FROM_NAME',(string)siteConfigValue('site','name','Site')))];
}
function mailTransportStatus(): array {
    $c=mailTransportConfig();$issues=[];$allowed=['smtp','mail','log'];if(!in_array($c['transport'],$allowed,true))$issues[]='Choose smtp, mail, or log transport.';
    if(!filter_var($c['from'],FILTER_VALIDATE_EMAIL))$issues[]='A valid sender address is required.';
    if($c['transport']==='smtp'){
        if($c['host']===''||preg_match('/[\x00-\x20\x7f]/',$c['host']))$issues[]='SMTP host is required.';
        if($c['port']<1||$c['port']>65535)$issues[]='SMTP port is invalid.';
        if(!in_array($c['security'],['ssl','tls','none'],true))$issues[]='SMTP security must be ssl, tls, or none.';
        if($c['security']==='none'&&!runtimeIsDevelopment())$issues[]='Unencrypted SMTP is disabled outside development.';
        if($c['username']===''||$c['password']==='')$issues[]='SMTP credentials are incomplete.';
    }
    if($c['transport']==='log'&&!runtimeIsDevelopment()&&siteSecret('AINCMS_ALLOW_MAIL_LOG','0')!=='1')$issues[]='Log mail transport is disabled outside development.';
    return ['configured'=>$issues===[],'transport'=>$c['transport']?:'not-configured','host'=>$c['transport']==='smtp'?$c['host']:'','port'=>$c['transport']==='smtp'?$c['port']:0,'security'=>$c['transport']==='smtp'?$c['security']:'','usernameSet'=>$c['username']!=='','from'=>$c['from'],'fromName'=>$c['fromName'],'issues'=>$issues];
}
function mailTransportValidateHeader(string $value,string $label,int $max): string {$value=trim($value);if($value===''||strlen($value)>$max||preg_match('/[\r\n\x00]/',$value))throw new RuntimeException($label.' is invalid.');return $value;}
function mailTransportMailbox(string $email): string {$email=strtolower(trim($email));if(strlen($email)>254||filter_var($email,FILTER_VALIDATE_EMAIL)===false)throw new RuntimeException('Mail recipient is invalid.');return $email;}
function mailTransportSmtpRead($socket,array $codes,string $stage): string {
    $lines=[];$code=0;while(($line=fgets($socket,4096))!==false){$lines[]=rtrim($line,"\r\n");if(preg_match('/^(\d{3})([ -])/',$line,$m)){if($code===0)$code=(int)$m[1];if($m[2]===' ')break;}if(count($lines)>40)break;}
    if(!in_array($code,$codes,true))throw new RuntimeException('SMTP '.$stage.' failed with response code '.($code?:0).'.');return implode("\n",$lines);
}
function mailTransportSmtpCommand($socket,string $command,array $codes,string $stage): string {if(fwrite($socket,$command."\r\n")===false)throw new RuntimeException('SMTP '.$stage.' write failed.');return mailTransportSmtpRead($socket,$codes,$stage);}
function mailTransportSmtpSend(array $c,string $to,string $subject,string $body): void {
    $host=$c['host'];$port=(int)$c['port'];$security=$c['security'];$context=stream_context_create(['ssl'=>['verify_peer'=>true,'verify_peer_name'=>true,'peer_name'=>$host,'SNI_enabled'=>true]]);$target=($security==='ssl'?'ssl://':'tcp://').$host.':'.$port;$errno=0;$errstr='';$socket=@stream_socket_client($target,$errno,$errstr,12,STREAM_CLIENT_CONNECT,$context);if(!is_resource($socket))throw new RuntimeException('SMTP connection failed.');stream_set_timeout($socket,12);
    try{
        mailTransportSmtpRead($socket,[220],'greeting');$ehloHost=(string)(parse_url(publicOrigin(),PHP_URL_HOST)??'localhost.localdomain');if($ehloHost==='')$ehloHost='localhost.localdomain';mailTransportSmtpCommand($socket,'EHLO '.$ehloHost,[250],'EHLO');
        if($security==='tls'){mailTransportSmtpCommand($socket,'STARTTLS',[220],'STARTTLS');if(stream_socket_enable_crypto($socket,true,STREAM_CRYPTO_METHOD_TLS_CLIENT)!==true)throw new RuntimeException('SMTP TLS negotiation failed.');mailTransportSmtpCommand($socket,'EHLO '.$ehloHost,[250],'EHLO after TLS');}
        if($security==='none'&&!runtimeIsDevelopment())throw new RuntimeException('Unencrypted SMTP is disabled outside development.');
        mailTransportSmtpCommand($socket,'AUTH LOGIN',[334],'authentication');mailTransportSmtpCommand($socket,base64_encode($c['username']),[334],'authentication username');mailTransportSmtpCommand($socket,base64_encode($c['password']),[235],'authentication password');
        mailTransportSmtpCommand($socket,'MAIL FROM:<'.$c['from'].'>',[250],'sender');mailTransportSmtpCommand($socket,'RCPT TO:<'.$to.'>',[250,251],'recipient');mailTransportSmtpCommand($socket,'DATA',[354],'message data');
        $fromName=mailTransportValidateHeader($c['fromName']!==''?$c['fromName']:'Site','Mail sender name',191);$headers=['From: '.$fromName.' <'.$c['from'].'>','To: <'.$to.'>','Subject: '.$subject,'Date: '.gmdate('D, d M Y H:i:s').' +0000','Message-ID: <'.bin2hex(random_bytes(12)).'@'.$ehloHost.'>','MIME-Version: 1.0','Content-Type: text/plain; charset=UTF-8','Content-Transfer-Encoding: 8bit'];$payload=implode("\r\n",$headers)."\r\n\r\n".str_replace("\n","\r\n",str_replace(["\r\n","\r"],"\n",$body));$payload=preg_replace('/(^|\r\n)\./','$1..',$payload)??$payload;if(fwrite($socket,$payload."\r\n.\r\n")===false)throw new RuntimeException('SMTP message write failed.');mailTransportSmtpRead($socket,[250],'message acceptance');@mailTransportSmtpCommand($socket,'QUIT',[221],'quit');
    }finally{fclose($socket);}
}
function mailTransportSend(string $to,string $subject,string $body): void {
    $to=mailTransportMailbox($to);$subject=mailTransportValidateHeader($subject,'Mail subject',255);$body=trim($body);if($body===''||strlen($body)>65536)throw new RuntimeException('Mail body is invalid.');$c=mailTransportConfig();$status=mailTransportStatus();if(!$status['configured'])throw new RuntimeException('Outgoing mail transport is not configured.');
    if($c['transport']==='log'){$stmt=db()->prepare('INSERT INTO mail_outbox (recipient,subject,body,created_at) VALUES (?,?,?,UTC_TIMESTAMP())');$stmt->execute([$to,$subject,$body]);return;}
    if($c['transport']==='mail'){$headers=['From: '.mailTransportValidateHeader($c['fromName']!==''?$c['fromName']:'Site','Mail sender name',191).' <'.$c['from'].'>','Content-Type: text/plain; charset=UTF-8','Content-Transfer-Encoding: 8bit'];if(!@mail($to,$subject,$body,implode("\r\n",$headers)))throw new RuntimeException('Local mail transport rejected the message.');return;}
    mailTransportSmtpSend($c,$to,$subject,$body);
}
