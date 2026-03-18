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
    ./vendor/bin/sail artisan migrate
    ./vendor/bin/sail artisan db:seed --class=CriteriaItemSeeder

    # Si usas PHP directamente:
    php artisan key:generate
    php artisan migrate
    php artisan db:seed --class=CriteriaItemSeeder
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

## 👥 Administración y Control de Acceso (Shield)

Si has desplegado el proyecto en un servidor nuevo, debes inicializar el sistema de permisos y crear el usuario administrador:

1.  **Generar Roles y Permisos**:
    El proyecto utiliza [Filament Shield](https://github.com/bezhanSalleh/filament-shield). Ejecuta este comando para generar todos los permisos basados en los modelos:
    ```bash
    # Si usas PHP directamente:
    php artisan shield:generate --all

    # Si usas Sail:
    ./vendor/bin/sail artisan shield:generate --all
    ```

2.  **Crear Usuario Administrador (Seeder)**:
    Recomendamos usar el seeder incluido para crear el usuario inicial con el rol `super_admin`:
    ```bash
    # Si usas PHP directamente:
    php artisan db:seed --class=AdminUserSeeder

    # Si usas Sail:
    ./vendor/bin/sail artisan db:seed --class=AdminUserSeeder
    ```

4.  **Inicializar Categorías e Indicadores (Documento Maestro)**:
    Para cargar las 5 áreas de evaluación y los 18 indicadores oficiales:
    ```bash
    # Si usas PHP directamente:
    php artisan db:seed --class=CriteriaItemSeeder

    # Si usas Sail:
    ./vendor/bin/sail artisan db:seed --class=CriteriaItemSeeder
    ```

3.  **Credenciales por defecto**: 
    - **Email**: `admin@openscirank.com`
    - **Password**: `password`

> [!IMPORTANT]
> **Seguridad**: Una vez que hayas iniciado sesión por primera vez, asegúrate de cambiar la contraseña y el email del administrador desde el panel de control de Filament.

## 🚀 Git y Despliegue

Si realizas cambios en el código y quieres subirlos a GitHub, utiliza estos comandos estándar:

```bash
# 1. Preparar todos los cambios
git add .

# 2. Guardar los cambios con un mensaje descriptivo
git commit -m "Descripción de tus cambios"

# 3. Subir los cambios a GitHub
git push origin main
```

## 🧪 Pruebas

El proyecto incluye una suite de pruebas para asegurar la calidad del código:

```bash
php artisan test
```

## 🎨 Compilación de Activos (Tailwind CSS)

Si realizas cambios en la interfaz (Blade, CSS, componentes), es **fundamental** compilar los activos para que se reflejen correctamente:

### Desarrollo (Hot Reloading)
Mantiene un servidor activo que compila los cambios al instante mientras trabajas:
```bash
npm run dev
```

### Producción (Compilación Final)
Genera los archivos optimizados y minificados para el servidor real:
```bash
npm run build
```

> [!TIP]
> **Vite**: El proyecto utiliza Vite para gestionar los assets. Si los cambios en el CSS no se ven aplicados, asegúrate de que el comando `npm run dev` esté ejecutándose en una terminal abierta.

## 📄 Licencia

Este proyecto está bajo la licencia MIT. Consulta el archivo [LICENSE](LICENSE) para más detalles.

---

Desarrollado con ❤️ para la comunidad científica.
