# Sistema de Balanceo de Solicitudes

Este proyecto es una API REST desarrollada en Laravel para la gestión y balanceo de solicitudes de usuarios, incluyendo control de carga, reportes, bitácora de movimientos y seguridad por timeout de sesión.

## 🚀 Características

- **Autenticación**: Login simple con control de inactividad de sesión (60 segundos).
- **Balanceo Automático**: Asignación de solicitudes al usuario con menor carga de trabajo en el año corriente.
- **Gestión de Solicitudes**: CRUD completo para solicitudes con opción de cancelación.
- **Reportes**: Generación de reportes de carga por usuario en formatos JSON, HTML y CSV.
- **Bitácora**: Registro automático de acciones importantes (Creación, Cancelación, Login, etc.).
- **Middleware Personalizado**: Control de sesión mediante headers personalizados (`X-User-Id`).

## 🛠️ Requisitos Técnicos

- PHP 8.2+
- Composer
- Base de Datos (MySQL/SQLite)
- Laravel 11.x

## 🏁 Instalación

1. Clonar el repositorio:
   ```bash
   git clone https://github.com/Fr0000st3r/balanceo_solicitudes.git
   ```
2. Instalar dependencias:
   ```bash
   composer install
   ```
3. Configurar el archivo `.env`:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. Ejecutar migraciones y seeders:
   ```bash
   php artisan migrate --seed
   ```
5. Iniciar el servidor:
   ```bash
   php artisan serve
6. Importar la colección de Postman incluida en el proyecto: `Balanceo_Solicitudes.postman_collection.json`.

## 🚀 Postman
Se incluye una colección de Postman en la raíz del proyecto para facilitar las pruebas de los endpoints. 
- Asegúrate de tener el servidor corriendo (`php artisan serve`).
- La colección usa una variable global `{{base_url}}` configurada por defecto en `http://localhost:8000`.
- Incluye ejemplos de cuerpos JSON para las peticiones POST y PUT.

## 📖 Documentación de la API

### 🔐 Autenticación
Todas las rutas (excepto login) requieren el header **`X-User-Id`** con el ID del usuario tras el login.

| Método | Ruta | Descripción |
| :--- | :--- | :--- |
| `POST` | `/api/login` | Inicia sesión y devuelve el `id_usuario`. |

**Cuerpo del Login:**
```json
{
    "login": "usuario1",
    "password": "password"
}
```

### 📋 Solicitudes
| Método | Ruta | Descripción |
| :--- | :--- | :--- |
| `POST` | `/api/solicitudes` | Crea una solicitud y la asigna automáticamente. |
| `GET` | `/api/solicitudes` | Lista todas las solicitudes (Paginado). |
| `GET` | `/api/solicitudes/{id}` | Detalle de una solicitud específica. |
| `PUT` | `/api/solicitudes/{id}/cancelar` | Cancela una solicitud y libera carga al usuario. |

### 📊 Reportes
| Método | Ruta | Descripción |
| :--- | :--- | :--- |
| `GET` | `/api/reportes/solicitudes-por-usuario` | Resumen de carga por usuario. |
| `GET` | `/api/reportes/solicitudes-por-usuario/export/html` | Descarga reporte en HTML. |
| `GET` | `/api/reportes/solicitudes-por-usuario/export/csv` | Descarga reporte en CSV. |

### ⚙️ Configuración de Carga
| Método | Ruta | Descripción |
| :--- | :--- | :--- |
| `GET` | `/api/configuracion-carga` | Lista las reglas de configuración. |
| `POST` | `/api/configuracion-carga` | Crea una nueva regla de proporción/diferencia. |

## 🛡️ Seguridad (Timeout)
La API implementa un Middleware de **Session Timeout**. 
- Si no hay actividad durante **60 segundos**, la sesión expira.
- El tiempo se renueva automáticamente con cada petición exitosa.
- Se utiliza el driver de `Cache` para el control de estos tiempos.

### ⚙️ Ajuste de Tiempo de Sesión
Para modificar el tiempo de inactividad permitido (actualmente 60 segundos), se debe actualizar el valor de la variable `$ttl` en los siguientes archivos:

1.  **Middleware**: `app/Http/Middleware/SessionTimeout.php`
    ```php
    $ttl = 60; // Cambiar por el tiempo deseado en segundos
    ```
2.  **Controlador de Autenticación**: `app/Http/Controllers/AuthController.php`
    ```php
    Cache::put("...", time(), 60); // Cambiar el último parámetro
    ```

## 📝 Bitácora
Cada acción relevante se registra en la tabla `tblbitacoras` mediante el `BitacoraService`, guardando el usuario, la acción, la fecha y el detalle del movimiento.
