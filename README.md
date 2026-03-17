# OpenSciRank

OpenSciRank es una plataforma avanzada para la evaluación y clasificación de revistas científicas y libros académicos bajo estándares de Ciencia Abierta. El sistema permite gestionar revisiones exhaustivas basadas en criterios ponderados, cosechar artículos mediante OAI-PMH y gestionar un ecosistema de publicaciones científicas.

## 🚀 Características Principales

- **Sistema de Evaluación Detallado**: Evaluación de revistas y libros basada en criterios categorizados (Core, Avanzados, Excelencia).
- **Cálculo de Puntuación Inteligente**: Algoritmo de puntuación ponderada con penalizaciones automáticas si no se cumplen los indicadores "core".
- **Cosecha OAI-PMH**: Integración para recolectar metadatos de artículos directamente desde servidores OAI.
- **Panel Administrativo Potente**: Gestión completa de recursos a través de Filament v5.
- **Gestión de Roles y Permisos**: Control de acceso granular (Admin, Evaluador, Editor) utilizando Filament Shield.
- **Módulo Comercial**: Soporte para pagos, facturación, cupones y productos relacionados con los servicios de evaluación.
- **Autenticación Segura**: Implementación de Laravel Fortify para un manejo robusto de sesiones y seguridad.

## 🛠️ Stack Tecnológico

- **Backend**: [Laravel 12+](https://laravel.com)
- **Frontend Interactivo**: [Livewire 4](https://livewire.laravel.com)
- **Panel de Administración**: [Filament v5](https://filamentphp.com)
- **Estilos**: [Tailwind CSS 4](https://tailwindcss.com)
- **Constructor de Assets**: [Vite](https://vitejs.dev)
- **Base de Datos**: MySQL / PostgreSQL / SQLite
- **Lenguaje**: PHP 8.2+

## ⚙️ Instalación

### Opción A: Instalación Manual

1. **Clonar el repositorio**:
   ```bash
   git clone https://github.com/tu-usuario/OpenSciRank.git
   cd OpenSciRank
   ```

2. **Instalar dependencias de PHP**:
   ```bash
   composer install
   ```

3. **Configurar el entorno**:
   ```bash
   cp .env.example .env
   # Edita el archivo .env con tus credenciales de base de datos
   ```

4. **Generar la clave de aplicación y migrar la base de datos**:
   ```bash
   # Si usas Sail:
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate --seed

   # Si usas PHP directamente:
   php artisan key:generate
   php artisan migrate --seed
   ```

5. **Instalar dependencias de JS y compilar**:
   ```bash
   npm install
   npm run build
   ```

### Opción B: Instalación Rápida (Script)

El proyecto incluye un script de configuración automática que realiza la mayoría de los pasos anteriores:

```bash
composer setup
```

## 🖥️ Desarrollo

Para iniciar el entorno de desarrollo con hot-reloading y servicios necesarios (cola, logs, etc.):

```bash
composer dev
# O manualmente
php artisan serve & npm run dev
```

## 👥 Administración (Primer Acceso)

Si has desplegado el proyecto en un servidor nuevo y no tienes un usuario administrador, puedes usar el script de emergencia `create_admin.php`:

1.  **Ejecutar el script**:
    ```bash
    # Si usas Sail:
    ./vendor/bin/sail artisan tinker create_admin.php

    # Si usas PHP directamente:
    php artisan tinker create_admin.php
    ```

2.  **Credenciales**: El usuario creado será `admin@openscirank.com` con la contraseña `password`.

> [!WARNING]
> **SEGURIDAD**: Por razones de seguridad, debes eliminar el archivo `create_admin.php` inmediatamente después de obtener tu acceso inicial y cambiar la contraseña desde el panel administrativo.
> ```bash
> rm create_admin.php
> ```

## 🧪 Pruebas

El proyecto incluye una suite de pruebas para asegurar la calidad del código:

```bash
php artisan test
```

## 📄 Licencia

Este proyecto está bajo la licencia MIT. Consulta el archivo [LICENSE](LICENSE) para más detalles.

---

Desarrollado con ❤️ para la comunidad científica.
