<?php

namespace App\Application\Service;

use App\Application\Interface\Service\IConsumoAguaService;
use App\Domain\DTO\ConsumoAguaDTO;
use App\Domain\Model\ConsumoAgua;
use App\Infrastructure\Repository\ConsumoAguaRepository;
use App\Infrastructure\Repository\CelulasAreasRepository;
use App\Infrastructure\Database\Connection;
use App\Shared\Mapper\Mapper;
use App\Shared\Util\Utilidades;
use Exception;
use PDO;

class ConsumoAguaService implements IConsumoAguaService
{
    private $db;
    private $consumoAguaRepository;
    private $celulasAreasRepository;

    public function __construct()
    {
        $this->db = (new Connection())->dbQuimicosHwi;
        $this->consumoAguaRepository = new ConsumoAguaRepository($this->db);
        $this->celulasAreasRepository = new CelulasAreasRepository($this->db);
    }

    public function onGetUltimosConsumos(int $id_celula): array
    {
        return $this->consumoAguaRepository->getLastConsumosByCelula($id_celula);
    }
    


    public function saveConsumoAgua(ConsumoAguaDTO $dto): bool
    {
        date_default_timezone_set('America/Bogota');
        $fechaRegistro = !empty($dto->fecha_consumo_agua) 
            ? date('Y-m-d', strtotime($dto->fecha_consumo_agua)) 
            : date('Y-m-d');

        // Validar si ya existe un registro para esa fecha en la célula / tanque
        if ($this->consumoAguaRepository->existeRegistroFecha($dto->id_celula_consumo_agua, $dto->id_tanque_abastecimiento_consumo_agua, $fechaRegistro)) {
            $tanque = $dto->id_tanque_abastecimiento_consumo_agua ? " para este tanque" : "";
            throw new Exception("Ya existe un registro de consumo de agua el día {$fechaRegistro} para esta célula{$tanque}.");
        }

        $dto->fecha_consumo_agua = "{$fechaRegistro} " . date('H:i:s');
        $consumoAgua = Mapper::consumoAguaDTOToModel($dto);

        $resultSave = $this->consumoAguaRepository->save($consumoAgua);
        $lastId = $resultSave ? $this->db->lastInsertId() : null;

        if ($resultSave) {
            try {
                $scriptPath = realpath(__DIR__ . '/../../../../analytics/logic/predict_consumo.py');
                if ($scriptPath) {
                    $idCelula   = escapeshellarg($dto->id_celula_consumo_agua);
                    $idTanque   = $dto->id_tanque_abastecimiento_consumo_agua ? escapeshellarg($dto->id_tanque_abastecimiento_consumo_agua) : 'null';
                    $consumoDia = $dto->consumo_final_agua - $dto->consumo_inicial_agua;
                    $consumoArg = escapeshellarg($consumoDia);
                    $idConsumo  = $lastId ? escapeshellarg((int)$lastId) : 'null';

                    $command = "python \"{$scriptPath}\" {$idCelula} {$idTanque} {$consumoArg} {$idConsumo} 2>&1";
                    $output = shell_exec($command);
                    
                    if ($output) {
                        $resultML = json_decode(trim($output), true);
                        
                        if ($resultML && isset($resultML['supera_tope']) && $resultML['supera_tope'] === true) {
                            $destinatario = 'ricardo.rojas@hacebwhirlpool.com';
                            $asunto = 'Alerta: Tope de Consumo de Agua Superado';
                            $titulo = 'Alerta Predictiva de Consumo de Agua';
                            $tope = $resultML['tope'];
                            
                            $celula = $this->celulasAreasRepository->findById($dto->id_celula_consumo_agua);
                            $nombreCelula = $celula ? $celula->nombre_celula : "Célula ID: {$dto->id_celula_consumo_agua}";
                            $tanqueStr = "General";
                            if ($dto->id_tanque_abastecimiento_consumo_agua) {
                                $idTanqueInt = (int)$dto->id_tanque_abastecimiento_consumo_agua;
                                $stmtT = $this->db->prepare("SELECT tanque_abastecimiento_agua FROM gestion_ambiental_hwi_tanques_abastecimiento_agua WHERE id_tanque_abastecimiento_agua = :id LIMIT 1");
                                $stmtT->bindValue(':id', $idTanqueInt, PDO::PARAM_INT);
                                $stmtT->execute();
                                $rowT = $stmtT->fetch(PDO::FETCH_ASSOC);
                                if ($rowT && !empty($rowT['tanque_abastecimiento_agua'])) {
                                    $tanqueStr = $rowT['tanque_abastecimiento_agua'];
                                } else {
                                    // Fallback: try raw query without prepared statement
                                    $rawResult = $this->db->query("SELECT tanque_abastecimiento_agua FROM gestion_ambiental_hwi_tanques_abastecimiento_agua WHERE id_tanque_abastecimiento_agua = {$idTanqueInt} LIMIT 1");
                                    $rawRow = $rawResult ? $rawResult->fetch(PDO::FETCH_ASSOC) : null;
                                    $tanqueStr = ($rawRow && !empty($rawRow['tanque_abastecimiento_agua'])) ? $rawRow['tanque_abastecimiento_agua'] : "Tanque {$idTanqueInt}";
                                }
                            }

                            $contenidoHtml = "
                                <p>Hola,</p>
                                <p>El sistema de Machine Learning ha detectado que el consumo registrado hoy supera el tope predictivo calculado para la célula.</p>
                                <ul>
                                    <li><strong>Célula:</strong> {$nombreCelula}</li>
                                    <li><strong>Tanque:</strong> {$tanqueStr}</li>
                                    <li><strong>Consumo Registrado:</strong> {$consumoDia} m³</li>
                                    <li><strong>Tope Calculado (ML):</strong> {$tope} m³</li>
                                </ul>
                                <p>Por favor, revisa el consumo para verificar posibles fugas o anomalías.</p>
                            ";

                            Utilidades::enviarCorreo($destinatario, $asunto, $titulo, $contenidoHtml);
                        }
                    }
                }
            } catch (Exception $e) {
                error_log("Error ejecutando modelo ML de consumo de agua: " . $e->getMessage());
            }
        }

        return $resultSave;
    }

