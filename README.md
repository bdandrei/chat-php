# Práctica Final - Servicio de Mensajería Instantánea

Este proyecto implementa un chat web completo utilizando PHP nativo, MySQL y Docker. Cumple con todos los requisitos de la práctica y añade mejoras significativas de usabilidad y diseño.

## 🚀 Instrucciones de Instalación y Ejecución

El proyecto está contenerizado para facilitar su despliegue en cualquier máquina Linux.

1.  **Requisitos**: Tener instalado `docker` y `docker compose`.
2.  **Despliegue**:
    Estando en la carpeta raíz del proyecto, ejecuta:
    ```bash
    docker compose up -d --build
    ```
3.  **Acceso**:
    *   **Aplicación Web**: [http://localhost:8080](http://localhost:8080)
    *   **Base de Datos (PhpMyAdmin)**: [http://localhost:8081](http://localhost:8081)
        *   Usuario: `chat_user`
        *   Contraseña: `123`

## 🛠️ Justificación Técnica (Criterios de Evaluación)

### 1. Acceso a Base de Datos (1 punto)
*   **Tecnología**: Se utiliza la librería **PDO** (PHP Data Objects) para todas las conexiones.
*   **Seguridad**: Uso estricto de **Sentencias Preparadas** (`prepare` / `execute`) para blindar la aplicación contra inyecciones SQL.
*   **Configuración**: La conexión se realiza mediante variables de entorno definidas en `docker-compose.yml`, permitiendo cambiar credenciales sin tocar el código.

### 2. Validación de Usuarios (1 punto)
*   **Registro**: Se validan los datos de entrada en el servidor (backend).
    *   Nombres de usuario: Solo alfanuméricos.
    *   Contraseñas: Longitud mínima de 6 caracteres.
*   **Seguridad**: Las contraseñas **NUNCA** se guardan en texto plano. Se utiliza el algoritmo **Bcrypt** (`password_hash` y `password_verify`) estándar de la industria.

### 3. Gestión de Sesión (1 punto)
*   **Mecanismo**: Uso de sesiones nativas de PHP (`session_start`).
*   **Control**: Se protege el acceso a `index.php`; si no hay sesión activa, redirige automáticamente al login. Al cerrar sesión (`logout.php`), se destruye completamente la sesión y las cookies asociadas.

### 4. Mejoras de Diferenciación (2 puntos)
Hemos implementado 4 funcionalidades clave que distinguen este chat:
1.  **🗑️ Borrado de Mensajes**: El usuario puede eliminar mensajes de su propia bandeja de entrada.
2.  **� Estado de Lectura**: Diferenciación visual clara. Los mensajes no leídos tienen un borde e indicador de color, que desaparece al marcarlos como leídos.
3.  **⭐ Favoritos**: Sistema para marcar mensajes importantes. Incluye una vista filtrada ("Favoritos") para ver solo esos mensajes.
4.  **🎨 Diseño Premium "Glassmorphism"**: Interfaz moderna, oscura y con efectos de transparencia, totalmente responsiva y animada.

### 5. Gestión de Errores (1 punto)
*   **Feedback Visual**: Sistema de "Mensajes Flash". Los errores (login fallido, usuario ocupado) o éxitos (mensaje enviado) se muestran en alertas de colores (verde/rojo) en la parte superior y desaparecen tras ser vistos, informando siempre al usuario del estado de sus acciones.

### 6. Legibilidad y Usabilidad (1 punto)
*   **Código**: Estructurado limpiamente en archivos separados (`db.php`, `functions.php`, vistas).
*   **Interfaz**: Intuitiva, con navegación clara entre buzón y favoritos, y acciones rápidas en cada tarjeta de mensaje.

## 🗂️ Esquema de la Base de Datos

El sistema utiliza una base de datos relacional llamada `cerowait` con tres tablas interconectadas:

### Tabla `users`
Almacena las credenciales de los usuarios.
*   `id` (PK): Identificador único.
*   `username`: Nombre único del usuario.
*   `password`: Hash cifrado de la contraseña.
*   `created_at`: Fecha de alta.

### Tabla `messages`
Almacena el contenido y estado de los envíos.
*   `id` (PK): Identificador del mensaje.
*   `sender_id` (FK): Quién lo envió.
*   `receiver_id` (FK): Quién lo recibe.
*   `message`: Texto del mensaje.
*   `is_read`: Estado (0 = No leído, 1 = Leído).

### Tabla `favorites`
Tabla intermedia para la funcionalidad de favoritos.
*   `user_id` (FK): Usuario que marca el favorito.
*   `message_id` (FK): Mensaje marcado.
*(Clave primaria compuesta por ambos campos para evitar duplicados)*
