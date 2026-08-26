<?php
declare(strict_types=1);

/** MySQL connection, adopter configuration, and small persistence helpers. */

function siteRootPath(): string { return dirname(__DIR__); }

function pathInside(string $candidate, string $root): bool {
    $rootReal = realpath($root);
    $candidateReal = realpath($candidate);
    if ($rootReal === false || $candidateReal === false) return false;
    $rootReal = rtrim(str_replace('\\','/',$rootReal),'/').'/';
    $candidateReal = str_replace('\\','/',$candidateReal);
    return $candidateReal === rtrim($rootReal,'/') || str_starts_with($candidateReal,$rootReal);
}

function siteConfig(): array {
    static $config = null;
    if (is_array($config)) return $config;
    $path = siteRootPath().'/config/site.php';
    if (!is_file($path)) $path = siteRootPath().'/config/site.example.php';
    if (!is_file($path)) return $config = [];
    $loaded = require $path;
    if (!is_array($loaded)) throw new RuntimeException('Site configuration must return an array.');
    return $config = $loaded;
}

function siteConfigValue(string $section, string $key, mixed $default = null): mixed {
    $config = siteConfig();
    if (!isset($config[$section]) || !is_array($config[$section])) return $default;
    return array_key_exists($key,$config[$section]) ? $config[$section][$key] : $default;
}

function siteSecret(string $key, string $default = ''): string {
    $env=getenv($key);
    if($env!==false && trim((string)$env)!=='') return (string)$env;
    static $fileSecrets=null;
    if($fileSecrets===null){
        $fileSecrets=[];
        $path=trim((string)(getenv('AINCMS_SECRET_CONFIG_FILE')?:''));
        if($path===''){
            // Shared-hosting convention: keep one hidden config file beside, never inside, the public site root.
            $conventional=dirname(siteRootPath()).'/.ai-native-cms.ini';
            if(is_file($conventional) && is_readable($conventional)) $path=$conventional;
        }
        if($path!==''){
            if(!is_file($path) || !is_readable($path)) throw new RuntimeException('Private configuration file is not readable.');
            if(pathInside($path,siteRootPath())) throw new RuntimeException('Private configuration file must live outside the public site root.');
            $parsed=parse_ini_file($path,false,INI_SCANNER_RAW);
            if(!is_array($parsed)) throw new RuntimeException('Private configuration file could not be parsed.');
            $fileSecrets=$parsed;
        }
    }
    return array_key_exists($key,$fileSecrets)?(string)$fileSecrets[$key]:$default;
}

function dbConfig(): array {
    $host=trim(siteSecret('AINCMS_DB_HOST','localhost'));
    $socket=trim(siteSecret('AINCMS_DB_SOCKET'));
    $local=$socket!=='' || in_array(strtolower($host),['localhost','127.0.0.1','::1'],true);
    $allowInsecureRemote=siteSecret('AINCMS_DB_ALLOW_INSECURE_REMOTE','0')==='1';
    $forceTls=siteSecret('AINCMS_DB_REQUIRE_TLS','0')==='1';
    return [
        'host' => $host,
        'port' => max(1,(int)siteSecret('AINCMS_DB_PORT','3306')),
        'socket' => $socket,
        'name' => trim(siteSecret('AINCMS_DB_NAME')),
        'user' => trim(siteSecret('AINCMS_DB_USER')),
        'password' => siteSecret('AINCMS_DB_PASSWORD'),
        'charset' => 'utf8mb4',
        'sslCa' => trim(siteSecret('AINCMS_DB_SSL_CA')),
        'requireTls' => $forceTls || (!$local && !$allowInsecureRemote),
        'remote' => !$local,
    ];
}

function dbConfigured(): bool {
    $cfg = dbConfig();
    return ($cfg['host'] !== '' || $cfg['socket'] !== '') && $cfg['name'] !== '' && $cfg['user'] !== '' && $cfg['password'] !== '';
}

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    if (!dbConfigured()) throw new RuntimeException('MySQL is not configured.');
    if (!extension_loaded('pdo_mysql')) throw new RuntimeException('PHP PDO MySQL support (pdo_mysql) is required.');
    $cfg = dbConfig();
    $dsn = $cfg['socket'] !== ''
        ? sprintf('mysql:unix_socket=%s;dbname=%s;charset=%s',$cfg['socket'],$cfg['name'],$cfg['charset'])
        : sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',$cfg['host'],$cfg['port'],$cfg['name'],$cfg['charset']);
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_PERSISTENT => false,
    ];
    if(defined('PDO::MYSQL_ATTR_MULTI_STATEMENTS')) $options[PDO::MYSQL_ATTR_MULTI_STATEMENTS]=false;
    if($cfg['sslCa']!==''){
        if(!is_file($cfg['sslCa'])||!is_readable($cfg['sslCa'])) throw new RuntimeException('MySQL CA file is not readable.');
        if(defined('PDO::MYSQL_ATTR_SSL_CA')) $options[PDO::MYSQL_ATTR_SSL_CA]=$cfg['sslCa'];
        if(defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=true;
    } elseif($cfg['requireTls'] && $cfg['socket']==='') {
        throw new RuntimeException('Remote MySQL requires TLS and AINCMS_DB_SSL_CA is not configured. Set AINCMS_DB_ALLOW_INSECURE_REMOTE=1 only for an explicitly accepted private-network exception.');
    }
    $pdo = new PDO($dsn, $cfg['user'], $cfg['password'], $options);
    $pdo->exec("SET time_zone = '+00:00'");
    return $pdo;
}

function dbJsonEncode(array $value): string {
    $json = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false) throw new RuntimeException('Could not encode database JSON.');
    return $json;
}

function dbJsonDecode(?string $value, array $fallback = []): array {
    if ($value === null || $value === '') return $fallback;
    $decoded = json_decode($value, true);
    return is_array($decoded) ? $decoded : $fallback;
}

function dbIso(?string $mysqlDate): ?string {
    if (!$mysqlDate) return null;
    $ts = strtotime($mysqlDate . ' UTC');
    return $ts ? gmdate('c', $ts) : $mysqlDate;
}

function dbNow(): string { return gmdate('Y-m-d H:i:s'); }

function dbHealth(): array {
    $pdo = db();
    $pdo->query('SELECT 1')->fetchColumn();
    $version = $pdo->query("SELECT schema_version FROM app_meta WHERE id = 1")->fetchColumn();
    return ['ok'=>true, 'schemaVersion'=>(int)$version];
}

function dbRequireSchemaVersion(int $minimum): void {
    $health=dbHealth();
    if((int)($health['schemaVersion']??0)<$minimum) throw new RuntimeException('Database schema upgrade required.');
}
