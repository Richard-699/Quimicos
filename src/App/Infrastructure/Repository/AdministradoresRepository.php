<?php

namespace App\Infrastructure\Repository;

use App\Domain\Model\Administradores;
use App\Application\Interface\Repository\IAdministradoresRepository;
use PDO;

class AdministradoresRepository implements IAdministradoresRepository
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function onGet(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM quimicos_hwi_administradores");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([Administradores::class, 'fromArray'], $rows);
    }

    public function onGet_By__Id($id): ?Administradores
    {
        $stmt = $this->db->prepare("SELECT * FROM quimicos_hwi_administradores WHERE id_administrador = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return Administradores::fromArray($row);
    }


    public function onGet_By__Email(string $correo_hwi_administrador): ?Administradores
    {
        $stmt = $this->db->prepare("SELECT * FROM quimicos_hwi_administradores WHERE correo_hwi_administrador = ?");
        $stmt->execute([$correo_hwi_administrador]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }
        return Administradores::fromArray($row);
    }

    public function save(Administradores $administradores): bool
    {
        $data = $administradores->toArray();
        $columnas = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $query = "INSERT INTO quimicos_hwi_administradores ($columnas) VALUES ($placeholders)";
        $stmt = $this->db->prepare($query);
        foreach ($data as $campo => $valor) {
            $stmt->bindValue(":$campo", $valor);
        }
        return $stmt->execute();
    }

    public function update_password(Administradores $administradores): bool
    {
        $query = "UPDATE quimicos_hwi_administradores 
                    SET password_administrador = :password, password_is_temporal = :istemporal 
                    WHERE id_administrador = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':password', $administradores->password_administrador);
        $stmt->bindParam(':istemporal', $administradores->password_is_temporal);
        $stmt->bindParam(':id', $administradores->id_administrador);

        return $stmt->execute();
    }

    public function updateStatusAdministrador($id, $id_estado): bool
    {
        $query = "UPDATE quimicos_hwi_administradores 
                    SET estado_administrador = :estado_administrador
                    WHERE id_administrador = :id_administrador";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':estado_administrador', $id_estado);
        $stmt->bindParam(':id_administrador', $id);

        return $stmt->execute();
    }

    public function delete(string $id)
    {
        $stmt = $this->db->prepare("DELETE FROM quimicos_hwi_administradores WHERE id_administrador = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
