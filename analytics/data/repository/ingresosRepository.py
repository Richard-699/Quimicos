import pandas as pd

def obtener_ingresos_inventario(conexion):
    """Obtiene el histórico de ingresos de inventario de los químicos."""
    query = """
    SELECT 
        q.descripcion_quimico,
        i.fecha_ingreso_inventario AS fecha_ingreso,
        i.cantidad_ingreso_inventario AS cantidad_ingreso
    FROM quimicos_hwi_logs_ingreso_inventario AS i
    INNER JOIN quimicos_hwi_quimicos AS q 
        ON i.id_quimico_ingreso_inventario = q.id_quimico
    ORDER BY i.fecha_ingreso_inventario DESC
    """
    return pd.read_sql(query, conexion)
