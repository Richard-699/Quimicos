import pandas as pd
import numpy as np
import datetime

MESES_ESPANOL = {
    'January': 'enero', 'February': 'febrero', 'March': 'marzo',
    'April': 'abril', 'May': 'mayo', 'June': 'junio',
    'July': 'julio', 'August': 'agosto', 'September': 'septiembre',
    'October': 'octubre', 'November': 'noviembre', 'December': 'diciembre'
}

def generar_pronostico_demanda(df_hist, rango_proyeccion):
    """
    Motor de Pronóstico Logístico: Suavizado Exponencial (EMA) con 
    Amortiguación de Tendencia y Retorno a la Media (Mean Reversion).
    Evita sobreestimaciones por picos temporales.
    """
    df_valid = df_hist[df_hist['consumo_kg'] >= 0]['consumo_kg'].values
    n_puntos = len(df_valid)
    
    if n_puntos == 0:
        return [0.1] * len(rango_proyeccion)
    elif n_puntos == 1:
        return [round(float(df_valid[0]), 2)] * len(rango_proyeccion)

    # 1. Parámetros Estadísticos Base
    media_historica = float(np.mean(df_valid))
    pico_maximo = float(np.max(df_valid))
    ultimo_valor = float(df_valid[-1])

    # 2. Suavizado Exponencial para estimar el Nivel Actual (Alpha = 0.4)
    alpha = 0.4
    nivel = df_valid[0]
    tendencia = 0.0

    for i in range(1, n_puntos):
        prev_nivel = nivel
        nivel = alpha * df_valid[i] + (1 - alpha) * (nivel + tendencia)
        tendencia = 0.2 * (nivel - prev_nivel) + 0.8 * tendencia

    # 3. Proyección con Retorno Progresivo a la Media (Mean Reversion)
    preds_finales = []
    nivel_proyectado = nivel
    
    # Factor de atracción hacia el promedio real del año
    factor_retorno_media = 0.15 
    
    for h in range(len(rango_proyeccion)):
        # La tendencia se amortigua rápidamente (phi = 0.5)
        tendencia *= 0.5 
        
        # El nivel futuro se atrae paulatinamente hacia la media histórica
        nivel_proyectado = (1 - factor_retorno_media) * (nivel_proyectado + tendencia) + (factor_retorno_media * media_historica)
        
        # Cota de seguridad: no superar el pico histórico máximo + 10%
        cota_superior = max(pico_maximo * 1.1, media_historica * 1.3)
        val_final = min(max(0.0, nivel_proyectado), cota_superior)
        
        preds_finales.append(round(val_final, 2))

    return preds_finales


