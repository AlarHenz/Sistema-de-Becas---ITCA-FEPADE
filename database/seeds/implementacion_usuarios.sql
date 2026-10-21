-- ==========================================================
-- SCRIPT DE SEEDS (DATOS DE PRUEBA)
-- Proyecto: Sistema de Gestión de Becas - ITCA FEPADE
-- Propósito: Poblar la BD para probar el Login y el Dashboard
-- ==========================================================

USE sistemabecas;

-- ----------------------------------------------------------
-- 1. Insertar Roles (Necesario para el Login)
-- ----------------------------------------------------------
INSERT INTO roles (id_rol, nombre_rol, descripcion) VALUES 
(1, 'Estudiante', 'Usuario que solicita y da seguimiento a becas'),
(2, 'Administrador', 'Usuario que gestiona expedientes, evaluaciones y reportes')
ON DUPLICATE KEY UPDATE nombre_rol = VALUES(nombre_rol);

-- ----------------------------------------------------------
-- 2. Insertar Carreras (Necesario para el perfil del estudiante)
-- ----------------------------------------------------------
INSERT INTO carreras (id_carrera, nombre_carrera, estado) VALUES 
(1, 'Ingeniería en Sistemas', 'Activa'),
(2, 'Técnico en Mecatrónica', 'Activa'),
(3, 'Administración de Empresas', 'Activa'),
(4, 'Ingeniería Civil', 'Activa')
ON DUPLICATE KEY UPDATE nombre_carrera = VALUES(nombre_carrera);

-- ----------------------------------------------------------
-- 3. Insertar Usuarios (El core para probar el Login)
-- NOTA: El hash corresponde a la contraseña: password123
-- ----------------------------------------------------------
INSERT INTO usuarios (id_usuario, id_rol, carnet, correo_electronico, password_hash, estado) VALUES 
-- Estudiante de prueba (usado en las imágenes)
(1, 1, '20240001', 'mariajose.lopez@itca.edu.sv', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JuywdB1T.q8p2Wq.nM3S', 'Activo'),
-- Administrador de prueba (usado en las imágenes)
(2, 2, 'ADMIN001', 'admin@itca.edu.sv', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JuywdB1T.q8p2Wq.nM3S', 'Activo'),
-- Estudiante extra para pruebas
(3, 1, '20240002', 'carlos.garcia@itca.edu.sv', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JuywdB1T.q8p2Wq.nM3S', 'Activo')
ON DUPLICATE KEY UPDATE correo_electronico = VALUES(correo_electronico);

-- ----------------------------------------------------------
-- 4. Insertar Perfiles de Estudiantes
-- NOTA: Solo se insertan para los usuarios con id_rol = 1 (Estudiantes)
-- ----------------------------------------------------------
INSERT INTO estudiantes (id_estudiante, id_usuario, id_carrera, nombres, apellidos, dui, fecha_nacimiento, telefono) VALUES 
(1, 1, 1, 'María José', 'López Hernández', '12345678-9', '2004-03-15', '7123-4567'),
(2, 3, 2, 'Carlos Eduardo', 'García', '98765432-1', '2003-08-22', '7890-1234')
ON DUPLICATE KEY UPDATE nombres = VALUES(nombres);

-- ==========================================================
-- CREDENCIALES DE PRUEBA PARA EL EQUIPO:
-- 
-- 👤 ESTUDIANTE:    Carnet: 20240001 | Pass: password123
-- 👑 ADMINISTRADOR: Carnet: ADMIN001 | Pass: password123
-- 👤 ESTUDIANTE 2:  Carnet: 20240002 | Pass: password123
-- ==========================================================