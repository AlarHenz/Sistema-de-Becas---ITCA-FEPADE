# Sistema de Gestión de Becas - ITCA FEPADE

Sistema web funcional desarrollado para la gestión de solicitudes de becas de la institución **ITCA FEPADE**. Permite a los estudiantes postularse a una beca y a los administradores gestionar las solicitudes, los becados y los catálogos del sistema.

---

## Tecnologías Utilizadas

*   **Backend:** PHP (Nativo, con PDO para la base de datos).
*   **Base de Datos:** MySQL (Gestionada con phpMyAdmin).
*   **Frontend:** HTML5, CSS3 y JavaScript (Vanilla).
*   **Entorno Local:** XAMPP (Apache + MySQL).
*   **Estilos:** Paleta de colores institucional (Blanco, Rojo y Amarillo).

---

## Estructura del Proyecto

```text
Sistema-de-Becas---ITCA-FEPADE/
│
├── config/
│   └── database.php           # Conexión a la BD (PDO)
│
├── migrations/
│   └── 01_migrations.sql      # Script de creación de tablas
│
├── seeds/
│   └── 02_seeds.sql           # Script de datos iniciales (Usuarios, Becas, etc.)
│
├── public/
│   ├── index.php              # Punto de entrada / Redirección
│   ├── login.php              # Formulario de Login
│   ├── logout.php             # Cerrar sesión
│   ├── css/
│   │   └── style.css          # Estilos globales
│   ├── uploads/               # Carpeta para archivos subidos por estudiantes
│   ├── admin/                 # Vistas del Administrador
│   └── estudiante/            # Vistas del Estudiante
│
├── includes/                  # Archivos reutilizables (auth, header, sidebar)
│
└── README.md                  # Este archivo
