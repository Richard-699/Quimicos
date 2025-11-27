<?php

namespace App\Infrastructure\Repository;

use App\Application\Interface\Repository\IQuimicosCelulasAreasRepository;
use App\Domain\Model\Quimicos;
use App\Domain\Model\QuimicosCelulasAreas;
use PDO;

class QuimicosCelulasAreasRepository implements IQuimicosCelulasAreasRepository
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function save(QuimicosCelulasAreas $quimicosCelulasAreas): bool {
        $data = $quimicosCelulasAreas->toArray();
        $columnas = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $query = "INSERT INTO quimicos_hwi_quimicos_celulas_areas ($columnas) VALUES ($placeholders)";
        $stmt = $this->db->prepare($query);
        foreach ($data as $campo => $valor) {
            $stmt->bindValue(":$campo", $valor);
        }
        return $stmt->execute();
    }

    public function onGet_By__Id_Quimico(string $id_quimico): array
    {
        $stmt = $this->db->prepare("SELECT * FROM quimicos_hwi_quimicos_celulas_areas WHERE id_quimico_quimicos = ?");
        $stmt->execute([$id_quimico]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([QuimicosCelulasAreas::class, 'fromArray'], $rows);
    }
}
