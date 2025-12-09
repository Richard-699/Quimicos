-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-11-2025 a las 21:27:21
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
  `password_is_temporal` int(1) NOT NULL,
  `estado_administrador` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(12, 'Inventario'),
(13, 'Planeación'),
(14, 'Seguridad y Salud en el Trabajo'),
(16, 'Sub-Ensamble'),
(17, 'Kaizen Shop'),
(18, 'Ingeniería'),
(20, 'Administrativos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `quimicos_hwi_estados`
--

CREATE TABLE `quimicos_hwi_estados` (
  `id_estado` int(10) NOT NULL,
  `descripcion_estado` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `quimicos_hwi_estados`
--

INSERT INTO `quimicos_hwi_estados` (`id_estado`, `descripcion_estado`) VALUES
(1, 'Aprobado'),
(2, 'Rechazado'),
(3, 'Pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `quimicos_hwi_quimicos`
--

CREATE TABLE `quimicos_hwi_quimicos` (
  `id_quimico` varchar(300) NOT NULL,
  `descripcion_quimico` varchar(100) NOT NULL,
  `umb_quimico` varchar(50) NOT NULL,
  `cantidad_disponible_quimico` double NOT NULL,
  `cantidad_maxima_retiro_quimico` double NOT NULL,
  `tope_minimo_quimico` double NOT NULL,
  `precio_quimico` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `quimicos_hwi_solicitudes_consumo`
--

CREATE TABLE `quimicos_hwi_solicitudes_consumo` (
  `id_solicitud_consumo` int(10) NOT NULL,
  `fecha_solicitud_consumo` date NOT NULL,
  `id_celula_area_solicitud_consumo` int(10) NOT NULL,
  `id_quimico_solicitud_consumo` varchar(300) NOT NULL,
  `cantidad_solicitud_consumo` double NOT NULL,
  `nombres_solicitante_consumo` varchar(100) NOT NULL,
  `apellidos_solicitante_consumo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `quimicos_hwi_umbs`
--

CREATE TABLE `quimicos_hwi_umbs` (
  `id_umb` int(10) NOT NULL,
  `descripcion_umb` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indices de la tabla `quimicos_hwi_quimicos`
--
ALTER TABLE `quimicos_hwi_quimicos`
  ADD PRIMARY KEY (`id_quimico`);

--
-- Indices de la tabla `quimicos_hwi_solicitudes_consumo`
--
ALTER TABLE `quimicos_hwi_solicitudes_consumo`
  ADD PRIMARY KEY (`id_solicitud_consumo`);

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
  MODIFY `id_celulas_areas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `quimicos_hwi_estados`
--
ALTER TABLE `quimicos_hwi_estados`
  MODIFY `id_estado` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `quimicos_hwi_solicitudes_consumo`
--
ALTER TABLE `quimicos_hwi_solicitudes_consumo`
  MODIFY `id_solicitud_consumo` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `quimicos_hwi_umbs`
--
ALTER TABLE `quimicos_hwi_umbs`
  MODIFY `id_umb` int(10) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
