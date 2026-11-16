# Sistema de Gestión de Becas - ITCA FEPADE

Sistema para gestionar solicitudes de becas de estudiantes y las tareas de administración.

## Tecnologías

- **Interfaz:** React, Vite, HTML y CSS.
- **API y sesiones:** PHP 8.2 o posterior, PDO.
- **Base de datos:** MySQL.
- **Servidor local:** XAMPP (Apache y MySQL).
- **Desarrollo de React:** Node.js 22.12+ o 24 LTS y npm.

React contiene las pantallas y consume la API PHP en `public/api/index.php`. La API conserva las sesiones existentes de PHP y accede a MySQL; la estructura de la base de datos no cambia.

## Preparar el entorno local

1. Inicia Apache y MySQL desde el panel de XAMPP.
2. Crea la base de datos ejecutando `migrations/01_migrations.sql` y, si necesitas datos de ejemplo, ejecuta `seeds/02_seeds.sql`.
3. Instala Node.js 24 LTS.
4. Desde la carpeta `frontend`, instala los paquetes y ejecuta el servidor de desarrollo:

   ```powershell
   cd frontend
   npm install
   npm run dev
   ```

5. Abre la URL que muestra Vite (por defecto `http://localhost:5173`). Las llamadas `/api` se envían a la instalación local de XAMPP.

Si Apache está configurado en un puerto distinto del 80, actualiza el destino del proxy en `frontend/vite.config.js`.

## Publicar/servir con Apache de XAMPP

Genera los archivos estáticos de React:

```powershell
cd frontend
npm install
npm run build
```

Vite coloca el resultado en `public/app`. Después abre la carpeta `public` del proyecto mediante Apache; la página de entrada redirige a la aplicación. Node.js solo se necesita para desarrollo y compilación, no para servir la aplicación compilada.

## Funcionalidades migradas

- Inicio/cierre de sesión y control de acceso por rol.
- Panel y gestión de solicitudes: filtros, estados y observaciones/notificaciones.
- Selección de aspirantes y asignación de becas.
- Catálogo de becas e instituciones/especialidades.
- Listado de becarios, exportación CSV y generación de lista de correos.
- Importación masiva CSV y descarga de plantilla.
- Portal del estudiante, solicitud/borrador y subida/visualización del DUI.

Las rutas PHP antiguas redirigen a las pantallas correspondientes de React para mantener compatibles los enlaces existentes.

## Estructura principal

```text
config/                 Conexión PDO a MySQL
frontend/               Aplicación React y configuración Vite
migrations/             Esquema de la base de datos
public/api/              Endpoints PHP consumidos por React
public/app/               Archivos generados por npm run build
public/css/style.css      Estilos institucionales compartidos
seeds/                    Datos iniciales de ejemplo
```
