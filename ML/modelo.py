import mysql.connector
import pandas as pd
import numpy as np
import json
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split
from sklearn.metrics import r2_score
from rapidfuzz import process
from groq import Groq
from datetime import datetime

import os
from groq import Groq

client = Groq(api_key=os.getenv("GROQ_API_KEY"))

ultimo_quimico_consultado = None
historial_chat = []
memoria_predicciones = {}
df_global = None

def obtener_datos():
    try:
        conexion = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="quimicos_hwi2"
        )
        
        query = """
        SELECT lp.id_quimico_log_precio, lp.fecha_log_precio, lp.precio_quimico, q.descripcion_quimico 
        FROM `quimicos_hwi_logs_precios` AS lp
        INNER JOIN quimicos_hwi_quimicos AS q ON lp.id_quimico_log_precio = q.id_quimico
        ORDER BY q.descripcion_quimico ASC, lp.fecha_log_precio ASC;
        """
        
        df = pd.read_sql(query, conexion)
        conexion.close()
        return df
    except mysql.connector.Error as err:
        print(f"❌ Error de conexión a la base de datos: {err}")
        return None

def atender_asistente(pregunta):
    global ultimo_quimico_consultado, historial_chat, memoria_predicciones, df_global
    
    lista_quimicos = list(memoria_predicciones.keys())
    pregunta_clean = pregunta.lower().strip()
    
    coincidencias = process.extract(pregunta, lista_quimicos, limit=5)
    coincidencias_validas = [c for c in coincidencias if c[1] >= 60]
    
    quimico_encontrado = None
    
    if not coincidencias_validas or coincidencias_validas[0][1] < 80:
        palabras = [p for p in pregunta_clean.split() if len(p) > 3]
        sub_coincidencias = []
        
        for q_real in lista_quimicos:
            for pal in palabras:
                if pal in q_real.lower() and q_real not in sub_coincidencias:
                    sub_coincidencias.append(q_real)
        
        if len(sub_coincidencias) > 1:
            opciones_texto = "\n".join([f"  • {q}" for q in sub_coincidencias[:7]])
            return (
                f"\n🤖 Encontré varias opciones en el catálogo que contienen esa palabra. "
                f"¿A cuál de estos químicos te refieres?\n\n{opciones_texto}\n\n"
                f"Por favor, escribe el nombre completo o cópialo para darte el precio exacto."
            )
        elif len(sub_coincidencias) == 1:
            quimico_encontrado = sub_coincidencias[0]

    if not quimico_encontrado:
        if coincidencias_validas and coincidencias_validas[0][1] >= 80:
            quimico_encontrado = coincidencias_validas[0][0]
        elif len(coincidencias_validas) > 1:
            opciones_texto = "\n".join([f"  • {c[0]}" for c in coincidencias_validas])
            return (
                f"\n🤖 Encontré varias opciones que coinciden con tu búsqueda. "
                f"¿A cuál de estos químicos te refieres?\n\n{opciones_texto}\n\n"
                f"Por favor, escribe el nombre completo o cópialo para darte el precio exacto."
            )
        elif len(coincidencias_validas) == 1:
            quimico_encontrado = coincidencias_validas[0][0]
        else:
            if ultimo_quimico_consultado is not None:
                quimico_encontrado = ultimo_quimico_consultado
            else:
                return "\n🤖 No logré identificar ningún químico en tu consulta. Por favor, escribe el nombre del producto que buscas."

    if quimico_encontrado != ultimo_quimico_consultado:
        ultimo_quimico_consultado = quimico_encontrado
        historial_chat = []

    datos_predictivos = memoria_predicciones[quimico_encontrado]
    df_historico = df_global[df_global['descripcion_quimico'] == quimico_encontrado].copy()
    promedio_historico_total = df_historico['precio_quimico'].mean()
    
    historial_pasado_texto = ""
    for _, fila in df_historico.iterrows():
        fecha_formateada = fila['fecha_log_precio'].strftime('%Y-%m-%d')
        historial_pasado_texto += f"  - En fecha {fecha_formateada}: ${fila['precio_quimico']:,.2f}\n"

    contexto_datos = (
        f"PRODUCTO QUÍMICO ACTIVO: {quimico_encontrado}\n\n"
        f"--- HISTORIAL REAL PASADO (Datos en BD) ---\n"
        f"Promedio general registrado: ${promedio_historico_total:,.2f}\n"
        f"Registros históricos detallados:\n{historial_pasado_texto}\n"
        f"--- PREDICCIONES FUTURAS (Resto del año 2026) ---\n"
        f"Precisión del modelo (Random Forest): {datos_predictivos['precision']:.2f}%\n"
        f"Mejor mes futuro para comprar: {datos_predictivos['mes_ideal']} (Precio estimado más bajo: ${datos_predictivos['precio_ideal']:,.2f})\n"
        f"Historial de precios proyectados:\n"
        f"{json.dumps(datos_predictivos['historial_futuro'], indent=2, ensure_ascii=False)}"
    )
    
    prompt_sistema = (
        "Eres un asistente virtual experto en análisis de compras para la empresa de químicos HWI. "
        "Responde a las dudas usando los datos numéricos provistos en el CONTEXTO DE DATOS. "
        "Estás en una conversación fluida; el usuario puede hacer preguntas de seguimiento sobre el mismo químico sin repetir su nombre. "
        "Responde de forma cortés, natural, humana y muy directa. No inventes datos que no estén explícitamente listados."
    )
    
    mensajes_api = [{"role": "system", "content": prompt_sistema}]
    for mensaje in historial_chat:
        mensajes_api.append(mensaje)
        
    mensajes_api.append({
        "role": "user", 
        "content": f"CONTEXTO DE DATOS DEL QUÍMICO ACTIVO:\n{contexto_datos}\n\nPREGUNTA ACTUAL DE SEGUIMIENTO: {pregunta}"
    })
    
    try:
        completion = client.chat.completions.create(
            model="llama-3.3-70b-versatile",
            messages=mensajes_api,
            temperature=0.3
        )
        
        respuesta_llm = completion.choices[0].message.content
        historial_chat.append({"role": "user", "content": pregunta})
        historial_chat.append({"role": "assistant", "content": respuesta_llm})
        
        return f"\n🤖 {respuesta_llm}"
    except Exception as e:
        return f"\n❌ Error al consultar el LLM de Groq: {e}"

