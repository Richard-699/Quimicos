import pandas as pd
import numpy as np
import streamlit as st
from config.database import obtener_conexion

# Importar los repositorios
from data.repository.consumosRepository import obtener_consumos_mensuales
from data.repository.preciosRepository import obtener_precios_mensuales
from data.repository.quimicosRepository import obtener_maestro_quimicos
from data.repository.ingresosRepository import obtener_ingresos_inventario

@st.cache_data(ttl=600)
def cargar_datos_ingresos():
    conexion = obtener_conexion()
    if conexion is None:
        return pd.DataFrame({'descripcion_quimico': [], 'fecha_ingreso': [], 'cantidad_ingreso': []})
    try:
        df = obtener_ingresos_inventario(conexion)
        conexion.close()
        return df
    except Exception as e:
        st.error(f"❌ Error obteniendo ingresos: {e}")
        return pd.DataFrame({'descripcion_quimico': [], 'fecha_ingreso': [], 'cantidad_ingreso': []})

@st.cache_data(ttl=600)
def cargar_datos_completos():
    """Llama a los repositorios, une la data y prepara el DataFrame final."""
    conexion = obtener_conexion()
    
    if conexion is None:
        return _generar_datos_fallback()

    try:
        # 1. Extraer DataFrames desde los repositorios (Capa de Acceso a Datos)
        df_consumos = obtener_consumos_mensuales(conexion)
        df_precios = obtener_precios_mensuales(conexion)
        df_maestro = obtener_maestro_quimicos(conexion)
        
        conexion.close()
        
        # 2. Procesamiento y Limpieza (Capa Lógica)
        df_consumos['fecha'] = pd.to_datetime(df_consumos['fecha'])
        df_precios['fecha'] = pd.to_datetime(df_precios['fecha'])
        
        # Merge de históricos
        df_final = pd.merge(df_consumos, df_precios, on=['descripcion_quimico', 'fecha'], how='outer')
        # Merge con datos maestros de inventario
        df_final = pd.merge(df_final, df_maestro, on='descripcion_quimico', how='left')
        
        df_final = df_final.sort_values(by=['descripcion_quimico', 'fecha'])
        
        # Limpieza de Nulos (NaN)
        columnas_cero = ['consumo_kg', 'stock_actual', 'stock_minimo', 'stock_maximo', 'lead_time_min', 'lead_time_max']
        for col in columnas_cero:
            df_final[col] = df_final[col].fillna(0)
            
        # Rellenar precios hacia adelante y hacia atrás para meses sin registro
        df_final['precio_quimico'] = df_final.groupby('descripcion_quimico')['precio_quimico'].ffill().bfill()
        
        return df_final.dropna(subset=['descripcion_quimico'])
        
    except Exception as e:
        st.error(f"❌ Error ejecutando repositorios: {e}")
        return _generar_datos_fallback()

def _generar_datos_fallback():
    """Genera datos de prueba en caso de caída de la BD para no romper la interfaz."""
    fechas = pd.date_range(start="2026-01-01", end="2026-08-01", freq="MS")
    return pd.DataFrame({
        "fecha": np.tile(fechas, 2),
        "descripcion_quimico": ["ACEITE DE RICINO"] * len(fechas) + ["ACIDO SULFURICO"] * len(fechas),
        "precio_quimico": 42900.0,
        "consumo_kg": np.random.uniform(4, 12, size=len(fechas)*2),
        "stock_actual": 7.0,
        "stock_minimo": 2.0,
        "stock_maximo": 20.0,
        "lead_time_min": 3,
        "lead_time_max": 8
    })