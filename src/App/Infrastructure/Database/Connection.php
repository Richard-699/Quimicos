<?php
namespace App\Infrastructure\Database;

use PDO;
use PDOException;

class Connection {
    public $dbQuimicosHwi;

    public function __construct() {
        $configPath = '../../../../../config/database.json';
        $config = json_decode(file_get_contents($configPath), true);

        if (!$config) {
            die("Error al leer la configuración de la base de datos.");
        }

        $dsnQuimicosHwiHwi = "mysql:host={$config['quimicos_hwi']['host']};dbname={$config['quimicos_hwi']['database']};charset=utf8mb4";
        try {
            $this->dbQuimicosHwi = new PDO($dsnQuimicosHwiHwi, $config['quimicos_hwi']['user'], $config['quimicos_hwi']['password']);
            $this->dbQuimicosHwi->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error de conexión con la base de datos quimicos_hwi: " . $e->getMessage());
        }
    }
}
?>