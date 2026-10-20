<?php
namespace App\Infrastructure\Database;

use PDO;
use PDOException;

class Connection {
    public $dbQuimicosHwi;

    public function __construct() {
        $configPath = __DIR__ . '/../../../../config/database.json';
        if (!file_exists($configPath)) {
            $configPath = '../../../../../config/database.json';
        }
        $config = json_decode(file_get_contents($configPath), true);

        $dbKey = isset($config['gestion_ambiental_hwi']) ? 'gestion_ambiental_hwi' : (isset($config['quimicos_hwi']) ? 'quimicos_hwi' : array_key_first($config));
        $dbConf = $config[$dbKey] ?? null;

        if (!$dbConf || !is_array($dbConf)) {
            throw new \Exception("Error al leer la configuración de la base de datos en: " . realpath($configPath));
        }

        $dsnQuimicosHwiHwi = "mysql:host={$dbConf['host']};dbname={$dbConf['database']};charset=utf8mb4";
        try {
            $this->dbQuimicosHwi = new PDO($dsnQuimicosHwiHwi, $dbConf['user'], $dbConf['password']);
            $this->dbQuimicosHwi->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new \Exception("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }
}
?>