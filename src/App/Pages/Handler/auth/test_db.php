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
    
    echo "\nStep 3: PHP Version: " . phpversion() . " (ID: " . PHP_VERSION_ID . ")\n";
    try {
        require_once __DIR__ . '/../../../../../vendor/autoload.php';
        echo "Autoload loaded successfully!\n";
    } catch (\Throwable $e) {
        echo "Autoload error: " . $e->getMessage() . "\n";
    }
    echo "LoginService created successfully!\n";
    
    $adminRepo = new \App\Infrastructure\Repository\AdministradoresRepository($pdo);
    $admins = $adminRepo->onGet();
    echo "Total administradores found: " . count($admins) . "\n";
    
    echo "\nStep 5: Testing LoginService->login('test@test.com')\n";
    try {
        $dto = new \App\Domain\DTO\AdministradoresDTO(
            id_administrador: null,
            cedula_administrador: null,
            nombre_administrador: null,
            apellidos_administrador: null,
            correo_hwi_administrador: 'test@test.com',
            password_administrador: '123',
            password_is_temporal: null,
            estado_administrador: null,
            type: 'login'
        );
        echo "DTO created!\n";
        $loginResult = $loginService->login($dto);
        echo "Login result: " . var_export($loginResult, true) . "\n";
    } catch (\Throwable $e) {
        echo "Login attempt caught throwable: " . $e->getMessage() . "\n";
    }
} catch (Throwable $t) {
    echo "GENERAL ERROR: " . $t->getMessage() . "\n";
}
