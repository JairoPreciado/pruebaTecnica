-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-07-2025 a las 03:15:55
-- Versión del servidor: 10.4.27-MariaDB
-- Versión de PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ordenes_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes`
--

CREATE TABLE `ordenes` (
  `id` int(11) NOT NULL,
  `cliente` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `estado` enum('Pendiente','En proceso','Completado') NOT NULL,
  `prioridad` enum('Baja','Media','Alta') NOT NULL,
  `fecha_estimada` date NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ordenes`
--

INSERT INTO `ordenes` (`id`, `cliente`, `descripcion`, `estado`, `prioridad`, `fecha_estimada`, `creado_en`) VALUES
(5, 'prueba_1', 'xxxxxx', 'Pendiente', 'Media', '2025-07-08', '2025-07-11 23:27:58'),
(6, 'prueba_2', 'xxxxxx', 'Pendiente', 'Media', '2025-07-08', '2025-07-11 23:28:14'),
(9, 'prueba_3', 'asdfghjk', 'En proceso', 'Baja', '2025-07-31', '2025-07-12 00:20:04'),
(10, 'prueba_4', 'tyuiop', 'En proceso', 'Media', '2025-07-23', '2025-07-12 00:20:29'),
(11, 'prueba_5', 'sdfghjkliuygfc', 'En proceso', 'Media', '2025-07-26', '2025-07-12 00:20:49'),
(12, 'prueba_6', 'dfghjioiuh', 'Completado', 'Media', '2025-07-26', '2025-07-12 00:21:06');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ordenes`
--
ALTER TABLE `ordenes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ordenes`
--
ALTER TABLE `ordenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
