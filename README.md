# Servicio de Mensajería Instantánea

Hemos desarrollado una aplicación web de chat completa utilizando **PHP nativo**, **MySQL** y **Docker**. Nuestro objetivo ha sido crear una plataforma segura, robusta y con un diseño moderno centrado en la usabilidad. El proyecto destaca por su interfaz "Glassmorphism" y una arquitectura limpia que separa la lógica de negocio de la presentación.

## 🚀 Instrucciones de Instalación y Ejecución

El proyecto se encuentra totalmente contenerizado para facilitar su despliegue en cualquier entorno Linux compatible con Docker.

1.  **Requisitos**: Tener instalado `docker` y `docker compose`.
2.  **Despliegue**:
    Desde la carpeta raíz del proyecto, ejecutamos el siguiente comando:
    ```bash
    docker compose up -d --build
    ```
3.  **Acceso**:
    *   **Aplicación Web**: [http://localhost:8080](http://localhost:8080)
    *   **Base de Datos (PhpMyAdmin)**: [http://localhost:8081](http://localhost:8081)
        *   Usuario: `chat_user`
        *   Contraseña: `123`

## 🛠️ Características Técnicas y Arquitectura

### Acceso a Datos y Seguridad
Para la interacción con la base de datos, utilizamos la librería **PDO (PHP Data Objects)**. Esta elección nos permite asegurar la portabilidad y seguridad del acceso a datos.
*   **Seguridad SQL**: Hemos implementado estrictamente **Sentencias Preparadas** (`prepare` / `execute`) en todas las consultas para blindar la aplicación contra inyecciones SQL.
*   **Configuración**: La conexión se gestiona mediante variables de entorno en el archivo `docker-compose.yml`, lo que nos permite modificar credenciales sin necesidad de alterar el código fuente.

### Autenticación y Usuarios
Hemos diseñado un sistema de autenticación robusto:
*   **Validación**: Realizamos una validación estricta de todos los datos en el servidor (backend), asegurando que los nombres de usuario sean alfanuméricos y las contraseñas cumplan con los requisitos de longitud.
*   **Encriptación de Contraseñas**: Garantizamos que las contraseñas **NUNCA** se almacenen en texto plano. Utilizamos el algoritmo **Bcrypt** (`password_hash` y `password_verify`), siguiendo los estándares actuales de seguridad.

### Gestión de Sesiones
Controlamos el acceso a las áreas privadas mediante sesiones nativas de PHP:
*   Protegemos el archivo principal (`index.php`) para redirigir al login si no existe una sesión activa.
*   Al cerrar sesión, nos aseguramos de destruir completamente tanto la sesión en el servidor como las cookies asociadas en el cliente.

### Experiencia de Usuario y Funcionalidades Extra
Hemos enriquecido la aplicación con características avanzadas que mejoran significativamente la experiencia de uso:
1.  **🗑️ Borrado de Mensajes**: Permitimos a los usuarios gestionar su bandeja de entrada eliminando mensajes no deseados.
2.  **👁️ Estado de Lectura**: Implementamos indicadores visuales claros; los mensajes nuevos destacan visualmente hasta que el usuario los marca como leídos.
3.  **⭐ Favoritos**: Hemos añadido un sistema para marcar mensajes importantes, incluyendo una vista filtrada exclusiva para acceder rápidamente a ellos.
4.  **🎨 Diseño Premium**: Hemos creado una interfaz moderna con estética "Glassmorphism", totalmente responsiva y con micro-animaciones que hacen la navegación fluida y agradable.

### Gestión de Errores y Feedback
Hemos integrado un sistema de "Mensajes Flash" para la gestión de errores y notificaciones. Ya sea un error en el login o una confirmación de envío, el usuario recibe feedback instantáneo mediante alertas visuales (verde/rojo) que no interrumpen el flujo de navegación.

## 🗂️ Esquema de la Base de Datos

El sistema se apoya en una estructura relacional (base de datos `cerowait`) compuesta por tres tablas normalizadas:

### Tabla `users`
Almacena la información de las cuentas de usuario.
*   `id` (PK): Identificador único.
*   `username`: Nombre de usuario (único).
*   `password`: Hash seguro de la contraseña.
*   `created_at`: Fecha de registro.

### Tabla `messages`
Contiene el historial de conversaciones.
*   `id` (PK): Identificador del mensaje.
*   `sender_id` (FK): Usuario remitente.
*   `receiver_id` (FK): Usuario destinatario.
*   `message`: Contenido del mensaje.
*   `is_read`: Estado de lectura (0/1).
*   `created_at`: Fecha de envío.

### Tabla `favorites`
Gestiona la relación de mensajes marcados como favoritos.
*   `user_id` (FK): Usuario que marca el favorito.
*   `message_id` (FK): Mensaje seleccionado.
*(Clave primaria compuesta para prevenir duplicados)*