    public function updateConsumoAgua(ConsumoAguaDTO $dto): bool
    {
        if (empty($dto->id_consumo_agua)) {
            throw new Exception("ID del registro es obligatorio para actualizar.");
        }

        $existente = $this->consumoAguaRepository->findById($dto->id_consumo_agua);
        if (!$existente) {
            throw new Exception("El registro de consumo no existe.");
        }

        if ($dto->consumo_final_agua < $existente->consumo_inicial_agua) {
            throw new Exception("El consumo final no puede ser menor al inicial (" . number_format($existente->consumo_inicial_agua, 4) . ").");
        }

        $consumoAgua = Mapper::consumoAguaDTOToModel($dto);
        $result = $this->consumoAguaRepository->update($consumoAgua);

        if ($result) {
            // Reejecutar evaluación predictiva ML en segundo plano
            try {
                $scriptPath = realpath(__DIR__ . '/../../../../analytics/logic/predict_consumo.py');
                if ($scriptPath) {
                    $idCelula   = escapeshellarg($existente->id_celula_consumo_agua);
                    $idTanque   = $existente->id_tanque_abastecimiento_consumo_agua ? escapeshellarg($existente->id_tanque_abastecimiento_consumo_agua) : 'null';
                    $consumoDia = $dto->consumo_final_agua - $existente->consumo_inicial_agua;
                    $consumoArg = escapeshellarg($consumoDia);
                    $idConsumo  = escapeshellarg((int)$dto->id_consumo_agua);

                    $command = "python \"{$scriptPath}\" {$idCelula} {$idTanque} {$consumoArg} {$idConsumo} 2>&1";
                    shell_exec($command);
                }
            } catch (Exception $e) {
                error_log("Error actualizando score ML: " . $e->getMessage());
            }
        }

        return $result;
    }
}
