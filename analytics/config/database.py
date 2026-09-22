import os
import json
import mysql.connector
import streamlit as st

def obtener_conexion():
    directorio_raiz = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
    ruta_json = os.path.join(directorio_raiz, 'config', 'database.json')

    try:
        with open(ruta_json, 'r', encoding='utf-8') as archivo:
            data = json.load(archivo)
        
        credenciales = data.get('quimicos_hwi', {})
        
        db_host = credenciales.get('host', 'localhost')
        db_user = credenciales.get('user', 'root')
        db_pass = credenciales.get('password', '')
        db_name = credenciales.get('database', 'quimicos_hwi3')

        conexion = mysql.connector.connect(
            host=db_host,
            user=db_user,
            password=db_pass,
            database=db_name
        )
        
        cursor = conexion.cursor()
        cursor.execute(f"USE {db_name};")
        cursor.close()
        
        return conexion

    except FileNotFoundError:
        st.error(f"❌ No se encontró el archivo de configuración en: {ruta_json}")
        return None
    except json.JSONDecodeError:
        st.error("❌ El archivo database.json tiene un formato inválido o corrupto.")
        return None
    except Exception as e:
        st.error(f"❌ Error conectando a MySQL: {e}")
        return None