# Documentación Técnica: Servicio de Mensajería Instantánea

## 1. Introducción
Este documento detalla la arquitectura, tecnologías y decisiones de diseño detrás de nuestro servicio de mensajería instantánea. El proyecto ha sido concebido como una solución robusta y segura, priorizando la experiencia de usuario mediante una interfaz moderna y funcionalidades avanzadas de gestión de comunicaciones.

## 2. Stack Tecnológico

Hemos seleccionado tecnologías estándar y probadas para garantizar el rendimiento y la facilidad de mantenimiento:

- **Backend**: **PHP 8.2** nativo. Hemos optado por no utilizar frameworks pesados para mantener un control total sobre la arquitectura y demostrar un conocimiento profundo del lenguaje, especialmente en el uso de **PDO** y gestión de sesiones.
- **Base de Datos**: **MySQL / MariaDB**. Utilizamos un modelo relacional eficiente para garantizar la integridad de los datos.
- **Frontend**: **HTML5 y CSS3**. El diseño se basa en la estética "Glassmorphism", utilizando variables CSS, Flexbox y Grid para un layout totalmente responsivo, sin dependencias de frameworks CSS externos.
- **Infraestructura**: **Docker & Docker Compose**. Todo el entorno está contenerizado para asegurar un despliegue consistente en cualquier máquina.

## 3. Arquitectura de Datos

El sistema se fundamenta en un esquema de base de datos relacional optimizado, compuesto por tres tablas principales:

### `users`
Gestiona la identidad y el acceso de los usuarios.
- `id`: Clave primaria autoincremental.
- `username`: Identificador único del usuario.
- `password`: Hash seguro de la contraseña (generado vía `password_hash`).
- `created_at`: Marca de tiempo del registro.

### `messages`
Almacena el núcleo de la comunicación.
- `id`: Clave primaria del mensaje.
- `sender_id`: Referencia al usuario que envía (*Foreign Key*).
- `receiver_id`: Referencia al usuario que recibe (*Foreign Key*).
- `message`: Contenido textual del mensaje.
- `is_read`: Indicador de estado (0: No leído, 1: Leído).
- `created_at`: Marca de tiempo del envío.

### `favorites`
Permite la funcionalidad de marcado de mensajes. Implementa una relación muchos-a-muchos.
- `user_id`: Usuario que realiza la acción.
- `message_id`: Mensaje marcado.
*(La combinación de ambos campos forma la clave primaria compuesta)*

## 4. Detalles de Implementación y Funcionalidades

### Seguridad y Acceso a Datos
La seguridad ha sido una prioridad en el desarrollo:
- **Prevención de Inyecciones SQL**: Utilizamos exclusivamente **PDO** con sentencias preparadas (`prepare`/`execute`) para todas las consultas a la base de datos.
- **Almacenamiento de Contraseñas**: Implementamos el algoritmo **Bcrypt** (estándar actual de la industria) para el hashing de contraseñas, asegurando que nunca se almacenen en texto plano.
- **Validación Estricta**: Todos los datos de entrada (registro, login, envío de mensajes) son validados rigurosamente en el backend.

### Gestión de Sesiones y Autenticación
El control de acceso se realiza mediante sesiones nativas de PHP:
- **Protección de Rutas**: Un middleware verifica la existencia de una sesión activa antes de permitir el acceso a las vistas privadas.
- **Ciclo de Vida**: Gestionamos el inicio y cierre de sesión (`login`/`logout`) asegurando la completa destrucción de los datos de sesión y cookies al salir.

### Funcionalidades Avanzadas de Usuario
Hemos enriquecido la aplicación más allá de la mensajería básica:
1.  **Gestión de Bandeja**: Implementamos la capacidad de eliminar mensajes recibidos, otorgando al usuario control sobre su historial.
2.  **Estado de Lectura**: Desarrollamos un sistema visual que diferencia claramente los mensajes nuevos de los ya leídos, mejorando la usabilidad.
3.  **Sistema de Favoritos**: Añadimos la posibilidad de marcar mensajes importantes y filtramos la vista para acceder rápidamente a esta selección.

### Interfaz y Experiencia de Usuario (UX/UI)
- **Diseño Glassmorphism**: Hemos creado una interfaz visualmente atractiva con efectos de transparencia y desenfoque.
- **Feedback Interactivo**: Implementamos un sistema de "mensajes flash" que informa al usuario sobre el resultado de sus acciones (éxito/error) de manera no intrusiva.
- **Adaptabilidad**: La interfaz es completamente responsive, adaptándose a diferentes tamaños de pantalla.

## 5. Guía de Despliegue

### Requisitos Previos
- Docker y Docker Compose instalados en el sistema anfitrión.

### Procedimiento
1.  Clonar el repositorio del proyecto.
2.  Ejecutar el comando de construcción y arranque:
    ```bash
    docker compose up -d --build
    ```
3.  Acceder a la aplicación web en: `http://localhost:8080`.
4.  (Opcional) Administrar la base de datos vía PhpMyAdmin en: `http://localhost:8081`.

### Datos de Prueba
El sistema se despliega con una base de datos limpia. Para probar el funcionamiento, registre dos usuarios nuevos (ej: `usuario1` y `usuario2`) e inicie una conversación entre ellos.
