import sys
import os
import json
import datetime
import warnings

try:
    sys.stdout.reconfigure(encoding='utf-8')
except Exception:
    pass

warnings.filterwarnings('ignore')

# Asegurar path de analytics
current_dir = os.path.dirname(os.path.abspath(__file__))
analytics_dir = os.path.dirname(current_dir)
if analytics_dir not in sys.path:
    sys.path.insert(0, analytics_dir)

from data.consultas import cargar_datos_completos, cargar_datos_ingresos
from logic.calculos import calcular_proyeccion
from logic.chatbot import inicializar_groq, obtener_modelo_activo, generar_respuesta

def action_filtros():
    df_base = cargar_datos_completos()
    if df_base is None or df_base.empty:
        return {"success": False, "message": "No se encontraron datos de químicos"}
    
    quimicos = sorted([str(q).strip() for q in df_base['descripcion_quimico'].dropna().unique() if str(q).strip()])
    celulas = ["Todas"]
    if 'celula' in df_base.columns:
        c_list = sorted([str(c).strip() for c in df_base['celula'].dropna().unique() if str(c).strip()])
        celulas += c_list
        
    return {
        "success": True,
        "quimicos": quimicos,
        "celulas": celulas
    }

def action_proyeccion(quimico, celula, ver_anio_siguiente):
    df_base = cargar_datos_completos()
    df_ingresos = cargar_datos_ingresos()
    
    if df_base is None or df_base.empty:
        return {"success": False, "message": "Base de datos vacía"}
        
    quimico_clean = quimico.strip()
    df_base['descripcion_quimico_clean'] = df_base['descripcion_quimico'].astype(str).str.strip()
    df_q = df_base[df_base['descripcion_quimico_clean'] == quimico_clean].copy()
    if celula and celula != "Todas":
        df_q = df_q[df_q['celula'].astype(str).str.strip() == celula.strip()]
        
    if df_q.empty:
        return {
            "success": True,
            "sin_datos": True,
            "quimico": quimico,
            "celula": celula,
            "mensaje": f"No hay registros de consumo para {quimico} en la célula {celula}"
        }
        
    df_q_agg = df_q.groupby('fecha', as_index=False).agg({
        'consumo_kg': 'sum',
        'precio_quimico': 'last',
        'stock_actual': 'last',
        'stock_minimo': 'last',
        'stock_maximo': 'last',
        'lead_time_min': 'last',
        'lead_time_max': 'last',
        'umb': 'last'
    }).sort_values('fecha').reset_index(drop=True)
    
    umb_val = df_q_agg['umb'].dropna().iloc[0] if 'umb' in df_q_agg.columns and not df_q_agg['umb'].dropna().empty else 'u.'
    
    hoy = datetime.datetime.now()
    hoy_str = hoy.strftime('%Y-%m-%d')
    anio_actual = hoy.year
    
    datos = calcular_proyeccion(df_q_agg, quimico, ver_anio_siguiente, hoy_str, anio_actual)
    
    if datos is None:
        return {"success": True, "sin_datos": True, "quimico": quimico}
        
    # Ingresos de este químico
    df_i_q = df_ingresos[df_ingresos['descripcion_quimico'].astype(str).str.strip() == quimico_clean].copy() if not df_ingresos.empty else None
    ingresos = []
    if df_i_q is not None and not df_i_q.empty:
        df_i_q['fecha_str'] = df_i_q['fecha_ingreso'].astype(str)
        ingresos = df_i_q[['fecha_str', 'cantidad_ingreso']].rename(columns={'fecha_str': 'fecha', 'cantidad_ingreso': 'cantidad'}).to_dict(orient='records')
        
    df_consolidado = datos['df_consolidado'].copy()
    df_consolidado['fecha_str'] = df_consolidado['fecha'].dt.strftime('%Y-%m-%d')
    
    # Separar Histórico y Proyectado
    serie_historico = df_consolidado[df_consolidado['tipo'] == 'Histórico'][['fecha_str', 'consumo_kg', 'precio_quimico', 'gasto_total']].rename(columns={'fecha_str': 'fecha'}).to_dict(orient='records')
    serie_proyectado = df_consolidado[df_consolidado['tipo'] == 'Proyectado'][['fecha_str', 'consumo_kg', 'precio_quimico', 'gasto_total']].rename(columns={'fecha_str': 'fecha'}).to_dict(orient='records')
    
    return {
        "success": True,
        "sin_datos": False,
        "quimico": quimico,
        "celula": celula,
        "umb": umb_val,
        "kpis": {
            "precio": float(datos['precio']),
            "c_prom": round(float(datos['c_prom']), 2),
            "c_pred": round(float(datos['c_pred']), 2),
            "var": round(float(datos['var']), 1),
            "stock_act": round(float(datos['stock_act']), 2),
            "stock_min": round(float(datos['stock_min']), 2),
            "stock_max": round(float(datos['stock_max']), 2),
            "lt_min": int(datos['lt_min']),
            "lt_max": int(datos['lt_max']),
            "sug_pedir": round(float(datos['sug_pedir']), 2),
            "momento_reorden": str(datos['momento_reorden'])
        },
        "historico": serie_historico,
        "proyectado": serie_proyectado,
        "ingresos": ingresos
    }

