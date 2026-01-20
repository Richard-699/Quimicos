<?php

namespace App\Application\Service;

use App\Application\Interface\Service\ISolicitudesConsumoService;
use Exception;
use App\Infrastructure\Database\Connection;
use App\Domain\DTO\SolicitudesConsumoDTO;
use App\Infrastructure\Repository\SolicitudesConsumoRepository;
use App\Shared\Mapper\Mapper;
use App\Application\Service\QuimicosService;
use App\Shared\Util\Utilidades;

class SolicitudesConsumoService implements ISolicitudesConsumoService
{

    private $db;
    private $solicitudesConsumoRepository;
    private $quimicosService;

    public function __construct()
    {
        $this->db = (new Connection())->dbQuimicosHwi;

        $this->solicitudesConsumoRepository = new SolicitudesConsumoRepository($this->db);
        $this->quimicosService = new QuimicosService($this->db);
    }

    public function saveSolicitudQuimico(SolicitudesConsumoDTO $solicitudesConsumoDTO): bool
    {
        try {
            $this->db->beginTransaction();

            $solicitudesConsumo = Mapper::solicitudesConsumoDTOToModel($solicitudesConsumoDTO);

            $saveSolicitudConsumo = $this->solicitudesConsumoRepository->save($solicitudesConsumo);
            if (!$saveSolicitudConsumo) {
                throw new Exception("No se pudo registrar la solicitud");
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Error al guardar la solicitud: ' . $e->getMessage());
            return false;
        }
    }

    public function onGetSolicitudes(): array
    {
        try {
            $id_estado = 3; //Pendiente
            $solicitudesBd = $this->solicitudesConsumoRepository->onGet_By__Id_Estado($id_estado);
            $solicitudes = Mapper::modelToSolicitudesConsumosDTO($solicitudesBd);

            $celulas_areas = $this->quimicosService->ongetCelulas();

            foreach ($solicitudes as $solicitud) {
                $id = $solicitud->id_celula_area_solicitud_consumo;
                foreach ($celulas_areas as $celula) {
                    if ($id == $celula->id_celulas_areas) {
                        $solicitud->celula_area = $celula->nombre_celula;
                    }
                }
            }

            $quimicos = $this->quimicosService->ongetQuimicos();

            foreach ($solicitudes as $solicitud) {
                $id = $solicitud->id_quimico_solicitud_consumo;
                foreach ($quimicos as $quimico) {
                    if ($id == $quimico->id_quimico) {
                        $solicitud->descripcion_quimico = $quimico->descripcion_quimico;
                        $solicitud->umb_quimico = $quimico->umb_quimico;
                    }
                }
            }

            if (!$solicitudes) {
                throw new Exception("No se encontraron solicitudes");
            }
            return $solicitudes;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function updateEstadoSolicitud(int $id_solicitud, int $id_estado, ?float $cantidad_solicitud, ?string $id_quimico): bool
    {
        try {
            $this->db->beginTransaction();

            if ($id_estado == 1) {
                $quimico = $this->quimicosService->onGetQuimicos_By__Id($id_quimico);

                if (!$quimico) {
                    throw new \Exception("Error al obtener el químico.");
                }

                $cantidad_disponible = $quimico->cantidad_disponible_quimico;
                $new_cantidad_disponible = $cantidad_disponible - $cantidad_solicitud;
                $quimico->cantidad_disponible_quimico = $new_cantidad_disponible;

                $update_quimico = $this->quimicosService->updateQuimicos($quimico);

                if (!$update_quimico) {
                    throw new \Exception("Error al actualizar el químico");
                }

                $tope_minimo = $quimico->tope_minimo_quimico;
                $rango_preventivo = $tope_minimo + ($tope_minimo * 0.20);

                $enviar_correo = false;

                if ($new_cantidad_disponible <= $tope_minimo) {
                    $contenido = '<p style="margin-top: 10px;">⚠ Nivel crítico: 
                        El químico ha llegado o bajado del tope mínimo 
                        ('. $quimico->tope_minimo_quimico. ' ' .$quimico->umb_quimico.') 
                        quedan disponibles: ' .$new_cantidad_disponible. '.</p>';
                    $titulo = '⚠ Nivel crítico con el químico: ' . $quimico->descripcion_quimico;
                    $enviar_correo = true;
                } elseif ($new_cantidad_disponible <= $rango_preventivo) {
                    $contenido = '<p style="margin-top: 10px;">⚠ Atención: 
                        La cantidad disponible se está acercando al tope mínimo
                        ('. $quimico->tope_minimo_quimico . ' ' .$quimico->umb_quimico.') 
                        quedan disponibles: ' .$new_cantidad_disponible. '.</p>';
                    $titulo = '⚠ Atención con el químico: ' . $quimico->descripcion_quimico;
                    $enviar_correo = true;
                }

                if ($enviar_correo) {
                    $email_superAdmin = 'almacen.indirectos@hacebwhirlpool.com';
                    $asunto = 'Alerta Inventario Químico';
                    $utilidades = new Utilidades();
                    $enviarCorreo = $utilidades->enviarCorreo($email_superAdmin, $asunto, $titulo, $contenido);

                    if (!$enviarCorreo) {
                        throw new Exception('Error al enviar el correo.');
                    }
                }
            }

            $update_estado = $this->solicitudesConsumoRepository->update_Id_Estado_By__Id($id_solicitud, $id_estado);

            if (!$update_estado) {
                throw new \Exception("No se pudo actualizar el estado de la solicitud.");
            }

            $this->db->commit();

            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
