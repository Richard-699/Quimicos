import streamlit as st

def aplicar_estilos_corporativos():
    """Inyecta FontAwesome 6, CSS de control de scroll estricto y estilos HWI."""
    st.markdown("""
        <!-- Carga CDN FontAwesome 6.4.0 -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <style>
            /* 1. RESET Y CONTROL ESTRICTO DE VIEWPORT */
            html, body, .stApp, section.main, .main .block-container {
                background-color: #f8fafc !important;
                color: #0f172a !important;
                padding-top: 0.1rem !important;
                padding-bottom: 2.5rem !important;
                padding-left: 0.6rem !important;
                padding-right: 0.6rem !important;
            }

            header[data-testid="stHeader"], 
            footer, 
            [data-testid="stSidebar"] {
                display: none !important;
            }

            /* 2. BANNER DE TÍTULO PRINCIPAL HWI */
            .hwi-header-banner {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-left: 5px solid #00a8cc;
                padding: 10px 16px;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            }

            .hwi-title {
                font-size: 1.25rem;
                font-weight: 700;
                color: #0f172a;
                display: flex;
                align-items: center;
                gap: 12px;
                margin: 0;
            }

            .hwi-title i {
                color: #00a8cc;
                font-size: 1.3rem;
            }

            /* 3. BORDE DEFINIDO Y VISIBLE EN EL SELECTBOX */
            div[data-testid="stSelectbox"] label p {
                color: #0f172a !important;
                font-weight: 700 !important;
                font-size: 0.88rem !important;
                margin-bottom: 4px !important;
            }

            div[data-testid="stSelectbox"] [data-baseweb="select"] {
                border: 1.5px solid #64748b !important;
                border-radius: 6px !important;
                background-color: #ffffff !important;
                overflow: hidden !important;
            }

            div[data-testid="stSelectbox"] [data-baseweb="select"] > div {
                border: none !important;
                background-color: transparent !important;
            }

            div[data-testid="stSelectbox"] [data-baseweb="select"]:hover,
            div[data-testid="stSelectbox"] [data-baseweb="select"]:focus-within {
                border-color: #00a8cc !important;
                box-shadow: 0 0 0 1px #00a8cc !important;
            }

            div[data-baseweb="select"] span,
            div[data-baseweb="select"] input {
                color: #0f172a !important;
                font-weight: 600 !important;
            }

            /* 4. TARJETAS KPI EJECUTIVAS */
            [data-testid="stMetric"] {
                background-color: #ffffff !important;
                border: 1px solid #e2e8f0 !important;
                border-top: 3px solid #00a8cc !important;
                padding: 8px 12px !important;
                border-radius: 8px !important;
                box-shadow: 0 1px 3px rgba(0,0,0,0.03) !important;
            }

            [data-testid="stMetricLabel"] {
                color: #64748b !important;
                font-weight: 600 !important;
                font-size: 0.75rem !important;
                text-transform: uppercase;
            }

            [data-testid="stMetricValue"] {
                color: #0f172a !important;
                font-weight: 800 !important;
                font-size: 1.2rem !important;
            }

            /* Insignia Logística */
            .hwi-status-badge {
                background: #ffffff;
                border: 1px solid #cbd5e1;
                padding: 6px 12px;
                border-radius: 6px;
                font-size: 0.8rem;
                color: #334155;
                margin-top: 6px;
                margin-bottom: 8px;
                display: flex;
                align-items: center;
                gap: 12px;
            }

            /* 5. ENCABEZADO Y CONTENEDOR DEL CHAT CORPORATIVO */
            .chat-header {
                background: #ffffff;
                border: 1px solid #cbd5e1;
                border-radius: 8px 8px 0 0;
                padding: 8px 12px;
                font-weight: 700;
                font-size: 0.9rem;
                color: #0f172a;
                display: flex;
                align-items: center;
                justify-content: space-between;
                border-bottom: 2px solid #f1f5f9;
                white-space: nowrap !important;
            }

            .chat-header span {
                white-space: nowrap !important;
            }

            div[data-testid="stVerticalBlock"] > div.stContainer {
                background-color: #ffffff !important;
                border: 1px solid #cbd5e1 !important;
                border-top: none !important;
                border-radius: 0 0 8px 8px !important;
            }

            /* Burbuja Azul Suave para el Usuario */
            [data-testid="stChatMessage"]:has([aria-label="Chat message avatar user"]),
            [data-testid="stChatMessage"]:has([data-testid="stChatMessageAvatarUser"]) {
                background-color: #e0f2fe !important;
                border: 1px solid #bae6fd !important;
                border-radius: 8px !important;
                padding: 8px 12px !important;
                margin-bottom: 6px !important;
            }

            [data-testid="stChatInput"] {
                padding-top: 4px !important;
                padding-bottom: 0px !important;
            }

            /* 6. TIPOGRAFÍA GENERAL (SIN AFECTAR ÍCONOS NATIVOS) */
            h1, h2, h3, h4, p, label, div {
                font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            }

            /* Forzar restauración de íconos de Streamlit (Material Symbols) */
            [data-testid="stIconMaterial"], 
            [data-testid="stExpanderToggleIcon"] span,
            .material-symbols-outlined, 
            .material-symbols-rounded {
                font-family: 'Material Symbols Rounded', 'Material Symbols Outlined' !important;
            }
        </style>
    """, unsafe_allow_html=True)