import pandas as pd
import datetime
import plotly.express as px
import streamlit as st
import streamlit.components.v1 as components

# Módulos del Proyecto
from data.consultas import cargar_datos_completos, cargar_datos_ingresos
from ui.estilos import aplicar_estilos_corporativos
from logic.calculos import calcular_proyeccion
from logic.chatbot import inicializar_groq, obtener_modelo_activo, generar_respuesta

def formato_cop(valor):
    if pd.isna(valor): return "$0"
    s = f"${float(valor):,.0f}"
    partes = s.split(',')
    if len(partes) >= 3:
        return partes[0] + "'" + ".".join(partes[1:])
    else:
        return s.replace(',', '.')

# ---------------------------------------------------------
# 1. INICIALIZACIÓN Y ANCLAJE DE SCRIPTS JS
# ---------------------------------------------------------
st.set_page_config(page_title="HWI - Proyecciones", layout="wide")
aplicar_estilos_corporativos()

js_holder = st.empty()

def ejecutar_js_carga(accion="mostrar"):
    """
    Ejecuta el spinner PHP dentro del contenedor superior anclado.
    """
    fn = "mostrarCarga" if accion == "mostrar" else "ocultarCarga"
    js_code = f"""
    <script>
        (function() {{
            const fnName = "{fn}";
            try {{
                window.parent.postMessage({{ action: fnName }}, '*');
                window.top.postMessage({{ action: fnName }}, '*');
            }} catch(e) {{}}

            try {{
                if (window.parent && typeof window.parent[fnName] === 'function') {{
                    window.parent[fnName]();
                }} else if (window.top && typeof window.top[fnName] === 'function') {{
                    window.top[fnName]();
                }}
            }} catch(e) {{}}
        }})();
    </script>
    """
    with js_holder:
        components.html(js_code, height=0, width=0)

def notificar_cambio():
    """Callback para activar el spinner PHP solo en cambios de filtros."""
    ejecutar_js_carga("mostrar")

if "app_inicializada" not in st.session_state:
    ejecutar_js_carga("mostrar")
    st.session_state.app_inicializada = True

hoy = datetime.datetime.now()
anio_actual = hoy.year
anio_siguiente = anio_actual + 1

client = inicializar_groq()
MODELO_AUTO = obtener_modelo_activo(client)

# ---------------------------------------------------------
# 2. ENCABEZADO PRINCIPAL DE PÁGINA
# ---------------------------------------------------------
st.markdown('''
    <div class="hwi-header-banner">
        <div class="hwi-title">
            <i class="fa-solid fa-chart-line"></i>
            <span>Proyecciones de Consumo y Precios</span>
        </div>
    </div>
    <div style="height: 25px; width: 100%; clear: both;"></div>
''', unsafe_allow_html=True)

df_base = cargar_datos_completos()
df_ingresos = cargar_datos_ingresos()
lista_quimicos = sorted(df_base['descripcion_quimico'].unique().tolist())
lista_celulas = ["Todas"] + sorted([str(x) for x in df_base['celula'].dropna().unique()]) if 'celula' in df_base.columns else ["Todas"]

if not lista_quimicos:
    ejecutar_js_carga("ocultar")
    st.error("No se encontraron productos registrados.")
    st.stop()

# ---------------------------------------------------------
# 3. ESTRUCTURA DUAL (DASHBOARD A LA IZQ / CHAT A LA DER)
# ---------------------------------------------------------
col_dash, col_chat = st.columns([2.1, 1.25], gap="medium")