def calcular_proyeccion(df_q, quimico_nombre, ver_anio_siguiente, hoy, anio_actual):
    """
    Procesa datos históricos y genera proyecciones equilibradas de demanda para HWI.
    """
    if df_q.empty:
        return None

    df_q = df_q.copy()
    df_q['fecha'] = pd.to_datetime(df_q['fecha'])

    # 1. DETERMINAR MESES CERRADOS (HISTÓRICO REAL)
    primer_dia_mes_actual = hoy.replace(day=1, hour=0, minute=0, second=0, microsecond=0)
    ultimo_mes_cerrado = primer_dia_mes_actual - pd.DateOffset(months=1)
    fecha_min_historica = df_q['fecha'].min()

    if pd.isna(fecha_min_historica) or fecha_min_historica > primer_dia_mes_actual:
        fecha_min_historica = ultimo_mes_cerrado

    # Rango histórico va hasta el mes actual (inclusive) para visualizar lo que va del mes
    rango_historico = pd.date_range(start=fecha_min_historica, end=primer_dia_mes_actual, freq='MS')
    df_hist_completo = pd.DataFrame({'fecha': rango_historico})

    df_hist = pd.merge(df_hist_completo, df_q, on='fecha', how='left')
    df_hist['consumo_kg'] = df_hist['consumo_kg'].fillna(0)
    df_hist['descripcion_quimico'] = quimico_nombre

    precio_val = df_q['precio_quimico'].dropna().iloc[-1] if 'precio_quimico' in df_q and not df_q['precio_quimico'].dropna().empty else 0.0
    df_hist['precio_quimico'] = df_hist['precio_quimico'].fillna(precio_val)
    df_hist['gasto_total'] = df_hist['consumo_kg'] * df_hist['precio_quimico']
    df_hist['tipo'] = 'Histórico'

    # 2. GENERAR PROYECCIÓN EQUILIBRADA (Solo con meses cerrados)
    df_hist_training = df_hist[df_hist['fecha'] <= ultimo_mes_cerrado]
    
    fin_proyeccion = pd.to_datetime(f"{anio_actual + 1}-12-01") if ver_anio_siguiente else pd.to_datetime(f"{anio_actual}-12-01")
    rango_proyeccion = pd.date_range(start=primer_dia_mes_actual, end=fin_proyeccion, freq='MS')

    valores_proyectados = generar_pronostico_demanda(df_hist_training, rango_proyeccion)

    df_proj = pd.DataFrame({
        'fecha': rango_proyeccion,
        'consumo_kg': valores_proyectados
    })
    df_proj['descripcion_quimico'] = quimico_nombre
    df_proj['precio_quimico'] = precio_val
    df_proj['gasto_total'] = df_proj['consumo_kg'] * df_proj['precio_quimico']
    df_proj['tipo'] = 'Proyectado'

    df_consolidado = pd.concat([df_hist, df_proj], ignore_index=True)
    df_consolidado = df_consolidado.drop_duplicates(subset=['fecha', 'tipo']).reset_index(drop=True)

    # 3. MÉTRICAS LOGÍSTICAS Y KPI
    consumos_validos = df_hist[df_hist['consumo_kg'] > 0]['consumo_kg']
    c_prom = float(consumos_validos.mean()) if not consumos_validos.empty else float(df_hist['consumo_kg'].mean())
    if np.isnan(c_prom): c_prom = 0.0

    c_pred = float(valores_proyectados[0]) if valores_proyectados else c_prom

    stock_act = float(df_q['stock_actual'].iloc[-1]) if 'stock_actual' in df_q and not df_q['stock_actual'].empty else 0.0
    stock_min = float(df_q['stock_minimo'].iloc[-1]) if 'stock_minimo' in df_q and not df_q['stock_minimo'].empty else 0.0
    stock_max = float(df_q['stock_maximo'].iloc[-1]) if 'stock_maximo' in df_q and not df_q['stock_maximo'].empty else 0.0
    lt_min = int(df_q['lead_time_min'].iloc[-1]) if 'lead_time_min' in df_q and not df_q['lead_time_min'].empty else 0
    lt_max = int(df_q['lead_time_max'].iloc[-1]) if 'lead_time_max' in df_q and not df_q['lead_time_max'].empty else 0

    var_pct = ((c_pred - c_prom) / c_prom * 100) if c_prom > 0 else 0.0
    sug_pedir = max(0.0, stock_max - stock_act) if stock_max > 0 else max(0.0, (c_pred * 2) - stock_act)

    if stock_act <= stock_min:
        momento_reorden = "⚠️ Stock crítico - Realizar pedido de inmediato"
    else:
        meses_cobertura = (stock_act / c_pred) if c_pred > 0 else 12
        fecha_reorden = hoy + pd.DateOffset(months=max(1, int(meses_cobertura)))
        nombre_mes = MESES_ESPANOL.get(fecha_reorden.strftime('%B'), fecha_reorden.strftime('%B'))
        momento_reorden = f"Comprar antes de {nombre_mes} de {fecha_reorden.year}"

    return {
        'precio': precio_val,
        'c_prom': c_prom,
        'c_pred': c_pred,
        'var': var_pct,
        'stock_act': stock_act,
        'stock_min': stock_min,
        'stock_max': stock_max,
        'lt_min': lt_min,
        'lt_max': lt_max,
        'sug_pedir': sug_pedir,
        'momento_reorden': momento_reorden,
        'df_consolidado': df_consolidado
    }