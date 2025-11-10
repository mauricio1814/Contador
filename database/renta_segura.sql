-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3307
-- Tiempo de generación: 23-10-2025 a las 21:26:40
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
-- Base de datos: `renta_segura`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `cargo` varchar(100) DEFAULT 'Administrador del sistema',
  `permisos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '["gestionarUsuarios", "gestionarContadores", "generarReportes", "verDeclaraciones"]' CHECK (json_valid(`permisos`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contador`
--

CREATE TABLE `contador` (
  `id_contador` int(11) NOT NULL,
  `tarjeta_profesional` varchar(50) DEFAULT NULL,
  `especialidad` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `declaracion`
--

CREATE TABLE `declaracion` (
  `id_declaracion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_contador` int(11) DEFAULT NULL,
  `anio_fiscal` year(4) NOT NULL,
  `estado` enum('pendiente','en revisión','aprobada','rechazada') DEFAULT 'pendiente',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_ingresos` decimal(15,2) DEFAULT 0.00,
  `total_deducciones` decimal(15,2) DEFAULT 0.00,
  `impuesto_calculado` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos_soporte`
--

CREATE TABLE `documentos_soporte` (
  `id_documento` int(11) NOT NULL,
  `id_declaracion` int(11) NOT NULL,
  `tipo_documento` varchar(100) DEFAULT NULL,
  `url_documento` varchar(255) DEFAULT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingreso`
--

CREATE TABLE `ingreso` (
  `id_ingreso` int(11) NOT NULL,
  `id_declaracion` int(11) NOT NULL,
  `tipo_ingreso` varchar(100) DEFAULT NULL,
  `valor` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion`
--

CREATE TABLE `notificacion` (
  `id_notificacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `mensaje` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('leída','no leída') DEFAULT 'no leída'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago`
--

CREATE TABLE `pago` (
  `id_pago` int(11) NOT NULL,
  `id_declaracion` int(11) NOT NULL,
  `fecha_pago` date DEFAULT NULL,
  `monto` decimal(15,2) DEFAULT NULL,
  `metodo_pago` enum('transferencia','tarjeta','efectivo') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitud_contador`
--

CREATE TABLE `solicitud_contador` (
  `id_solicitud` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tarjeta_profesional` varchar(50) DEFAULT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `documento_certificacion` varchar(255) DEFAULT NULL,
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','aprobada','rechazada') DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `tipo_documento` varchar(50) DEFAULT NULL,
  `numero_documento` varchar(50) DEFAULT NULL,
  `correo` varchar(150) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `rol` enum('usuario','contador','admin') DEFAULT 'usuario',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellido`, `tipo_documento`, `numero_documento`, `correo`, `contrasena`, `telefono`, `rol`, `fecha_creacion`) VALUES
(1, 'Wilson', 'Tovar', 'CC', '1234567890', 'wilson.tovar@example.com', '$2y$10$E2wUx.kZgW7QW14mriJP.eeB0wc/1.mywm6LJG73NY7mbS6Dusv2e', '3001234567', 'usuario', '2025-10-23 17:25:49'),
(2, 'Admin', 'Principal', 'CC', '1000000000', 'admin@admin.com', '$2y$10$IfwI3XJZpM1j8p4QZbZSzOZL5eJXZCJZzMjZC93SCTByV.rXPwKcK', '3000000000', 'admin', '2025-10-23 19:05:20');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indices de la tabla `contador`
--
ALTER TABLE `contador`
  ADD PRIMARY KEY (`id_contador`);

--
-- Indices de la tabla `declaracion`
--
ALTER TABLE `declaracion`
  ADD PRIMARY KEY (`id_declaracion`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_contador` (`id_contador`);

--
-- Indices de la tabla `documentos_soporte`
--
ALTER TABLE `documentos_soporte`
  ADD PRIMARY KEY (`id_documento`),
  ADD KEY `id_declaracion` (`id_declaracion`);

--
-- Indices de la tabla `ingreso`
--
ALTER TABLE `ingreso`
  ADD PRIMARY KEY (`id_ingreso`),
  ADD KEY `id_declaracion` (`id_declaracion`);

--
-- Indices de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `pago`
--
ALTER TABLE `pago`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `id_declaracion` (`id_declaracion`);

--
-- Indices de la tabla `solicitud_contador`
--
ALTER TABLE `solicitud_contador`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `numero_documento` (`numero_documento`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `declaracion`
--
ALTER TABLE `declaracion`
  MODIFY `id_declaracion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `documentos_soporte`
--
ALTER TABLE `documentos_soporte`
  MODIFY `id_documento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ingreso`
--
ALTER TABLE `ingreso`
  MODIFY `id_ingreso` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pago`
--
ALTER TABLE `pago`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `solicitud_contador`
--
ALTER TABLE `solicitud_contador`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `contador`
--
ALTER TABLE `contador`
  ADD CONSTRAINT `contador_ibfk_1` FOREIGN KEY (`id_contador`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `declaracion`
--
ALTER TABLE `declaracion`
  ADD CONSTRAINT `declaracion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `declaracion_ibfk_2` FOREIGN KEY (`id_contador`) REFERENCES `contador` (`id_contador`) ON DELETE SET NULL;

--
-- Filtros para la tabla `documentos_soporte`
--
ALTER TABLE `documentos_soporte`
  ADD CONSTRAINT `documentos_soporte_ibfk_1` FOREIGN KEY (`id_declaracion`) REFERENCES `declaracion` (`id_declaracion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ingreso`
--
ALTER TABLE `ingreso`
  ADD CONSTRAINT `ingreso_ibfk_1` FOREIGN KEY (`id_declaracion`) REFERENCES `declaracion` (`id_declaracion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD CONSTRAINT `notificacion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pago`
--
ALTER TABLE `pago`
  ADD CONSTRAINT `pago_ibfk_1` FOREIGN KEY (`id_declaracion`) REFERENCES `declaracion` (`id_declaracion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitud_contador`
--
ALTER TABLE `solicitud_contador`
  ADD CONSTRAINT `solicitud_contador_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
