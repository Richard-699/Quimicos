import os
import json
import mysql.connector

def obtener_conexion():
    # 1. Intentar archivo database.json del proyecto
    config_file = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..', 'config', 'database.json'))
    if os.path.exists(config_file):
        try:
            with open(config_file, 'r', encoding='utf-8') as f:
                full_cfg = json.load(f)
                cfg = (
                    full_cfg.get('gestion_ambiental_hwi')
                    or full_cfg.get('quimicos_hwi')
                    or full_cfg.get('dbp492eljihwxp')
                    or (next(iter(full_cfg.values())) if full_cfg else {})
                )
                if cfg:
                    return mysql.connector.connect(
                        host=cfg.get('host', 'localhost'),
                        user=cfg.get('user', 'root'),
                        password=cfg.get('password', ''),
                        database=cfg.get('database', 'gestion_ambiental_hwi'),
                        port=int(cfg.get('port', 3306))
                    )
        except Exception as e:
            print(f"Error conectando a BD desde json: {e}")

    # 2. Variables de entorno o fallback
    db_host = os.getenv("DB_HOST", "localhost")
    db_user = os.getenv("DB_USER", "root")
    db_pass = os.getenv("DB_PASSWORD", "")
    db_name = os.getenv("DB_NAME", "dbp492eljihwxp")
    db_port = int(os.getenv("DB_PORT", 3306))

    try:
        return mysql.connector.connect(
            host=db_host,
            user=db_user,
            password=db_pass,
            database=db_name,
            port=db_port
        )
    except mysql.connector.Error as err:
        print(f"Error conectando a MySQL ({db_host}): {err}")
        return None