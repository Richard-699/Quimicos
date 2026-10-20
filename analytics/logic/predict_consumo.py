import sys
import json
import pandas as pd
import numpy as np
from pathlib import Path
from sqlalchemy import create_engine, text
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import mean_squared_error

BASE_DIR = Path(__file__).resolve().parent.parent.parent
CONFIG_PATH = BASE_DIR / "config" / "database.json"

def get_engine():
    with open(CONFIG_PATH, "r", encoding="utf-8") as file:
        db_config = json.load(file)["gestion_ambiental_hwi"]

    DB_USER = db_config["user"]
    DB_PASS = db_config["password"]
    DB_HOST = db_config["host"]
    DB_NAME = db_config["database"]

    pass_segment = f":{DB_PASS}" if DB_PASS else ""
    DATABASE_URL = f"mysql+pymysql://{DB_USER}{pass_segment}@{DB_HOST}/{DB_NAME}"
    return create_engine(DATABASE_URL)


def guardar_score(engine, id_consumo_agua, tope, prediccion, rmse, score_anomalia, supera_tope):
    """Persiste el resultado ML en gestion_ambiental_hwi_consumo_agua_ml_scores."""
    sql = text("""
        INSERT INTO gestion_ambiental_hwi_consumo_agua_ml_scores
            (id_consumo_agua_score, tope_calculado, prediccion_modelo, rmse, score_anomalia, supera_tope, fecha_score)
        VALUES
            (:id_consumo, :tope, :pred, :rmse, :score, :supera, NOW())
        ON DUPLICATE KEY UPDATE
            tope_calculado    = VALUES(tope_calculado),
            prediccion_modelo = VALUES(prediccion_modelo),
            rmse              = VALUES(rmse),
            score_anomalia    = VALUES(score_anomalia),
            supera_tope       = VALUES(supera_tope),
            fecha_score       = NOW()
    """)
    with engine.connect() as conn:
        conn.execute(sql, {
            "id_consumo": id_consumo_agua,
            "tope":  round(tope, 4),
            "pred":  round(prediccion, 4),
            "rmse":  round(rmse, 4),
            "score": round(score_anomalia, 4) if score_anomalia is not None else None,
            "supera": 1 if supera_tope else 0,
        })
        conn.commit()


def main():
    # Argumentos: id_celula  id_tanque  consumo_dia  [id_consumo_agua]
    if len(sys.argv) < 4:
        print(json.dumps({"error": "Faltan argumentos"}))
        return

    id_celula      = int(sys.argv[1])
    id_tanque_arg  = sys.argv[2]
    id_tanque      = int(id_tanque_arg) if id_tanque_arg and id_tanque_arg.lower() != 'null' else None
    consumo_dia    = float(sys.argv[3])
    id_consumo_arg  = sys.argv[4] if len(sys.argv) >= 5 else None
    id_consumo_agua = int(id_consumo_arg) if id_consumo_arg and id_consumo_arg.lower() != 'null' else None

    engine = get_engine()

    # ─── Query de histórico (excluyendo el registro actual para no sesgar el modelo) ──
    id_filter = f"AND id_consumo_agua < {id_consumo_agua}" if id_consumo_agua is not None else ""

    if id_tanque is not None:
        query = f"""
            SELECT id_consumo_agua, fecha_consumo_agua, (consumo_final_agua - consumo_inicial_agua) as consumo
            FROM gestion_ambiental_hwi_consumo_agua
            WHERE id_celula_consumo_agua = {id_celula}
              AND id_tanque_abastecimiento_consumo_agua = {id_tanque}
              {id_filter}
            ORDER BY fecha_consumo_agua ASC
        """
    else:
        query = f"""
            SELECT id_consumo_agua, fecha_consumo_agua, (consumo_final_agua - consumo_inicial_agua) as consumo
            FROM gestion_ambiental_hwi_consumo_agua
            WHERE id_celula_consumo_agua = {id_celula}
              AND id_tanque_abastecimiento_consumo_agua IS NULL
              {id_filter}
            ORDER BY fecha_consumo_agua ASC
        """

    df = pd.read_sql(query, engine)

    # ── Remove any records that are clearly erroneous (negative or zero consumo) ──
    df = df[df['consumo'] > 0]

    # ── Apply IQR-based outlier removal starting from 5+ records (2.0x IQR = strict) ──
    if len(df) >= 5:
        q75 = df['consumo'].quantile(0.75)
        q25 = df['consumo'].quantile(0.25)
        iqr = q75 - q25
        if iqr > 0:
            upper_limit = q75 + (2.0 * iqr)
        else:
            # No variance: use 3x mean as cap
            upper_limit = df['consumo'].mean() * 3.0
        # Also use a hard cap: if any record is > 10x the median, remove it
        median_val = df['consumo'].median()
        hard_cap = max(upper_limit, median_val * 5.0)
        df = df[df['consumo'] <= hard_cap]

    if len(df) < 5:
        result = {
            "error": "No hay suficientes datos históricos",
            "tope": None,
            "supera_tope": False
        }
        print(json.dumps(result))
        return

    df['fecha_consumo_agua'] = pd.to_datetime(df['fecha_consumo_agua'])
    df['dayofweek'] = df['fecha_consumo_agua'].dt.dayofweek
    df['month']     = df['fecha_consumo_agua'].dt.month
    df['day']       = df['fecha_consumo_agua'].dt.day
    df = df.dropna()

    X = df[['dayofweek', 'month', 'day']]
    y = df['consumo']

    # Train / Test split 80/20
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

    model = RandomForestRegressor(n_estimators=50, random_state=42)
    model.fit(X_train, y_train)

    predictions = model.predict(X_test)
    rmse = float(np.sqrt(mean_squared_error(y_test, predictions)))

    # Predicción para hoy
    today = pd.Timestamp.today()
    X_today = pd.DataFrame({
        'dayofweek': [today.dayofweek],
        'month':     [today.month],
        'day':       [today.day]
    })
    pred_today = float(model.predict(X_today)[0])

    # Tope = predicción + 2*RMSE (intervalo ~95 %)
    tope = pred_today + (2 * rmse) + 0.1
    supera = bool(consumo_dia > tope)

    # Score de anomalía: % de exceso respecto al tope
    score_anomalia = round((consumo_dia - tope) / tope, 4) if tope > 0 else None

    # ─── Guardar en BD si tenemos el id_consumo_agua ─────────────────
    if id_consumo_agua is not None:
        try:
            guardar_score(
                engine,
                id_consumo_agua=id_consumo_agua,
                tope=tope,
                prediccion=pred_today,
                rmse=rmse,
                score_anomalia=score_anomalia,
                supera_tope=supera
            )
        except Exception as e:
            # No interrumpir el flujo si falla el guardado
            import sys as _sys
            print(f"[ML WARN] No se pudo guardar el score: {e}", file=_sys.stderr)

    result = {
        "tope":        round(tope, 4),
        "prediccion":  round(pred_today, 4),
        "rmse":        round(rmse, 4),
        "score_anomalia": score_anomalia,
        "supera_tope": supera
    }
    print(json.dumps(result))


if __name__ == "__main__":
    main()
