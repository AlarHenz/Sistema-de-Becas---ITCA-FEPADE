USE sistemabecas;

-- 10. Crear la tabla de Estados de Solicitud
-- Define los estados por los que pasa una solicitud.
-- Incluye un campo de color para pintar las etiquetas en el frontend (Imagen 4 y 5).
CREATE TABLE estados_solicitud (
    id_estado INT AUTO_INCREMENT PRIMARY KEY,
    nombre_estado VARCHAR(50) NOT NULL UNIQUE,
    color_hex VARCHAR(7) DEFAULT '#CCCCCC', -- Código de color para la UI (ej: '#28A745' para verde)
    orden_paso INT NULL, -- Para ordenar el timeline (1, 2, 3, 4)
    descripcion VARCHAR(255) NULL
);

-- Insertamos los estados basados en las imágenes
INSERT INTO estados_solicitud (nombre_estado, color_hex, orden_paso, descripcion) VALUES 
('Borrador', '#808080', 0, 'Solicitud iniciada pero no enviada'),
('En revisión', '#FFC107', 3, 'Solicitud enviada, esperando evaluación'),
('Aprobada', '#28A745', 4, 'Beca otorgada al estudiante'),
('Rechazada', '#DC3545', 4, 'Solicitud denegada'),
('Documentos Pendientes', '#17A2B8', 2, 'Falta cargar o corregir documentos');

-- 11. Crear la tabla de Solicitudes (El núcleo del sistema)
CREATE TABLE solicitudes (
    id_solicitud INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Código único visible en el panel (Ej: BCA-2024-00123)
    codigo_solicitud VARCHAR(30) NOT NULL UNIQUE, 
    
    -- Relaciones principales
    id_estudiante INT NOT NULL,
    id_programa INT NOT NULL,
    id_estado_actual INT NOT NULL,
    
    -- Fechas de control
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Notas adicionales (opcional)
    comentarios_estudiante TEXT NULL,
    
    -- Llaves Foráneas
    CONSTRAINT fk_solicitudes_estudiante 
        FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id_estudiante)
        ON DELETE CASCADE, -- Si se borra el estudiante, se borran sus solicitudes
        
    CONSTRAINT fk_solicitudes_programa 
        FOREIGN KEY (id_programa) REFERENCES programas_beca(id_programa)
        ON UPDATE CASCADE 
        ON DELETE RESTRICT, -- No se puede borrar un programa si tiene solicitudes activas
        
    CONSTRAINT fk_solicitudes_estado 
        FOREIGN KEY (id_estado_actual) REFERENCES estados_solicitud(id_estado)
        ON UPDATE CASCADE 
        ON DELETE RESTRICT
);

-- Índices para acelerar las búsquedas en el panel de administración (Imagen 4)
CREATE INDEX idx_solicitudes_codigo ON solicitudes(codigo_solicitud);
CREATE INDEX idx_solicitudes_estado ON solicitudes(id_estado_actual);
CREATE INDEX idx_solicitudes_fecha ON solicitudes(fecha_solicitud);