df_global = obtener_datos()

if df_global is not None and not df_global.empty:
    df_global['fecha_log_precio'] = pd.to_datetime(df_global['fecha_log_precio'])
    df_global['anio'] = df_global['fecha_log_precio'].dt.year
    df_global['mes'] = df_global['fecha_log_precio'].dt.month

    quimicos = df_global['id_quimico_log_precio'].unique()

    print(f"🤖 Entrenando y evaluando modelos para {len(quimicos)} químicos...")
    print("-" * 75)

    fecha_hoy = datetime.now()
    anio_actual = fecha_hoy.year
    mes_actual = fecha_hoy.month
    nombres_meses = {
        1: "Enero", 2: "Febrero", 3: "Marzo", 4: "Abril", 5: "Mayo", 6: "Junio",
        7: "Julio", 8: "Agosto", 9: "Septiembre", 10: "Octubre", 11: "Noviembre", 12: "Diciembre"
    }

    for id_q in quimicos:
        df_q = df_global[df_global['id_quimico_log_precio'] == id_q]
        nombre_quimico = df_q['descripcion_quimico'].iloc[0]
        
        if len(df_q) < 5:
            continue
        
        X = df_q[['anio', 'mes']]
        y = df_q['precio_quimico']
        
        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
        
        modelo = RandomForestRegressor(n_estimators=100, random_state=42)
        modelo.fit(X_train, y_train)
        
        y_pred_test = modelo.predict(X_test)
        score_r2 = r2_score(y_test, y_pred_test)
        mape = np.mean(np.abs((y_test - y_pred_test) / y_test)) * 100
        precision_porcentaje = max(0, 100 - mape)
        
        meses_futuros = list(range(mes_actual + 1, 13))
        X_futuro = pd.DataFrame({
            'anio': [anio_actual] * len(meses_futuros),
            'mes': meses_futuros
        })
        
        predicciones = modelo.predict(X_futuro)
        
        precio_minimo = min(predicciones)
        idx_minimo = list(predicciones).index(precio_minimo)
        mes_ideal = nombres_meses[meses_futuros[idx_minimo]]
        
        memoria_predicciones[nombre_quimico] = {
            "mes_ideal": mes_ideal,
            "precio_ideal": precio_minimo,
            "precision": precision_porcentaje,
            "r2": score_r2,
            "historial_futuro": {nombres_meses[m]: p for m, p in zip(meses_futuros, predicciones)}
        }

    print("✅ Modelos entrenados con éxito. Asistente conversacional listo.")
    print("-" * 75)

    print("\n🤖 Asistente Inteligente con Memoria de Historial Activa")
    print("Hazme preguntas sobre los precios pasados, promedios o futuros de tus químicos...")
    print("(Escribe 'salir' para terminar)\n")

    while True:
        entrada_usuario = input("Tú: ")
        if entrada_usuario.lower() == 'salir':
            print("🤖 ¡Hasta luego!")
            break
        
        if entrada_usuario.strip() == "":
            continue
            
        resultado = atender_asistente(entrada_usuario)
        print(resultado)
        print("-" * 75)

else:
    print("❌ No se pudieron cargar los datos.")