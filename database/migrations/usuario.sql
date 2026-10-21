-- 1. Crear la base de datos si no existe y seleccionarla
CREATE DATABASE IF NOT EXISTS sistemabecas 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE sistemabecas;

-- 2. Crear la tabla de Roles
-- Es importante crearla primero porque la tabla Usuarios dependerá de ella.
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar los roles básicos que vimos en las imágenes (Estudiante y Administrador)
INSERT INTO roles (nombre_rol, descripcion) VALUES 
('Estudiante', 'Usuario que solicita y da seguimiento a becas'),
('Administrador', 'Usuario que gestiona expedientes, evaluaciones y reportes');

-- 3. Crear la tabla de Usuarios
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_rol INT NOT NULL,
    
    -- Campo para el login (según la imagen 1, se usa el carnet)
    carnet VARCHAR(20) NOT NULL UNIQUE, 
    
    -- Datos de contacto
    correo_electronico VARCHAR(100) NOT NULL UNIQUE,
    
    -- Seguridad
    password_hash VARCHAR(255) NOT NULL, -- Aquí se guarda la contraseña encriptada (ej. bcrypt)
    
    -- Estado de la cuenta
    estado ENUM('Activo', 'Inactivo', 'Bloqueado') DEFAULT 'Activo',
    
    -- Auditoría
    ultimo_acceso DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Definición de la Llave Foránea
    CONSTRAINT fk_usuarios_roles 
        FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
        ON UPDATE CASCADE 
        ON DELETE RESTRICT -- Evita que se borre un rol si tiene usuarios asignados
);

-- 4. Crear Índices (Opcional pero muy recomendado)
-- Aunque 'carnet' y 'correo' ya son UNIQUE (lo que crea un índice automáticamente),
-- podemos crear un índice explícito para búsquedas por estado (ej. listar solo usuarios activos)
CREATE INDEX idx_usuarios_estado ON usuarios(estado);