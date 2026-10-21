USE sistemabecas;

-- 7. Crear la tabla de Programas de Beca
-- Define las convocatorias o tipos de becas disponibles.
CREATE TABLE programas_beca (
    id_programa INT AUTO_INCREMENT PRIMARY KEY,
    nombre_programa VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    
    -- Datos financieros y de vigencia
    monto DECIMAL(10,2) NULL, -- Monto de la beca (opcional, por si es porcentaje)
    cupos_disponibles INT DEFAULT 0, -- Cuántas becas se pueden otorgar
    
    -- Fechas de convocatoria (útiles para el administrador)
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    
    -- Estado del programa
    estado ENUM('Activa', 'Cerrada', 'En Planificación') DEFAULT 'Activa',
    
    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertamos un par de ejemplos de programas
INSERT INTO programas_beca (nombre_programa, descripcion, monto, cupos_disponibles, fecha_inicio, fecha_fin) VALUES 
('Beca de Excelencia Académica', 'Dirigida a estudiantes con promedio mayor a 8.5', 500.00, 50, '2024-01-01', '2024-12-31'),
('Beca de Apoyo Socioeconómico', 'Para estudiantes con dificultades económicas comprobables', 350.00, 100, '2024-01-01', '2024-12-31');

-- 8. Crear la tabla de Requisitos (Catálogo de documentos)
-- Es un catálogo maestro de todos los documentos que el sistema puede pedir.
CREATE TABLE requisitos (
    id_requisito INT AUTO_INCREMENT PRIMARY KEY,
    nombre_documento VARCHAR(100) NOT NULL UNIQUE, -- Ej: 'DUI', 'Título de Bachiller'
    descripcion VARCHAR(255) NULL,
    
    -- Validaciones para la subida de archivos (Imagen 3)
    formatos_permitidos VARCHAR(100) DEFAULT 'JPG, PNG, PDF',
    tamano_maximo_mb INT DEFAULT 5,
    
    es_obligatorio BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertamos los requisitos típicos
INSERT INTO requisitos (nombre_documento, descripcion, formatos_permitidos, tamano_maximo_mb, es_obligatorio) VALUES 
('Documento Único de Identidad (DUI)', 'Copia legible del DUI por ambos lados', 'JPG, PNG, PDF', 5, TRUE),
('Título de Bachiller', 'Título original o copia certificada', 'PDF', 5, TRUE),
('Boleta de Notas', 'Notas del último año cursado', 'PDF', 5, TRUE),
('Comprobante de Ingresos', 'Recibo de luz o agua (opcional según la beca)', 'JPG, PNG, PDF', 5, FALSE);

-- 9. Tabla Intermedia: Requisitos por Programa de Beca
-- Esto permite que cada beca tenga sus propios requisitos.
-- Ejemplo: La "Beca de Excelencia" quizás no pida comprobante de ingresos, pero la "Socioeconómica" sí.
CREATE TABLE programa_requisitos (
    id_programa INT NOT NULL,
    id_requisito INT NOT NULL,
    
    -- Llave primaria compuesta (evita duplicados)
    PRIMARY KEY (id_programa, id_requisito),
    
    -- Llaves foráneas
    CONSTRAINT fk_prog_req_programa 
        FOREIGN KEY (id_programa) REFERENCES programas_beca(id_programa)
        ON DELETE CASCADE, -- Si se borra la beca, se borran sus reglas de requisitos
        
    CONSTRAINT fk_prog_req_requisito 
        FOREIGN KEY (id_requisito) REFERENCES requisitos(id_requisito)
        ON DELETE CASCADE
);