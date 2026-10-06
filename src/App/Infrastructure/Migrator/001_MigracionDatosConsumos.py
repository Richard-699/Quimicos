import json
from pathlib import Path
import pandas as pd
import numpy as np
from sqlalchemy import create_engine

BASE_DIR = Path(__file__).resolve().parent.parent.parent.parent.parent

CONFIG_PATH = BASE_DIR / "config" / "database.json"
EXCEL_PATH = BASE_DIR / "src" / "App" / "Infrastructure" / "Migrator" / "F-GM-118 Control consumo de agua potable.xlsx"

if not CONFIG_PATH.exists():
    raise FileNotFoundError(f"No se encontró el archivo de configuración en: {CONFIG_PATH}")

with open(CONFIG_PATH, "r", encoding="utf-8") as file:
    db_config = json.load(file)["gestion_ambiental_hwi"]

DB_USER = db_config["user"]
DB_PASS = db_config["password"]
DB_HOST = db_config["host"]
DB_NAME = db_config["database"]

pass_segment = f":{DB_PASS}" if DB_PASS else ""
DATABASE_URL = f"mysql+pymysql://{DB_USER}{pass_segment}@{DB_HOST}/{DB_NAME}"

engine = create_engine(DATABASE_URL)

print(f"Cargando archivo Excel desde: {EXCEL_PATH}")
df_raw = pd.read_excel(EXCEL_PATH, sheet_name="2024-2025")

df_raw['Fecha'] = pd.to_datetime(df_raw['Fecha'], errors='coerce')
df_raw = df_raw.dropna(subset=['Fecha'])

MAPPING_CONFIG = [
    # Célula Recubrimiento (id_celula = 3)
    (3, 1, 'Inicial T1', 'Final T1'),
    (3, 2, 'Inicial T2', 'Final T2'),
    (3, 3, 'Inicial T3', 'Final T3'),
    (3, 4, 'Inicial PO', 'Final PO'),
    (3, 5, 'Inicial TR', 'Final TR'),
    
    # Célula LAP (id_celula = 21)
    (21, None, 'Inicial LAP', 'Final LAP'),
    
    # Sub Ensamble (id_celula = 16)
    (16, None, 'Inicial SUB', 'Final SUB'),
    
    # Laboratorio de Calidad (id_celula = 22)
    (22, None, 'Inicial LABS', 'Final LABS'),
]

rows_to_insert = []

for idx, row in df_raw.iterrows():
    fecha = row['Fecha']
    
    for id_celula, id_tanque, col_ini, col_fin in MAPPING_CONFIG:
        val_ini = pd.to_numeric(row.get(col_ini, np.nan), errors='coerce')
        val_fin = pd.to_numeric(row.get(col_fin, np.nan), errors='coerce')
        
        if pd.isna(val_ini) or pd.isna(val_fin):
            continue
            
        rows_to_insert.append({
            'fecha_consumo_agua': fecha,
            'id_celula_consumo_agua': int(id_celula),
            'id_tanque_abastecimiento_consumo_agua': int(id_tanque) if pd.notna(id_tanque) and id_tanque is not None else None,
            'consumo_inicial_agua': float(val_ini),
            'consumo_final_agua': float(val_fin)
        })

df_final = pd.DataFrame(rows_to_insert)

TABLE_NAME = "gestion_ambiental_hwi_consumo_agua"

if not df_final.empty:
    print(f"Insertando {len(df_final)} registros válidos en la tabla '{TABLE_NAME}'...")
    df_final.to_sql(
        name=TABLE_NAME,
        con=engine,
        if_exists='append',
        index=False,
        chunksize=500
    )
    print("¡Migración realizada exitosamente!")
else:
    print("No se encontraron registros válidos para migrar.")