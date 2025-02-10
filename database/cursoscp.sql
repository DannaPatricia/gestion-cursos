-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 31-01-2025 a las 08:22:12
-- Versión del servidor: 8.0.40-0ubuntu0.24.04.1
-- Versión de PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cursoscp`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos` (
  `codigo` int NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `abierto` tinyint(1) DEFAULT '1',
  `numeroplazas` int DEFAULT NULL,
  `plazoinscripcion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`codigo`, `nombre`, `abierto`, `numeroplazas`, `plazoinscripcion`) VALUES
(1, 'JavaScript', 1, 16, '2025-05-30'),
(2, 'PHP', 0, 4, '2024-06-06'),
(3, 'Bases de Datos', 1, 17, '2025-07-10'),
(4, 'Desarrollo Web', 1, 18, '2025-08-01'),
(5, 'Programación en Java', 0, 15, '2025-06-25'),
(6, 'Ciberseguridad', 1, 9, '2025-07-20'),
(7, 'Diseño Web', 0, 10, '2025-06-30'),
(8, 'Frameworks Web', 1, 18, '2025-09-10'),
(9, 'Machine Learning', 1, 10, '2025-02-05'),
(11, 'MySQL', 1, 20, '2025-01-16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitantes`
--

CREATE TABLE `solicitantes` (
  `dni` char(9) NOT NULL,
  `apellidos` varchar(100) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `correo` varchar(50) DEFAULT NULL,
  `codigocentro` char(8) DEFAULT NULL,
  `coordinadortc` tinyint(1) DEFAULT NULL,
  `grupotc` tinyint(1) DEFAULT NULL,
  `nombregrupo` varchar(50) DEFAULT NULL,
  `pbilin` tinyint(1) DEFAULT NULL,
  `cargo` tinyint(1) DEFAULT NULL,
  `nombrecargo` varchar(15) DEFAULT NULL,
  `situacion` enum('activo','inactivo') DEFAULT NULL,
  `fechanac` date DEFAULT NULL,
  `especialidad` varchar(50) DEFAULT NULL,
  `puntos` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `solicitantes`
--

INSERT INTO `solicitantes` (`dni`, `apellidos`, `nombre`, `telefono`, `correo`, `codigocentro`, `coordinadortc`, `grupotc`, `nombregrupo`, `pbilin`, `cargo`, `nombrecargo`, `situacion`, `fechanac`, `especialidad`, `puntos`) VALUES
('34491564R', 'Torero', 'Tomas', '433678254', 'tomas@domenico.es', '0D12', 1, 1, 'daw', 0, 0, 'vacio', 'activo', '2000-11-24', 'daw', 9),
('38919977Y', 'Sal', 'Karen', '992864773', 'karen@domenico.es', '0A12', 1, 1, 'daw', 1, 1, 'secretario', 'activo', '1997-03-21', 'daw', 14),
('43905889J', 'Torres', 'Maria', '455682267', 'maria@domenico.es', '0B12', 0, 1, 'daw', 0, 1, 'becario', 'inactivo', '2001-03-24', 'daw', 4),
('67492665W', 'Ruiz', 'Paca', '233658368', 'paca@domenico.es', '0E12', 0, 1, 'daw', 1, 1, 'becario', 'inactivo', '1997-12-11', 'daw', 7),
('98736290B', 'juarez', 'Paco', '482603366', 'paco@domenico.es', '0C12', 0, 1, 'daw', 1, 0, 'vacio', 'inactivo', '1997-08-29', 'daw', 7);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes`
--

CREATE TABLE `solicitudes` (
  `dni` char(9) NOT NULL,
  `codigocurso` int NOT NULL,
  `fechasolicitud` date DEFAULT NULL,
  `admitido` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `solicitudes`
--

INSERT INTO `solicitudes` (`dni`, `codigocurso`, `fechasolicitud`, `admitido`) VALUES
('34491564R', 2, '2025-02-05', 0),
('34491564R', 3, '2025-01-31', NULL),
('34491564R', 5, '2025-02-14', NULL),
('38919977Y', 2, '2023-04-06', 0),
('38919977Y', 3, '2025-01-31', NULL),
('38919977Y', 5, '2023-02-02', 1),
('43905889J', 2, '2023-01-01', 0),
('43905889J', 3, '2025-01-31', NULL),
('43905889J', 5, '2023-02-12', NULL),
('67492665W', 2, '2023-02-09', 0),
('67492665W', 3, '2025-01-31', NULL),
('67492665W', 5, '2023-02-05', NULL),
('98736290B', 1, '2025-01-31', NULL),
('98736290B', 2, '2023-01-04', 0),
('98736290B', 3, '2025-01-31', NULL),
('98736290B', 5, '2023-01-05', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `apellidos` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `correo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `telefono` int NOT NULL,
  `nombre_usuario` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `clave` varchar(255) NOT NULL,
  `dni` char(9) DEFAULT NULL,
  `rol` enum('admin','cliente') DEFAULT 'cliente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellidos`, `correo`, `telefono`, `nombre_usuario`, `clave`, `dni`, `rol`) VALUES
(1, 'admin', 'admin', 'admin@gmail.com', 143569764, 'admin', '1234', '12345678A', 'admin'),
(2, 'Paco', 'juarez', 'paco@domenico.es', 482603366, 'paco', 'paco', '98736290B', 'cliente'),
(3, 'Karen', 'Sal', 'karen@domenico.es', 992864773, 'karen', 'karen', '38919977Y', 'cliente'),
(4, 'Maria', 'Torres', 'maria@domenico.es', 455682267, 'maria', 'maria', '43905889J', 'cliente'),
(5, 'Tomas', 'Torero', 'tomas@domenico.es', 433678254, 'tomas', 'tomas', '34491564R', 'cliente'),
(6, 'Paca', 'Ruiz', 'paca@domenico.es', 233658368, 'paca', 'paca', '67492665W', 'cliente');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`codigo`);

--
-- Indices de la tabla `solicitantes`
--
ALTER TABLE `solicitantes`
  ADD PRIMARY KEY (`dni`);

--
-- Indices de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD PRIMARY KEY (`dni`,`codigocurso`),
  ADD KEY `solicitudes_ibfk_2` (`codigocurso`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dni` (`dni`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `codigo` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `solicitantes`
--
ALTER TABLE `solicitantes`
  ADD CONSTRAINT `fk_solicitantes_usuarios` FOREIGN KEY (`dni`) REFERENCES `usuarios` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD CONSTRAINT `solicitudes_ibfk_1` FOREIGN KEY (`dni`) REFERENCES `solicitantes` (`dni`) ON DELETE CASCADE,
  ADD CONSTRAINT `solicitudes_ibfk_2` FOREIGN KEY (`codigocurso`) REFERENCES `cursos` (`codigo`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