# --- COLUMNA 1: FILTROS + DASHBOARD ---
with col_dash:
    f_col1, f_col2, f_col3 = st.columns([1.5, 1.2, 1.3], gap="small")
    
    with f_col1:
        quimico_seleccionado = st.selectbox(
            "Seleccionar Químico:", 
            lista_quimicos,
            on_change=notificar_cambio
        )

    with f_col2:
        celula_seleccionada = st.selectbox(
            "Célula:", 
            lista_celulas,
            on_change=notificar_cambio
        )

    with f_col3:
        st.write(" ")
        st.checkbox(
            f"Proyección {anio_siguiente}", 
            key="ver_anio_siguiente",
            on_change=notificar_cambio
        )

    # Ingresos de Inventario
    df_i_q = df_ingresos[df_ingresos['descripcion_quimico'] == quimico_seleccionado].copy()

    # Procesamiento Logístico con ML
    df_q = df_base[df_base['descripcion_quimico'] == quimico_seleccionado].copy()
    if celula_seleccionada != "Todas":
        df_q = df_q[df_q['celula'].astype(str) == celula_seleccionada]
        
    df_q = df_q.groupby('fecha', as_index=False).agg({
        'consumo_kg': 'sum',
        'precio_quimico': 'last',
        'stock_actual': 'last',
        'stock_minimo': 'last',
        'stock_maximo': 'last',
        'lead_time_min': 'last',
        'lead_time_max': 'last',
        'umb': 'last'
    }).sort_values('fecha').reset_index(drop=True)
    
    umb_val = df_q['umb'].dropna().iloc[0] if 'umb' in df_q.columns and not df_q['umb'].dropna().empty else 'kg'
    
    datos = calcular_proyeccion(df_q, quimico_seleccionado, st.session_state.ver_anio_siguiente, hoy, anio_actual)

    dash_container = st.container(height=480, border=False)
    
    with dash_container:
        st.markdown(f"<h3 style='margin-top: 4px; margin-bottom: 16px; font-weight: 700; color: #0f172a;'>{quimico_seleccionado}</h3>", unsafe_allow_html=True)
        
        # Tarjetas KPI con espaciado vertical limpio
        if datos is None:
            st.warning(f"No hay registros de consumo para el químico **{quimico_seleccionado}** en la célula **{celula_seleccionada}**.")
        else:
            st.markdown("<style>div[data-testid='stMetric'] { margin-bottom: 14px !important; }</style>", unsafe_allow_html=True)
            
            row1_col1, row1_col2 = st.columns(2)
            row1_col1.metric("Precio Actual", f"{formato_cop(datos['precio'])} /{umb_val}")
            row1_col2.metric("Prom. Histórico", f"{datos['c_prom']:,.1f} {umb_val}")
            
            row2_col1, row2_col2 = st.columns(2)
            row2_col1.metric("Est. Próx. Mes", f"{datos['c_pred']:,.1f} {umb_val}", delta=f"{datos['var']:+.1f}% vs Prom")
            row2_col2.metric("Stock Disponible", f"{datos['stock_act']:,.1f} {umb_val}", help=f"Límites: Mín {datos['stock_min']:,.0f} {umb_val} | Máx {datos['stock_max']:,.0f} {umb_val}")

            # Insignia Logística de Alerta
            st.markdown(f'''
                <div class="hwi-status-badge" style="margin-top: 10px; margin-bottom: 16px;">
                    <span><i class="fa-solid fa-truck-ramp-box" style="color: #00a8cc;"></i> <b>Lead Time:</b> {datos['lt_min']} a {datos['lt_max']} días</span>
                    <span>|</span>
                    <span><i class="fa-solid fa-circle-exclamation" style="color: #eab308;"></i> <b>Estado:</b> {datos['momento_reorden']}</span>
                </div>
            ''', unsafe_allow_html=True)

            # Gráfico 1: Consumo
            fig_c = px.line(
                datos['df_consolidado'], x='fecha', y='consumo_kg', color='tipo',
                title=f"<b>Tendencia de Consumo ({umb_val}) - Histórico vs Pronóstico ML</b>",
                color_discrete_map={'Histórico': '#0284c7', 'Proyectado': '#f97316'}, 
                markers=True
            )
            fig_c.update_layout(
                template='plotly_white', paper_bgcolor='rgba(0,0,0,0)', plot_bgcolor='rgba(0,0,0,0)', 
                font=dict(color='#334155'), height=240, margin=dict(l=10, r=10, t=35, b=10)
            )
            fig_c.update_xaxes(dtick="M1", tickformat="%b %Y", tickangle=-45, gridcolor='#f1f5f9')
            fig_c.update_yaxes(gridcolor='#e2e8f0')
            st.plotly_chart(fig_c, width="stretch")

            # Gráfico 2: Gasto Total
            datos['df_consolidado']['gasto_fmt'] = datos['df_consolidado']['gasto_total'].apply(formato_cop)
            fig_g = px.bar(
                datos['df_consolidado'], x='fecha', y='gasto_total', color='tipo',
                title="<b>Proyección de Gasto Total ($) [Consumo × Precio]</b>",
                color_discrete_map={'Histórico': '#10b981', 'Proyectado': '#ef4444'},
                custom_data=['gasto_fmt']
            )
            fig_g.update_traces(hovertemplate='<b>%{x|%b %Y}</b><br>Gasto: %{customdata[0]}<br>Tipo: %{fullData.name}<extra></extra>')
            fig_g.update_layout(
                template='plotly_white', paper_bgcolor='rgba(0,0,0,0)', plot_bgcolor='rgba(0,0,0,0)', 
                font=dict(color='#334155'), height=240, margin=dict(l=10, r=10, t=35, b=10)
            )
            fig_g.update_xaxes(dtick="M1", tickformat="%b %Y", tickangle=-45, gridcolor='#f1f5f9')
            fig_g.update_yaxes(gridcolor='#e2e8f0')
            st.plotly_chart(fig_g, width="stretch")
            
            with st.expander(f"Ingresos a Inventario de {quimico_seleccionado}"):
                if not df_i_q.empty:
                    st.dataframe(
                        df_i_q[['fecha_ingreso', 'cantidad_ingreso']].rename(columns={'fecha_ingreso': 'Fecha', 'cantidad_ingreso': f'Cantidad ({umb_val})'}), 
                        use_container_width=True, hide_index=True
                    )
                else:
                    st.info("No hay registros de ingreso a inventario para este producto.")


