<?php
namespace App\Infrastructure\Repository;

use App\Application\Interface\Repository\IPermisosRepository;
use PDO;

class PermisosRepository implements IPermisosRepository {

    private $db;
    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findAll(): array {
        $stmt = $this->db->prepare("SELECT * FROM gestion_ambiental_hwi_permisos ORDER BY tipo_permiso ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>