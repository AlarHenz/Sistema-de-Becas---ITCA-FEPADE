USE sistemabecas;

-- 13. Crear la tabla de Historial de Solicitudes (Bitácora de cambios)
-- Registra cada vez que una solicitud cambia de estado.
CREATE TABLE historial_solicitud (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Relaciones
    id_solicitud INT NOT NULL,
    id_estado_anterior INT NULL, -- Puede ser NULL si es la primera vez que se crea la solicitud
    id_estado_nuevo INT NOT NULL,
    
    -- ¿Quién hizo el cambio?
    id_usuario_accion INT NOT NULL, -- Puede ser el estudiante o el administrador
    
    -- Detalles del cambio
    comentario TEXT NULL, -- Ej: "Documentos verificados correctamente" o "Falta firma en el DUI"
    
    -- Auditoría
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Llaves Foráneas
    CONSTRAINT fk_historial_solicitud 
        FOREIGN KEY (id_solicitud) REFERENCES solicitudes(id_solicitud)
        ON DELETE CASCADE,
        
    CONSTRAINT fk_historial_estado_ant 
        FOREIGN KEY (id_estado_anterior) REFERENCES estados_solicitud(id_estado)
        ON DELETE SET NULL, -- Si se borra un estado del catálogo, no rompe el historial
        
    CONSTRAINT fk_historial_estado_nuevo 
        FOREIGN KEY (id_estado_nuevo) REFERENCES estados_solicitud(id_estado)
        ON UPDATE CASCADE 
        ON DELETE RESTRICT,
        
    CONSTRAINT fk_historial_usuario 
        FOREIGN KEY (id_usuario_accion) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE 
        ON DELETE RESTRICT
);

-- Índices para que el timeline cargue rápido
CREATE INDEX idx_historial_solicitud ON historial_solicitud(id_solicitud);
CREATE INDEX idx_historial_fecha ON historial_solicitud(fecha_cambio);


-- 14. Crear la tabla de Notificaciones
-- Para el panel derecho de la Imagen 2 ("Notificaciones de estado")
CREATE TABLE notificaciones (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    
    -- ¿A quién va dirigida la notificación?
    id_usuario INT NOT NULL, 
    
    -- Contenido
    titulo VARCHAR(100) NOT NULL, -- Ej: "Beca Aprobada" o "Estado de Beca ha cambiado"
    mensaje TEXT NOT NULL,        -- Ej: "Tu solicitud ha sido aprobada."
    
    -- ¿A qué solicitud hace referencia? (Opcional, por si es un aviso general)
    id_solicitud INT NULL,
    
    -- Estado de lectura
    leida BOOLEAN DEFAULT FALSE,
    
    -- Auditoría
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Llaves Foráneas
    CONSTRAINT fk_notificaciones_usuario 
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,
        
    CONSTRAINT fk_notificaciones_solicitud 
        FOREIGN KEY (id_solicitud) REFERENCES solicitudes(id_solicitud)
        ON DELETE CASCADE
);

-- Índice para cargar rápidamente las notificaciones no leídas de un usuario
CREATE INDEX idx_notificaciones_usuario_leidas ON notificaciones(id_usuario, leida);