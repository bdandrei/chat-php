# Chat App (PHP + MySQL + Docker)

Un servicio de mensajería moderno y seguro (sin frameworks PHP) diseñado para la práctica final.

## 🚀 Inicio Rápido

### Requisitos
- Docker & Docker Compose

### Instalación
1. Clona este repositorio o entra en la carpeta.
2. Levanta los contenedores:
   ```bash
   docker-compose up -d --build
   ```
3. Accede a la aplicación en:
   [http://localhost:8080](http://localhost:8080)

### Funcionalidades
- 🔒 **Login/Registro Seguro**: Validación de usuario/contraseña y hashing.
- 📩 **Mensajería**: Enviar y recibir mensajes en tiempo real (al recargar).
- ⭐ **Favoritos**: Marcar mensajes importantes.
- 🗑️ **Borrar**: Eliminar mensajes de tu bandeja de entrada.
- 👀 **Leído/No Leído**: Distinción visual clara.
- 🎨 **Diseño Premium**: Interfaz responsive con efecto Glassmorphism.

## 🛠️ Desarrollo

El código fuente se encuentra en `src/`.
La base de datos se inicializa automáticamente con `init.sql`.

Si necesitas ver la base de datos:
- PhpMyAdmin: [http://localhost:8081](http://localhost:8081)
- User: `root` / Pass: `root_secret`
