-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-01-2026 a las 16:26:53
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `quimicos_hwi`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `quimicos_hwi_administradores`
--

CREATE TABLE `quimicos_hwi_administradores` (
  `id_administrador` varchar(300) NOT NULL,
  `cedula_administrador` bigint(20) NOT NULL,
  `nombre_administrador` varchar(100) NOT NULL,
  `apellidos_administrador` varchar(100) NOT NULL,
  `correo_hwi_administrador` varchar(100) NOT NULL,
  `password_administrador` varchar(400) NOT NULL,
  `password_is_temporal` int(11) NOT NULL,
  `estado_administrador` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `quimicos_hwi_administradores`
--

INSERT INTO `quimicos_hwi_administradores` (`id_administrador`, `cedula_administrador`, `nombre_administrador`, `apellidos_administrador`, `correo_hwi_administrador`, `password_administrador`, `password_is_temporal`, `estado_administrador`) VALUES
('3f77418f-e6df-4e0b-b09d-f21cbaa1959e', 1036675723, 'Juliana', 'Gallego Montoya', 'gestion.ambiental@hacebwhirlpool.com', '$2y$10$t1.JhlsZRqh2Y6YFYanAs.GdFJaN47BtUxrTI5MPvtiTxNpaKtx2O', 0, 1),
('5cca8518-ac8d-4793-9f48-b10e2b29906a', 1001768645, 'Ricardo', 'Rojas Yepes', 'ricardo.rojas@hacebwhirlpool.com', '$2y$10$ozn62ov1o28rgsjzOKLEj.g.NDf97vyGvcYO4aG83dI94hnTKU1KC', 0, 1),
('aa35b925-fc83-4f8b-9515-26a7fac4d48a', 98701708, 'Andres Felipe ', 'Cuartas', 'almacen.indirectos@hacebwhirlpool.com', '$2y$10$qzdtYB2G2w7dzSqkoWCHe.BrUUejt/3ehu0cOfADT2PXmPvkVZJo.', 0, 1),
('d432599f-7717-4681-8743-2480e45fd40e', 1001250448, 'Samuel', 'Cano Ocampo', 'administrador.smartcenter@hacebwhirlpool.com', '$2y$10$GrYZfcFYfim9RIvLZ7D62O5x.FFXEnNujUCHLyghLXVFWlY5jxNzu', 0, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `quimicos_hwi_celulas_areas`
--

CREATE TABLE `quimicos_hwi_celulas_areas` (
  `id_celulas_areas` int(11) NOT NULL,
  `nombre_celula` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `quimicos_hwi_celulas_areas`
--

INSERT INTO `quimicos_hwi_celulas_areas` (`id_celulas_areas`, `nombre_celula`) VALUES
(1, 'Almacen Materia Prima'),
(2, 'Conformadora cesto y gabinete'),
(3, 'Recubrimiento'),
(4, 'Mecanismos'),
(5, 'Ensamble cesto y gabinete'),
(6, 'Tapa fija'),
(7, 'Testeo Final'),
(9, 'Logistica de Salida'),
(10, 'Mantenimiento'),
(11, 'Calidad'),
(14, 'Seguridad y Salud en el Trabajo'),
(16, 'Sub-Ensamble'),
(17, 'Kaizen Shop'),
(20, 'Administrativos'),
(21, 'LAP'),
(22, 'Laboratorio de Calidad'),
(23, 'Metrología'),
(24, 'Producto No Conforme'),
(25, 'PTAR'),
(26, 'EULEN'),
(27, 'Acopio de Residuos'),
(28, 'Empacadora');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `quimicos_hwi_estados`
--

CREATE TABLE `quimicos_hwi_estados` (
  `id_estado` int(11) NOT NULL,
  `descripcion_estado` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `quimicos_hwi_estados`
--

INSERT INTO `quimicos_hwi_estados` (`id_estado`, `descripcion_estado`) VALUES
(1, 'Aprobado'),
(2, 'Rechazado'),
(3, 'Pendiente'),
(4, 'Activo'),
(5, 'Inactivo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `quimicos_hwi_logs_precios`
--

CREATE TABLE `quimicos_hwi_logs_precios` (
  `id_log_precio` int(11) NOT NULL,
  `fecha_log_precio` date NOT NULL,
  `id_quimico_log_precio` varchar(300) NOT NULL,
  `precio_quimico` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `quimicos_hwi_peligrosidad`
--

CREATE TABLE `quimicos_hwi_peligrosidad` (
  `id_peligrosidad` int(11) NOT NULL,
  `descripcion_peligrosidad` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `quimicos_hwi_peligrosidad`
--

INSERT INTO `quimicos_hwi_peligrosidad` (`id_peligrosidad`, `descripcion_peligrosidad`) VALUES
(1, 'No Peligroso'),
(2, 'Peligroso');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `quimicos_hwi_quimicos`
--

CREATE TABLE `quimicos_hwi_quimicos` (
  `id_quimico` varchar(300) NOT NULL,
  `descripcion_quimico` varchar(100) NOT NULL,
  `fabricante_quimico` varchar(200) NOT NULL,
  `id_peligrosidad_quimico` int(11) NOT NULL,
  `uso_quimico` varchar(500) NOT NULL,
  `id_umb_quimico` int(11) NOT NULL,
  `cantidad_disponible_quimico` double NOT NULL,
  `cantidad_maxima_retiro_quimico` double NOT NULL,
  `tope_minimo_quimico` double NOT NULL,
  `precio_quimico` double NOT NULL,
  `url_etiqueta_emergencia_quimico` varchar(600) NOT NULL,
  `id_estado_quimico` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `quimicos_hwi_quimicos_celulas_areas`
--

CREATE TABLE `quimicos_hwi_quimicos_celulas_areas` (
  `id_quimico_celula_area` int(11) NOT NULL,
  `id_quimico_quimicos` varchar(300) NOT NULL,
  `id_celulas_areas_quimicos` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `quimicos_hwi_solicitudes_consumo`
--

CREATE TABLE `quimicos_hwi_solicitudes_consumo` (
  `id_solicitud_consumo` int(11) NOT NULL,
  `fecha_solicitud_consumo` date NOT NULL,
  `id_celula_area_solicitud_consumo` int(11) NOT NULL,
  `id_quimico_solicitud_consumo` varchar(300) NOT NULL,
  `cantidad_solicitud_consumo` double NOT NULL,
  `cedula_solicitante` int(11) NOT NULL,
  `nombres_solicitante_consumo` varchar(100) NOT NULL,
  `apellidos_solicitante_consumo` varchar(100) NOT NULL,
  `id_estado_solicitud_quimico` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `quimicos_hwi_umbs`
--

CREATE TABLE `quimicos_hwi_umbs` (
  `id_umb` int(11) NOT NULL,
  `descripcion_umb` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `quimicos_hwi_umbs`
--

INSERT INTO `quimicos_hwi_umbs` (`id_umb`, `descripcion_umb`) VALUES
(4, 'Kg'),
(5, 'Lts'),
(6, 'Lata'),
(7, 'Gr'),
(8, 'Lb'),
(9, 'Pipeta'),
(10, 'Ml');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `quimicos_hwi_administradores`
--
ALTER TABLE `quimicos_hwi_administradores`
  ADD PRIMARY KEY (`id_administrador`);

--
-- Indices de la tabla `quimicos_hwi_celulas_areas`
--
ALTER TABLE `quimicos_hwi_celulas_areas`
  ADD PRIMARY KEY (`id_celulas_areas`);

--
-- Indices de la tabla `quimicos_hwi_estados`
--
ALTER TABLE `quimicos_hwi_estados`
  ADD PRIMARY KEY (`id_estado`);

--
-- Indices de la tabla `quimicos_hwi_logs_precios`
--
ALTER TABLE `quimicos_hwi_logs_precios`
  ADD PRIMARY KEY (`id_log_precio`),
  ADD KEY `id_quimico_log_precio` (`id_quimico_log_precio`);

--
-- Indices de la tabla `quimicos_hwi_peligrosidad`
--
ALTER TABLE `quimicos_hwi_peligrosidad`
  ADD PRIMARY KEY (`id_peligrosidad`);

--
-- Indices de la tabla `quimicos_hwi_quimicos`
--
ALTER TABLE `quimicos_hwi_quimicos`
  ADD PRIMARY KEY (`id_quimico`),
  ADD KEY `id_umb_quimicos` (`id_umb_quimico`),
  ADD KEY `id_estado_quimico` (`id_estado_quimico`),
  ADD KEY `id_peligrosidad_quimico` (`id_peligrosidad_quimico`);

--
-- Indices de la tabla `quimicos_hwi_quimicos_celulas_areas`
--
ALTER TABLE `quimicos_hwi_quimicos_celulas_areas`
  ADD PRIMARY KEY (`id_quimico_celula_area`),
  ADD KEY `id_quimico_quimicos` (`id_quimico_quimicos`),
  ADD KEY `id_celulas_areas_quimicos` (`id_celulas_areas_quimicos`);

--
-- Indices de la tabla `quimicos_hwi_solicitudes_consumo`
--
ALTER TABLE `quimicos_hwi_solicitudes_consumo`
  ADD PRIMARY KEY (`id_solicitud_consumo`),
  ADD KEY `id_celula_area_solicitud_consumo` (`id_celula_area_solicitud_consumo`),
  ADD KEY `id_quimico_solicitud_consumo` (`id_quimico_solicitud_consumo`),
  ADD KEY `id_estado_solicitud_quimico` (`id_estado_solicitud_quimico`);

--
-- Indices de la tabla `quimicos_hwi_umbs`
--
ALTER TABLE `quimicos_hwi_umbs`
  ADD PRIMARY KEY (`id_umb`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `quimicos_hwi_celulas_areas`
--
ALTER TABLE `quimicos_hwi_celulas_areas`
  MODIFY `id_celulas_areas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `quimicos_hwi_estados`
--
ALTER TABLE `quimicos_hwi_estados`
  MODIFY `id_estado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `quimicos_hwi_logs_precios`
--
ALTER TABLE `quimicos_hwi_logs_precios`
  MODIFY `id_log_precio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `quimicos_hwi_peligrosidad`
--
ALTER TABLE `quimicos_hwi_peligrosidad`
  MODIFY `id_peligrosidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `quimicos_hwi_quimicos_celulas_areas`
--
ALTER TABLE `quimicos_hwi_quimicos_celulas_areas`
  MODIFY `id_quimico_celula_area` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT de la tabla `quimicos_hwi_solicitudes_consumo`
--
ALTER TABLE `quimicos_hwi_solicitudes_consumo`
  MODIFY `id_solicitud_consumo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `quimicos_hwi_umbs`
--
ALTER TABLE `quimicos_hwi_umbs`
  MODIFY `id_umb` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `quimicos_hwi_logs_precios`
--
ALTER TABLE `quimicos_hwi_logs_precios`
  ADD CONSTRAINT `id_quimico_log_precio` FOREIGN KEY (`id_quimico_log_precio`) REFERENCES `quimicos_hwi_quimicos` (`id_quimico`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `quimicos_hwi_quimicos`
--
ALTER TABLE `quimicos_hwi_quimicos`
  ADD CONSTRAINT `id_estado_quimico` FOREIGN KEY (`id_estado_quimico`) REFERENCES `quimicos_hwi_estados` (`id_estado`),
  ADD CONSTRAINT `id_peligrosidad_quimico` FOREIGN KEY (`id_peligrosidad_quimico`) REFERENCES `quimicos_hwi_peligrosidad` (`id_peligrosidad`),
  ADD CONSTRAINT `id_umb_quimicos` FOREIGN KEY (`id_umb_quimico`) REFERENCES `quimicos_hwi_umbs` (`id_umb`);

--
-- Filtros para la tabla `quimicos_hwi_quimicos_celulas_areas`
--
ALTER TABLE `quimicos_hwi_quimicos_celulas_areas`
  ADD CONSTRAINT `id_celulas_areas_quimicos` FOREIGN KEY (`id_celulas_areas_quimicos`) REFERENCES `quimicos_hwi_celulas_areas` (`id_celulas_areas`),
  ADD CONSTRAINT `id_quimico_quimicos` FOREIGN KEY (`id_quimico_quimicos`) REFERENCES `quimicos_hwi_quimicos` (`id_quimico`);

--
-- Filtros para la tabla `quimicos_hwi_solicitudes_consumo`
--
ALTER TABLE `quimicos_hwi_solicitudes_consumo`
  ADD CONSTRAINT `id_celula_area_solicitud_consumo` FOREIGN KEY (`id_celula_area_solicitud_consumo`) REFERENCES `quimicos_hwi_celulas_areas` (`id_celulas_areas`),
  ADD CONSTRAINT `id_estado_solicitud_quimico` FOREIGN KEY (`id_estado_solicitud_quimico`) REFERENCES `quimicos_hwi_estados` (`id_estado`),
  ADD CONSTRAINT `id_quimico_solicitud_consumo` FOREIGN KEY (`id_quimico_solicitud_consumo`) REFERENCES `quimicos_hwi_quimicos` (`id_quimico`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
