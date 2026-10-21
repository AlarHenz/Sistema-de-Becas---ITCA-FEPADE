# Sistema de Gestión de Becas - ITCA FEPADE

> **Proyecto Final** de la materia **"Aplicación de Metodologías Ágiles y Control de la Calidad del Software"**.
> *Remodelación y modernización del sitio web institucional de Becas del ITCA-FEPADE.*

![Estado del Proyecto](https://img.shields.io/badge/Estado-En%20Desarrollo-yellow)
![Versión](https://img.shields.io/badge/Versión-1.0.0-blue)
![Licencia](https://img.shields.io/badge/Licencia-Académica-lightgrey)

---

## Descripción del Proyecto

El **Sistema de Gestión de Becas** es una plataforma web institucional diseñada para digitalizar, agilizar y transparentar el proceso de solicitud, revisión y aprobación de becas estudiantiles en el **ITCA-FEPADE**.

Esta versión representa una **remodelación completa** de la interfaz y la arquitectura del sitio anterior, incorporando un diseño moderno (UI/UX), un flujo de trabajo claro por etapas (*timeline*) y un panel administrativo robusto para la gestión de expedientes y la generación de reportes estadísticos.

### Características Principales

*   **Portal del Estudiante:**
    *   Inicio de sesión seguro mediante carnet institucional.
    *   Dashboard con *timeline* visual del estado de la solicitud (Registro → Datos → Documentos → Revisión).
    *   Formulario de solicitud dividido por pasos.
    *   **Sistema de Borradores:** Permite guardar múltiples solicitudes incompletas antes de enviarlas formalmente.
    *   Carga de documentos validada (formatos JPG, PNG, PDF, máx. 5MB).
    *   Panel de notificaciones en tiempo real.
*   **Panel del Administrador:**
    *   Gestión de Expedientes con filtros avanzados (por nombre, correo, estado y carrera).
    *   Validación de documentos adjuntos por parte del administrador.
    *   Generación de Reportes estadísticos (Aprobadas, En revisión, Borradores, Rechazadas) con opción de exportación a **PDF** y **Excel**.
    *   Auditoría interna mediante bitácora de cambios de estado.

---

## Stack Tecnológico (Sugerido)

*   **Frontend:** React / Angular / Vue.js (a elección del equipo) + Tailwind CSS o Bootstrap.
*   **Backend:** Node.js (Express) / PHP (Laravel) / Python (Django).
*   **Base de Datos:** MySQL / MariaDB.
*   **Control de Versiones:** Git y GitHub.

---

## Arquitectura de la Base de Datos

El sistema cuenta con un diseño relacional normalizado, diseñado para soportar la escalabilidad (ej. múltiples borradores por estudiante). El esquema se divide en los siguientes módulos lógicos:

1.  **Seguridad:** `roles`, `usuarios`.
2.  **Académico:** `carreras`, `estudiantes`.
3.  **Catálogo de Becas:** `programas_beca`, `requisitos`, `programa_requisitos`.
4.  **Núcleo:** `estados_solicitud`, `solicitudes`, `borradores`.
5.  **Documentación:** `documentos_solicitud`.
6.  **Seguimiento:** `historial_solicitud`, `notificaciones`.

### Diagrama Entidad-Relación (DER) - Resumen
*   Un **Usuario** tiene un **Rol**.
*   Un **Estudiante** (perfil del usuario) crea múltiples **Solicitudes** y **Borradores**.
*   Cada **Solicitud** pertenece a un **Programa de Beca** y tiene un **Estado** actual.
*   Las **Solicitudes** acumulan **Documentos** y registros en el **Historial**.

---

## Instalación y Configuración Local

Sigue estos pasos para ejecutar el proyecto en tu entorno local:

### 1. Clonar el repositorio
```bash
git clone https://github.com/TU-USUARIO/sistema-becas-itca.git
cd sistema-becas-itca
