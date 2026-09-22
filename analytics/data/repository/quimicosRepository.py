import pandas as pd

def obtener_maestro_quimicos(conexion):
    """Obtiene los datos logísticos y stock actual de los químicos."""
    query = """
    SELECT 
        q.descripcion_quimico,
        u.descripcion_umb AS umb,
        q.cantidad_disponible_quimico AS stock_actual,
        q.tope_minimo_quimico AS stock_minimo,
        q.cantidad_maxima_almacenamiento_quimico AS stock_maximo,
        q.tiempo_entrega_minimo_quimico AS lead_time_min,
        q.tiempo_entrega_maximo_quimico AS lead_time_max
    FROM quimicos_hwi_quimicos AS q
    LEFT JOIN quimicos_hwi_umbs AS u ON q.id_umb_quimico = u.id_umb;
    """
    return pd.read_sql(query, conexion)