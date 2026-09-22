import pandas as pd

def obtener_consumos_mensuales(conexion):
    """Obtiene el histórico mensual de consumos (solicitudes aprobadas)."""
    query = """
    SELECT 
        q.descripcion_quimico,
        c.nombre_celula AS celula,
        DATE_FORMAT(sc.fecha_solicitud_consumo, '%Y-%m-01') AS fecha,
        SUM(sc.cantidad_solicitud_consumo) AS consumo_kg
    FROM quimicos_hwi_solicitudes_consumo AS sc
    INNER JOIN quimicos_hwi_quimicos AS q 
        ON sc.id_quimico_solicitud_consumo = q.id_quimico
    LEFT JOIN quimicos_hwi_celulas_areas AS c 
        ON sc.id_celula_area_solicitud_consumo = c.id_celulas_areas
    WHERE sc.id_estado_solicitud_quimico = 1 
      AND YEAR(sc.fecha_solicitud_consumo) >= 2026
    GROUP BY q.descripcion_quimico, c.nombre_celula, DATE_FORMAT(sc.fecha_solicitud_consumo, '%Y-%m-01');
    """
    return pd.read_sql(query, conexion)