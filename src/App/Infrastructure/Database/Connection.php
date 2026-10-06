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

        if (!$config || !isset($config['gestion_ambiental_hwi'])) {
            throw new \Exception("Error al leer la configuración de la base de datos en: " . realpath($configPath));
        }

        $dsnQuimicosHwiHwi = "mysql:host={$config['gestion_ambiental_hwi']['host']};dbname={$config['gestion_ambiental_hwi']['database']};charset=utf8mb4";
        try {
            $this->dbQuimicosHwi = new PDO($dsnQuimicosHwiHwi, $config['gestion_ambiental_hwi']['user'], $config['gestion_ambiental_hwi']['password']);
            $this->dbQuimicosHwi->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new \Exception("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }
}
?>