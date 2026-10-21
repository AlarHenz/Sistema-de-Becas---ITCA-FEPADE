USE sistemabecas;

-- 12. Crear la tabla de Documentos de Solicitud
-- Almacena los archivos que el estudiante sube para una solicitud específica.
CREATE TABLE documentos_solicitud (
    id_documento INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Relaciones
    id_solicitud INT NOT NULL,
    id_requisito INT NOT NULL, -- Para saber si es el DUI, el Título, etc.
    
    -- Datos del archivo físico
    nombre_original VARCHAR(255) NOT NULL, -- Ej: 'mi_dui_frente.pdf'
    ruta_archivo VARCHAR(500) NOT NULL,    -- Ej: '/uploads/2024/solicitud_123/dui.pdf'
    tipo_mime VARCHAR(100) NULL,           -- Ej: 'application/pdf', 'image/jpeg'
    tamano_kb INT NULL,                    -- Tamaño en Kilobytes para validar el límite
    
    -- Control de validación (Para el administrador)
    estado_validacion ENUM('Pendiente', 'Aprobado', 'Rechazado') DEFAULT 'Pendiente',
    observaciones_admin TEXT NULL, -- Ej: "La imagen está borrosa, favor subir de nuevo"
    
    -- Auditoría
    fecha_carga TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Llaves Foráneas
    CONSTRAINT fk_docs_solicitud 
        FOREIGN KEY (id_solicitud) REFERENCES solicitudes(id_solicitud)
        ON DELETE CASCADE, -- Si se borra la solicitud, se borran sus documentos
        
    CONSTRAINT fk_docs_requisito 
        FOREIGN KEY (id_requisito) REFERENCES requisitos(id_requisito)
        ON UPDATE CASCADE 
        ON DELETE RESTRICT
);

-- Índices para búsquedas rápidas
CREATE INDEX idx_docs_solicitud ON documentos_solicitud(id_solicitud);
CREATE INDEX idx_docs_validacion ON documentos_solicitud(estado_validacion);