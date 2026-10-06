<?php
namespace App\Infrastructure\Repository;

use App\Application\Interface\Repository\IPermisosAdministradoresRepository;
use PDO;

class PermisosAdministradoresRepository implements IPermisosAdministradoresRepository {
    private $db;
    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findPermisosByAdministradorId(string $id_administrador): array {
        $stmt = $this->db->prepare("SELECT * FROM gestion_ambiental_hwi_permisos_administradores pa
                                    INNER JOIN gestion_ambiental_hwi_permisos p ON pa.id_permiso_permisos = p.id_permiso
                                    WHERE id_administrador_permisos = :id_administrador");
        $stmt->bindParam(':id_administrador', $id_administrador);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignPermisosAdministradores(int $id_permiso, string $id_administrador): bool {
        $stmt = $this->db->prepare("INSERT INTO gestion_ambiental_hwi_permisos_administradores
                                    (id_permiso_permisos, id_administrador_permisos)
                                    VALUES (:id_permiso, :id_administrador)");
        $stmt->bindValue(':id_permiso', $id_permiso, PDO::PARAM_INT);
        $stmt->bindValue(':id_administrador', $id_administrador, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function removeAllPermisosFromAdministradorByAdministradorId(string $id_administrador): int
    {
        $stmt = $this->db->prepare("DELETE FROM gestion_ambiental_hwi_permisos_administradores WHERE id_administrador_permisos = :id");
        $stmt->bindParam(':id', $id_administrador);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
?>