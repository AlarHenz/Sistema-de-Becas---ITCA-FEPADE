USE sistemabecas;

-- 15. Crear la tabla de Borradores (Solicitudes Incompletas)
-- Permite a los estudiantes guardar múltiples intentos o versiones de una solicitud
-- antes de enviarla formalmente.
CREATE TABLE borradores (
    id_borrador INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Relaciones
    id_estudiante INT NOT NULL,
    id_programa INT NOT NULL, -- ¿Para qué beca es este borrador?
    
    -- Datos parciales en formato JSON
    -- En lugar de crear 50 columnas que estarán vacías, guardamos lo que el estudiante
    -- haya llenado parcialmente como un objeto JSON.
    datos_json JSON NULL, 
    
    -- Control de progreso (Opcional, para la barra de progreso del frontend)
    paso_actual INT DEFAULT 1, -- 1: Datos personales, 2: Académicos, 3: Documentos
    
    -- Auditoría
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Llaves Foráneas
    CONSTRAINT fk_borradores_estudiante 
        FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id_estudiante)
        ON DELETE CASCADE, -- Si se borra el estudiante, se borran sus borradores
        
    CONSTRAINT fk_borradores_programa 
        FOREIGN KEY (id_programa) REFERENCES programas_beca(id_programa)
        ON DELETE CASCADE
);

-- Índice para que el estudiante vea rápido sus borradores al entrar al sistema
CREATE INDEX idx_borradores_estudiante ON borradores(id_estudiante);