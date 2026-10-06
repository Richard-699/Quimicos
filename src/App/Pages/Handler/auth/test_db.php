<?php
header('Content-Type: text/plain');
ini_set('display_errors', '1');
error_reporting(E_ALL);

echo "Step 1: Reading database.json\n";
$configPath = __DIR__ . '/../../../../../config/database.json';
if (!file_exists($configPath)) {
    echo "Config file not found at: $configPath\n";
    exit;
}
$raw = file_get_contents($configPath);
echo "Config content: $raw\n";

$cfg = json_decode($raw, true);
$dbKey = isset($cfg['gestion_ambiental_hwi']) ? 'gestion_ambiental_hwi' : (isset($cfg['quimicos_hwi']) ? 'quimicos_hwi' : null);
echo "DB Key found: $dbKey\n";

if (!$dbKey) {
    echo "No valid DB key in config!\n";
    exit;
}

$dbConfig = $cfg[$dbKey];
$host = $dbConfig['host'];
$dbname = $dbConfig['database'];
$user = $dbConfig['user'];
$pass = $dbConfig['password'];

echo "Step 2: Connecting to PDO (host=$host, db=$dbname, user=$user)\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "SUCCESS: PDO connected successfully!\n";
    
    $stmt = $pdo->query("SELECT 1 as test");
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Query test: " . json_encode($res) . "\n";
    
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in DB: " . implode(", ", $tables) . "\n";
    
    echo "\nStep 3: Testing LoginService autoload & query\n";
    require_once __DIR__ . '/../../../../../vendor/autoload.php';
    $loginService = new \App\Application\Service\LoginService();
    echo "LoginService created successfully!\n";
    
    $adminRepo = new \App\Infrastructure\Repository\AdministradoresRepository($pdo);
    $admins = $adminRepo->onGet();
    echo "Total administradores found: " . count($admins) . "\n";
    
    echo "\nStep 4: Testing session path\n";
    $session_path = realpath(__DIR__ . '/../../../../../sessions');
    echo "Session path: " . var_export($session_path, true) . "\n";
    echo "is_dir: " . var_export(is_dir($session_path), true) . "\n";
    echo "is_writable: " . var_export(is_writable($session_path), true) . "\n";
    echo "PDO ERROR: " . $e->getMessage() . "\n";
} catch (Throwable $t) {
    echo "GENERAL ERROR: " . $t->getMessage() . "\n";
}
