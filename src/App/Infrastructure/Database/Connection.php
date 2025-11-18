<?php
namespace App\Infrastructure\Database;

use PDO;
use PDOException;

class Connection {
    public $dbInventarioHwi;

    public function __construct() {
        $configPath = '../../../../../config/database.json';
        $config = json_decode(file_get_contents($configPath), true);

        if (!$config) {
            die("Error al leer la configuración de la base de datos.");
        }

        $dsnInventarioHwi = "mysql:host={$config['inventario_hwi']['host']};dbname={$config['inventario_hwi']['database']};charset=utf8mb4";
        try {
            $this->dbInventarioHwi = new PDO($dsnInventarioHwi, $config['inventario_hwi']['user'], $config['inventario_hwi']['password']);
            $this->dbInventarioHwi->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error de conexión con la base de datos inventario_hwi: " . $e->getMessage());
        }
    }
}
?>