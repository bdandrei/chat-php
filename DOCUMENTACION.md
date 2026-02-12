# Documentación del Proyecto: Servicio de Mensajería Instantánea (PHP)

## 1. Introducción
Este proyecto consiste en un servicio de mensajería instantánea accesible vía web, desarrollado en PHP, MySQL, HTML5 y CSS3. Permite a los usuarios registrarse, iniciar sesión, enviar y recibir mensajes, y gestionar su buzón de entrada con funcionalidades avanzadas.

## 2. Tecnologías Utilizadas
- **Backend**: PHP 8.2 (sin frameworks, uso de PDO para seguridad).
- **Base de Datos**: MySQL / MariaDB (relational).
- **Frontend**: HTML5, CSS3 (Diseño "Glassmorphism" con animaciones y gradientes), JavaScript mínimo.
- **Entorno**: Docker & Docker Compose (para despliegue reproducible).

## 3. Estructura de la Base de Datos
El sistema utiliza 3 tablas principales:

### `users`
Almacena la información de acceso.
- `id`: Identificador único (PK).
- `username`: Nombre de usuario (único).
- `password`: Hash de la contraseña (bcrypt).
- `created_at`: Fecha de registro.

### `messages`
Almacena el contenido de los mensajes.
- `id`: Identificador (PK).
- `sender_id`: ID del remitente (FK -> users.id).
- `receiver_id`: ID del destinatario (FK -> users.id).
- `message`: Contenido de texto.
- `is_read`: Booleano (0/1) para el estado de lectura.
- `created_at`: Fecha de envío.

### `favorites`
Relación muchos-a-muchos para mensajes favoritos por usuario.
- `user_id`: ID del usuario.
- `message_id`: ID del mensaje.

## 4. Funcionalidades y Criterios de Evaluación

### Acceso a Base de Datos (Seguridad)
- Se utiliza **PDO** (PHP Data Objects) con sentencias preparadas (`prepare`/`execute`) para prevenir completamente ataques de **Inyección SQL**.

### Validación de Usuarios
- Registro con validación de:
  - Usuario: Solo letras y números (Reject).
  - Contraseña: Longitud 6-30 caracteres.
  - Confirmación de contraseña.
- Contraseñas almacenadas de forma segura usando `password_hash()` (Blowfish/Bcrypt).

### Gestión de Sesión
- Se utilizan sesiones nativas de PHP (`session_start`) para mantener al usuario autenticado.
- Protección contra secuestro básico (regeneración de ID tras login - *opcional, implementado implícitamente al destruir/crear*).

### Mejoras de Diferenciación (2 Puntos)
Hemos implementado 3 mejoras significativas:
1.  **Borrado de Mensajes**: Los usuarios pueden eliminar mensajes de su buzón.
2.  **Estado de Lectura Visual**: Los mensajes no leídos aparecen con un resaltado distintivo (borde y fondo) para diferenciarlos rápidamente.
3.  **Sistema de Favoritos**: Los usuarios pueden marcar mensajes importantes con una estrella. Existe una vista filtrada para ver "Solo Favoritos".

### Gestión de Errores
- Sistema de "mensajes flash" (`flash()`) para mostrar errores y confirmaciones al usuario de forma no intrusiva tras las redirecciones.
- Validaciones en backend de todos los inputs.

### Diseño (Aesthetics)
- Interfaz moderna con "Glassmorphism" (transparencias y desenfoques).
- Modo oscuro por defecto con paleta de colores vibrante (Inter font).
- Totalmente responsive.

## 5. Instrucciones de Despliegue

### Requisitos
- Docker y Docker Compose instalados.

### Pasos
1. Clonar el repositorio.
2. Ejecutar `docker-compose up -d --build`.
3. Acceder a `http://localhost:8080`.
4. (Opcional) Acceder a PhpMyAdmin en `http://localhost:8081`.

### Usuarios de Prueba
El sistema se inicia vacío. Regístrese con cualquier usuario (ej: `usuario1`, `pass123`) para probar.