def action_chat(prompt, quimico, celula, historial):
    client = None
    try:
        client = inicializar_groq()
    except Exception:
        pass

    proy = action_proyeccion(quimico, celula, False)
    
    if not proy.get('sin_datos') and 'kpis' in proy:
        k = proy['kpis']
        umb = proy['umb']
        stock_str = f"{k['stock_act']:,.2f} {umb} (Mínimo: {k['stock_min']}, Máximo: {k['stock_max']})"
        alerta_str = f"ALERTA COMPRA: {k['momento_reorden']} | CANTIDAD SUGERIDA A PEDIR: {k['sug_pedir']:,.2f} {umb}"
        tiempo_str = f"TIEMPO DE ENTREGA: {k['lt_min']} a {k['lt_max']} días hábiles"
        ingresos_str = json.dumps(proy.get('ingresos', [])[:5], ensure_ascii=False)
        hist_str = json.dumps(proy.get('historico', [])[-6:], ensure_ascii=False)
        proj_str = json.dumps(proy.get('proyectado', [])[:6], ensure_ascii=False)
        contexto = (
            f"PRODUCTO: {quimico} | CÉLULA: {celula} | STOCK DISPONIBLE: {stock_str}\n"
            f"{alerta_str}\n"
            f"{tiempo_str}\n"
            f"HISTÓRICO RECIENTE DE CONSUMO:\n{hist_str}\n"
            f"PROYECCIÓN DE DEMANDA:\n{proj_str}\n"
            f"INGRESOS RECIENTES A INVENTARIO:\n{ingresos_str}\n"
        )
    else:
        contexto = f"PRODUCTO: {quimico} | CÉLULA: {celula} | Sin datos detallados en esta célula."

    if client:
        try:
            modelo = obtener_modelo_activo(client)
            respuesta = generar_respuesta(client, modelo, historial, contexto)
            if respuesta:
                return {"success": True, "respuesta": respuesta}
        except Exception:
            pass

    # Fallback inteligente local basado en los datos reales calculados
    if not proy.get('sin_datos') and 'kpis' in proy:
        k = proy['kpis']
        umb = proy['umb']
        p_lower = prompt.lower()
        if any(w in p_lower for w in ["stock", "cuanto hay", "disponible", "cantidad"]):
            resp = f"Para **{quimico}**, actualmente contamos con **{k['stock_act']:,.1f} {umb}** disponibles en inventario (Tope mínimo: {k['stock_min']:,.0f} {umb}, Máximo: {k['stock_max']:,.0f} {umb})."
        elif any(w in p_lower for w in ["compra", "pedir", "reorden", "cuanto pido", "alerta"]):
            resp = f"El estado logístico indica: **{k['momento_reorden']}**. La cantidad sugerida a pedir es de **{k['sug_pedir']:,.1f} {umb}** y el tiempo de entrega del proveedor es de **{k['lt_min']} a {k['lt_max']} días hábiles**."
        elif any(w in p_lower for w in ["precio", "costo", "vale", "valor"]):
            resp = f"El precio unitario actual registrado de **{quimico}** es de **${k['precio']:,.0f} COP** por {umb}."
        elif any(w in p_lower for w in ["proyeccion", "proximo mes", "consumo", "pronostico"]):
            resp = f"El consumo promedio histórico es de **{k['c_prom']:,.1f} {umb}** y la proyección calculada para el próximo mes es de **{k['c_pred']:,.1f} {umb}** ({k['var']:+.1f}% vs promedio)."
        else:
            resp = (
                f"Resumen logístico de **{quimico}** ({celula}):\n\n"
                f"• **Stock Disponible:** {k['stock_act']:,.1f} {umb}\n"
                f"• **Consumo Estimado Próx. Mes:** {k['c_pred']:,.1f} {umb}\n"
                f"• **Estado de Reorden:** {k['momento_reorden']}\n"
                f"• **Tiempo de Entrega:** {k['lt_min']} a {k['lt_max']} días hábiles\n\n"
                f"¿Deseas conocer más detalles sobre compras, proyecciones o precios?"
            )
        return {"success": True, "respuesta": resp}
    else:
        return {
            "success": True, 
            "respuesta": f"No se registran consumos para **{quimico}** en la célula **{celula}**."
        }

if __name__ == '__main__':
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "message": "Acción requerida"}))
        sys.exit(0)
        
    action = sys.argv[1]
    
    if action == 'filtros':
        res = action_filtros()
    elif action == 'proyeccion':
        quimico = sys.argv[2] if len(sys.argv) > 2 else ""
        celula = sys.argv[3] if len(sys.argv) > 3 else "Todas"
        ver_siguiente = sys.argv[4].lower() in ['1', 'true', 'yes'] if len(sys.argv) > 4 else False
        res = action_proyeccion(quimico, celula, ver_siguiente)
    elif action == 'chat':
        if len(sys.argv) > 2:
            raw = sys.argv[2]
            payload = {}
            try:
                import base64
                decoded = base64.b64decode(raw).decode('utf-8')
                payload = json.loads(decoded)
            except Exception:
                try:
                    payload = json.loads(raw)
                except Exception as e:
                    payload = {}
            prompt = payload.get('prompt', '')
            quimico = payload.get('quimico', '')
            celula = payload.get('celula', 'Todas')
            historial = payload.get('historial', [])
            res = action_chat(prompt, quimico, celula, historial)
        else:
            res = {"success": False, "message": "Payload requerido para chat"}
    else:
        res = {"success": False, "message": f"Acción desconocida: {action}"}
        
    print(json.dumps(res, ensure_ascii=False))
