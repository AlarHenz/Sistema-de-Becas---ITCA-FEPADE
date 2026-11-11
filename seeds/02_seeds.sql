-- ==========================================================
-- SISTEMA DE GESTIÓN DE BECAS - ITCA FEPADE
-- ARCHIVO: 02_seeds.sql
-- DESCRIPCIÓN: Inserción de datos iniciales (Usuarios, Becas, etc.)
-- ==========================================================

USE sistema_becas_itca;

-- 1. Insertar Usuarios (Administrador y Estudiante)
-- NOTA: Las contraseñas están hasheadas con BCRYPT. 
-- Contraseña para ambos: "123456"
INSERT INTO usuarios (carnet, nombre_completo, correo_institucional, correo_personal, telefono_movil, password, rol) VALUES 
('ADMIN001', 'Administrador del Sistema', 'admin@itca.edu.sv', 'admin@gmail.com', '7777-8888', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JuywdB7V5X5X5X5X5X5X', 'administrador'),
('2024001', 'María José López', 'mlopez@itca.edu.sv', 'maria@gmail.com', '7123-4567', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JuywdB7V5X5X5X5X5X5X', 'estudiante');

-- 2. Insertar Becas
INSERT INTO becas (nombre_beca, descripcion) VALUES 
('Fundación Gloria de Kriete', 'Beca completa para estudiantes destacados.'),
('RRHH', 'Beca otorgada por Recursos Humanos de ITCA.'),
('Alcaldía Santa Ana Norte', 'Beca municipal para residentes de la zona norte.'),
('Integración', 'Beca para fomentar la integración estudiantil.'),
('Asamblea Legislativa', 'Beca de excelencia académica.');

-- 3. Insertar Instituciones
INSERT INTO instituciones (nombre_institucion) VALUES 
('ITCA FEPADE - Santa Ana'),
('ITCA FEPADE - San Salvador'),
('ITCA FEPADE - San Miguel');

-- 4. Insertar Especialidades (Asociadas a la Institución 1)
INSERT INTO especialidades (id_institucion, nombre_especialidad) VALUES 
(1, 'Ingeniería en Sistemas'),
(1, 'Técnico en Mecatrónica'),
(1, 'Administración de Empresas'),
(1, 'Ingeniería Civil'),
(1, 'Técnico en Logística'),
(1, 'Ingeniería Eléctrica'),
(1, 'Técnico en Turismo'),
(1, 'Bachiller General'),
(1, 'Bachiller en Ciencias'),
(1, 'Letras y Matemática');

-- 5. Insertar una Solicitud de prueba (para el estudiante María José)
-- Nota: Como el id_usuario de María es 2, y la especialidad 1 es Ingeniería en Sistemas.
INSERT INTO solicitudes (id_usuario, id_especialidad, estado, prioridad, fecha_solicitud) VALUES 
(2, 1, 'en_revision', 'alta', NOW());

-- 6. Insertar una Notificación de prueba
INSERT INTO notificaciones (id_usuario, id_solicitud, mensaje) VALUES 
(2, 1, 'Tu solicitud está en revisión. Por favor, verifica que tu DUI sea legible.');
