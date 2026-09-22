import os
import streamlit as st
import mysql.connector

if "DB_HOST" in st.secrets:
    db_host = st.secrets["DB_HOST"]
    db_user = st.secrets["DB_USER"]
    db_pass = st.secrets["DB_PASSWORD"]
    db_name = st.secrets["DB_NAME"]
    db_port = int(st.secrets.get("DB_PORT", 3306))
else:
    db_host = os.getenv("DB_HOST", "localhost")
    db_user = os.getenv("DB_USER", "root")
    db_pass = os.getenv("DB_PASSWORD", "")
    db_name = os.getenv("DB_NAME", "nombre_bd_local")
    db_port = int(os.getenv("DB_PORT", 3306))

try:
    conn = mysql.connector.connect(
        host=db_host,
        user=db_user,
        password=db_pass,
        database=db_name,
        port=db_port
    )
except mysql.connector.Error as err:
    st.error(f"❌ Error conectando a MySQL en ({db_host}): {err}")
    st.stop()