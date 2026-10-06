-- ================================================================
-- Tabla: gestion_ambiental_hwi_consumo_agua_ml_scores
-- Propósito: Guardar los scores del modelo ML de consumo de agua.
-- Cada vez que se registra un consumo se llama a predict_consumo.py
-- y el resultado se persiste aquí para el dashboard.
-- ================================================================

CREATE TABLE IF NOT EXISTS `gestion_ambiental_hwi_consumo_agua_ml_scores` (
  `id_score`                   INT(11)         NOT NULL AUTO_INCREMENT,
  `id_consumo_agua_score`      INT(11)         NOT NULL COMMENT 'FK hacia gestion_ambiental_hwi_consumo_agua.id_consumo_agua',
  `tope_calculado`             DOUBLE          NULL     COMMENT 'Tope predictivo (pred + 2*RMSE)',
  `prediccion_modelo`          DOUBLE          NULL     COMMENT 'Valor predicho por el modelo para ese día',
  `rmse`                       DOUBLE          NULL     COMMENT 'RMSE del modelo en el momento del cálculo',
  `score_anomalia`             DOUBLE          NULL     COMMENT 'Qué tanto superó el tope: (consumo - tope) / tope',
  `supera_tope`                TINYINT(1)      NOT NULL DEFAULT 0 COMMENT '1 = superó el tope ML',
  `fecha_score`                DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Cuándo se calculó',
  PRIMARY KEY (`id_score`),
  UNIQUE KEY `uq_consumo_score` (`id_consumo_agua_score`),
  CONSTRAINT `fk_score_consumo`
    FOREIGN KEY (`id_consumo_agua_score`)
    REFERENCES `gestion_ambiental_hwi_consumo_agua` (`id_consumo_agua`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Scores del modelo ML de consumo de agua (RandomForest)';
