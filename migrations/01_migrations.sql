-- ==========================================================
-- SISTEMA DE GESTIÓN DE BECAS - ITCA FEPADE
-- ARCHIVO: 01_migrations.sql
-- DESCRIPCIÓN: Creación de la base de datos y tablas
-- ==========================================================

-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS sistema_becas_itca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_becas_itca;

-- 1. Tabla de Usuarios (Administradores y Estudiantes)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    carnet VARCHAR(20) NOT NULL UNIQUE, -- Carnet para login (ej: 2024001 o ADMIN001)
    nombre_completo VARCHAR(150) NOT NULL,
    correo_institucional VARCHAR(100) NOT NULL UNIQUE,
    correo_personal VARCHAR(100) NULL,
    telefono_fijo VARCHAR(15) NULL,
    telefono_movil VARCHAR(15) NULL,
    password VARCHAR(255) NOT NULL, -- Se almacenará hasheado con password_hash()
    rol ENUM('administrador', 'estudiante') NOT NULL DEFAULT 'estudiante',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabla de Becas (Catálogo)
CREATE TABLE IF NOT EXISTS becas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_beca VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    estado ENUM('activa', 'inactiva') NOT NULL DEFAULT 'activa',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabla de Instituciones
CREATE TABLE IF NOT EXISTS instituciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_institucion VARCHAR(150) NOT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1
);

-- 4. Tabla de Especialidades (Relacionadas con Instituciones)
CREATE TABLE IF NOT EXISTS especialidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_institucion INT NOT NULL,
    nombre_especialidad VARCHAR(150) NOT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (id_institucion) REFERENCES instituciones(id) ON DELETE CASCADE
);

-- 5. Tabla de Solicitudes (El corazón del sistema)
CREATE TABLE IF NOT EXISTS solicitudes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL, -- Estudiante que solicita
    id_beca INT NULL, -- Beca asignada (si aplica)
    id_especialidad INT NULL, -- Especialidad de interés
    estado ENUM('borrador', 'en_revision', 'aprobada', 'rechazada', 'incompleta') NOT NULL DEFAULT 'borrador',
    prioridad ENUM('alta', 'media', 'baja') DEFAULT 'media',
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    observaciones_admin TEXT NULL, -- Para notificaciones de error (HU-12)
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_beca) REFERENCES becas(id) ON DELETE SET NULL,
    FOREIGN KEY (id_especialidad) REFERENCES especialidades(id) ON DELETE SET NULL
);

-- 6. Tabla de Documentos (Adjuntos de la solicitud)
CREATE TABLE IF NOT EXISTS documentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_solicitud INT NOT NULL,
    tipo_documento ENUM('dui', 'notas', 'constancia', 'otro') NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    estado_documento ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_solicitud) REFERENCES solicitudes(id) ON DELETE CASCADE
);

-- 7. Tabla de Notificaciones (Para el Dashboard del Estudiante - HU-12)
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_solicitud INT NULL,
    mensaje TEXT NOT NULL,
    leida TINYINT(1) DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_solicitud) REFERENCES solicitudes(id) ON DELETE CASCADE
);
