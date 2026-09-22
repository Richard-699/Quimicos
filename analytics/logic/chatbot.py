import os
import re
import streamlit as st
from dotenv import load_dotenv
from groq import Groq

load_dotenv()

def inicializar_groq():
    """Conecta con la API de Groq priorizando st.secrets o el archivo .env."""
    api_key = None
    
    try:
        api_key = st.secrets.get("GROQ_API_KEY")
    except Exception:
        pass
        
    if not api_key:
        api_key = os.getenv("GROQ_API_KEY")
        
    if not api_key:
        st.error("❌ No se encontró la clave 'GROQ_API_KEY' en el archivo .env ni en st.secrets.")
        st.stop()
        
    return Groq(api_key=api_key)

def obtener_modelo_activo(client):
    """Busca un modelo de chat de texto activo en Groq evitando modelos restringidos o de voz."""
    candidatos_validos = [
        "llama-3.3-70b-versatile",
        "llama-3.1-8b-instant",
        "llama3-70b-8192",
        "llama3-8b-8192",
        "gemma2-9b-it",
        "mixtral-8x7b-32768"
    ]
    
    try:
        modelos_api = client.models.list()
        
        ids_disponibles = [
            m.id for m in modelos_api.data 
            if not any(x in m.id.lower() for x in ['canopylabs', 'whisper', 'audio', 'orpheus', 'tts', 'guard'])
        ]
        
        for candidato in candidatos_validos:
            if candidato in ids_disponibles: 
                return candidato
                
        for m_id in ids_disponibles:
            if any(m_id.lower().startswith(prefix) for prefix in ['llama', 'gemma', 'mixtral', 'qwen']):
                return m_id
            
        return "llama-3.3-70b-versatile"
    except Exception:
        return "llama-3.3-70b-versatile"
        
def generar_respuesta(client, modelo, mensajes_historial, contexto):
    """Estructura el Prompt aislando el contexto en el System Message con validación robusta."""
    sys_msg = (
        "Eres el asistente logístico experto en inventario de la empresa HWI. "
        "REGLA OBLIGATORIA DE IDIOMA: Responde SIEMPRE Y ÚNICAMENTE en ESPAÑOL. "
        "Está estrictamente prohibido responder en inglés. "
        "No muestres procesos de pensamiento ni etiquetas como <think>. "
        "Responde de forma directa, precisa, clara y amable basándote EXCLUSIVAMENTE en el contexto proporcionado.\n\n"
        f"--- CONTEXTO ACTUAL EN PANTALLA ---\n{contexto}\n-----------------------------------"
    )
    
    historial_limpio = []
    for msg in mensajes_historial:
        contenido = re.sub(r'<think>.*?</think>', '', str(msg.get("content", "")), flags=re.DOTALL).strip()
        if contenido:
            historial_limpio.append({"role": msg["role"], "content": contenido})
    
    msgs_api = [{"role": "system", "content": sys_msg}] + historial_limpio
    
    try:
        resp = client.chat.completions.create(model=modelo, messages=msgs_api, temperature=0.2)
        if resp and resp.choices and len(resp.choices) > 0:
            texto_respuesta = resp.choices[0].message.content or ""
            texto_limpio = re.sub(r'<think>.*?</think>', '', texto_respuesta, flags=re.DOTALL).strip()
            if texto_limpio:
                return texto_limpio
        return None
    except Exception as e:
        print(f"Error en motor IA ({modelo}): {e}")
        return None