# --- COLUMNA 2: CHATBOT AI ---
with col_chat:
    st.markdown('''
        <div class="chat-header">
            <span><i class="fa-solid fa-robot" style="color: #00a8cc;"></i> Asistente de Análisis</span>
            <span style="font-size: 0.75rem; color: #10b981;"><i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i> En línea</span>
        </div>
    ''', unsafe_allow_html=True)
    
    nombre_limpio = quimico_seleccionado.strip()
    
    if "messages" not in st.session_state or st.session_state.get("last_chemical") != quimico_seleccionado:
        st.session_state.last_chemical = quimico_seleccionado
        if datos is None:
            saludo_inicial = (
                f"¡Hola! 👋 Soy tu asistente de inventario HWI.\n\n"
                f"No tengo datos de inventario para **{nombre_limpio}** en la célula **{celula_seleccionada}**, pero puedo ayudarte a resolver otras dudas."
            )
        else:
            saludo_inicial = (
                f"¡Hola! 👋 Soy tu asistente de inventario HWI.\n\n"
                f"Actualmente contamos con **{datos['stock_act']:,.1f} {umb_val}** disponibles de **{nombre_limpio}**.\n\n"
                f"¿En qué puedo ayudarte?"
            )
        st.session_state.messages = [{"role": "assistant", "content": saludo_inicial}]

    chat_container = st.container(height=380, border=True)
    
    with chat_container:
        # 1. Renderizado de mensajes pasados
        for msg in st.session_state.messages:
            st.chat_message(msg["role"], avatar="🤖" if msg["role"] == "assistant" else "👤").markdown(msg["content"])

        # 2. Procesamiento fluido de preguntas pendientes con Fallback garantizado
        if "pending_prompt" in st.session_state:
            prompt_actual = st.session_state.pop("pending_prompt")
            st.session_state.messages.append({"role": "user", "content": prompt_actual})
            
            st.chat_message("user", avatar="👤").markdown(prompt_actual)
            
            with st.chat_message("assistant", avatar="🤖"):
                with st.spinner("Escribiendo..."):
                    if datos is not None:
                        df_tabla_limpia = datos['df_consolidado'].drop_duplicates(subset=['fecha'], keep='last')
                        tabla_str = df_tabla_limpia[['fecha', 'consumo_kg', 'precio_quimico', 'gasto_total', 'tipo']].to_string(index=False)
                        stock_str = f"{datos['stock_act']:,.2f} {umb_val} (Mínimo: {datos['stock_min']}, Máximo: {datos['stock_max']})"
                        alerta_str = f"ALERTA COMPRA: {datos['momento_reorden']} | CANTIDAD SUGERIDA A PEDIR: {datos['sug_pedir']:,.2f} {umb_val}"
                        tiempo_str = f"TIEMPO DE ENTREGA: {datos['lt_min']} a {datos['lt_max']} días hábiles"
                    else:
                        tabla_str = "Sin datos de tabla para mostrar."
                        stock_str = "No hay datos de stock para la célula seleccionada."
                        alerta_str = "No hay alertas de compra."
                        tiempo_str = "No hay datos de tiempo de entrega."

                    if not df_i_q.empty:
                        df_i_q['fecha_ingreso'] = pd.to_datetime(df_i_q['fecha_ingreso']).dt.strftime('%Y-%m-%d')
                        texto_ingresos = df_i_q[['fecha_ingreso', 'cantidad_ingreso']].to_string(index=False)
                    else:
                        texto_ingresos = "Sin ingresos registrados."
                    
                    contexto = (
                        f"PRODUCTO: {nombre_limpio} | CÉLULA FILTRADA: {celula_seleccionada} | STOCK DISPONIBLE: {stock_str}\n"
                        f"{alerta_str}\n"
                        f"{tiempo_str}\n"
                        f"HISTÓRICO DE INGRESOS A INVENTARIO:\n{texto_ingresos}\n\n"
                        f"TABLA RESUMEN EN PANTALLA:\n{tabla_str}"
                    )
                    
                    # Intento 1 con el modelo activo
                    respuesta = generar_respuesta(client, MODELO_AUTO, st.session_state.messages, contexto)
                    
                    # Intento 2 con modelo de respaldo si el primero falla o devuelve None
                    if not respuesta:
                        modelo_fallback = "llama3-70b-8192" if MODELO_AUTO != "llama3-70b-8192" else "mixtral-8x7b-32768"
                        respuesta = generar_respuesta(client, modelo_fallback, st.session_state.messages, contexto)
                    
                    # Mensaje final garantizado
                    if respuesta:
                        st.markdown(respuesta)
                        st.session_state.messages.append({"role": "assistant", "content": respuesta})
                    else:
                        fallback_msg = "Disculpa, hubo una interrupción momentánea en la conexión. Por favor, intenta formular tu pregunta de nuevo."
                        st.markdown(fallback_msg)
                        st.session_state.messages.append({"role": "assistant", "content": fallback_msg})
            
            st.rerun()

    # 3. Captura del chat
    if prompt := st.chat_input("Pregunta sobre compras, stock o proyecciones..."):
        st.session_state.pending_prompt = prompt
        
        if any(p in prompt.lower() for p in [str(anio_siguiente), "siguiente", "próximo", "proximo", "2027"]) and not st.session_state.ver_anio_siguiente:
            st.session_state.ver_anio_siguiente = True
            ejecutar_js_carga("mostrar")

        st.rerun()

# ---------------------------------------------------------
# 4. FINALIZACIÓN Y OCULTAMIENTO DEL SPINNER PHP
# ---------------------------------------------------------
ejecutar_js_carga("ocultar")