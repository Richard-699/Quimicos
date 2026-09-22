import pandas as pd

def obtener_precios_mensuales(conexion):
    """Obtiene el histórico de precios promedio por mes."""
    query = """
    SELECT 
        q.descripcion_quimico,
        DATE_FORMAT(lp.fecha_log_precio, '%Y-%m-01') AS fecha,
        AVG(lp.precio_quimico) AS precio_quimico
    FROM quimicos_hwi_logs_precios AS lp
    INNER JOIN quimicos_hwi_quimicos AS q 
        ON lp.id_quimico_log_precio = q.id_quimico
    WHERE YEAR(lp.fecha_log_precio) >= 2026
    GROUP BY q.descripcion_quimico, DATE_FORMAT(lp.fecha_log_precio, '%Y-%m-01');
    """
    return pd.read_sql(query, conexion)