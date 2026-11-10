USE sistemabecas;

-- 5. Crear la tabla de Carreras
-- Necesaria para clasificar a los estudiantes y para los filtros del administrador.
CREATE TABLE carreras (
    id_carrera INT AUTO_INCREMENT PRIMARY KEY,
    nombre_carrera VARCHAR(150) NOT NULL UNIQUE,
    estado ENUM('Activa', 'Inactiva') DEFAULT 'Activa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertamos algunas carreras de ejemplo basadas en la Imagen 4
INSERT INTO carreras (nombre_carrera) VALUES 
('Ingeniería en Sistemas'),
('Técnico en Mecatrónica'),
('Administración de Empresas'),
('Ingeniería Civil'),
('Técnico en Logística'),
('Ingeniería Eléctrica'),
('Técnico en Turismo');

-- 6. Crear la tabla de Estudiantes
-- Esta tabla extiende la información del usuario. 
-- Relación 1 a 1: Un usuario con rol "Estudiante" tiene exactamente un registro aquí.
CREATE TABLE estudiantes (
    id_estudiante INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL UNIQUE, -- UNIQUE asegura la relación 1 a 1
    id_carrera INT NOT NULL,
    
    -- Datos personales (Imagen 3)
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    dui VARCHAR(10) NOT NULL UNIQUE, -- Formato: 12345678-9
    fecha_nacimiento DATE NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    
    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Llaves Foráneas
    CONSTRAINT fk_estudiantes_usuarios 
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE, -- Si se borra el usuario, se borra su perfil de estudiante
        
    CONSTRAINT fk_estudiantes_carreras 
        FOREIGN KEY (id_carrera) REFERENCES carreras(id_carrera)
        ON UPDATE CASCADE 
        ON DELETE RESTRICT
);

-- Índices para búsquedas rápidas en el panel de administración (Imagen 4)
CREATE INDEX idx_estudiantes_dui ON estudiantes(dui);
CREATE INDEX idx_estudiantes_apellidos ON estudiantes(apellidos);