# Editorial Standards Platform

Plataforma de evaluación y visibilidad de revistas científicas y libros académicos. Las revistas son evaluadas contra criterios ponderados, reciben una puntuación (0–100 %) y pueden obtener el **Sello de Estándares Editoriales** con validez anual. Los libros pueden indexarse mediante pago.

## Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 12, PHP 8.2+ |
| Frontend interactivo | Livewire 4 |
| Panel de administración | Filament v5 |
| Estilos | Tailwind CSS 4 + Vite |
| Base de datos | MySQL 8.4 |
| Contenedores | Laravel Sail (Docker) |
| Email (dev) | Mailpit |
| Pagos | Stripe |

---

## Requisitos previos

- **Docker Desktop** (Windows / macOS) o **Docker Engine + Docker Compose** (Linux)
- **Git**
- **Composer** (solo para el primer `composer install` antes de levantar Sail)

> En Windows se requiere **WSL 2** con Docker Desktop integrado.

---

## Instalación paso a paso

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio>
cd OpenSciRank
```

### 2. Instalar dependencias de PHP

```bash
composer install --no-scripts
```

### 3. Configurar el entorno

```bash
cp .env.example .env
```

Edita `.env` y ajusta al menos estas variables:

```env
APP_PORT=5000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_FROM_ADDRESS="noreply@editorialstandards.com"
MAIL_FROM_NAME="Editorial Standards Platform"

STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

### 4. Levantar los contenedores Docker

```bash
./vendor/bin/sail up -d
```

La primera vez descargará las imágenes de Docker (~2–3 min).

### 5. Generar la clave de aplicación

```bash
./vendor/bin/sail artisan key:generate
```

### 6. Ejecutar migraciones y seeders

```bash
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan shield:generate --all
```

El segundo comando genera todos los roles y permisos de Filament Shield para el panel de administración. Este comando crea todas las tablas y carga los datos iniciales:

| Seeder | Qué crea |
|--------|----------|
| `AdminUserSeeder` | Usuario administrador con rol `super_admin` |
| `EvaluationCategorySeeder` | 5 categorías de evaluación |
| `CriteriaItemSeeder` | 18 indicadores de evaluación (5 core) |
| `ProductSeeder` | 7 productos/planes de pago |
| `JournalSeeder` | 104 revistas científicas reales de referencia |

### 7. Instalar dependencias de JavaScript y compilar assets

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

### 8. Acceder al sistema

| Recurso | URL |
|---------|-----|
| Aplicación principal | http://localhost:5000 |
| Panel de administración | http://localhost:5000/admin |
| Bandeja de email (dev) | http://localhost:8025 |

**Credenciales del administrador:**

```
Email:    admin@editorialstandards.com
Password: password
```

> **Importante:** Cambia la contraseña en el primer inicio de sesión.

---

## Desarrollo (hot-reload)

Con los contenedores corriendo, abre una segunda terminal para Vite:

```bash
./vendor/bin/sail npm run dev
```

Los cambios en Blade, Livewire y CSS se reflejan instantáneamente en el navegador.

---

## Comandos frecuentes

```bash
# Iniciar / detener contenedores
./vendor/bin/sail up -d
./vendor/bin/sail down

# Reinstalar base de datos desde cero
./vendor/bin/sail artisan migrate:fresh --seed

# Ejecutar un seeder individual
./vendor/bin/sail artisan db:seed --class=JournalSeeder

# Ejecutar tests
./vendor/bin/sail artisan test

# Verificar estilo de código (Pint)
./vendor/bin/sail composer test:lint

# Acceder al shell del contenedor
./vendor/bin/sail shell

# Ver logs en tiempo real
./vendor/bin/sail logs -f
```

---

## Seeders disponibles

```bash
./vendor/bin/sail artisan db:seed --class=AdminUserSeeder           # Usuario admin
./vendor/bin/sail artisan db:seed --class=EvaluationCategorySeeder  # Categorías
./vendor/bin/sail artisan db:seed --class=CriteriaItemSeeder        # Indicadores
./vendor/bin/sail artisan db:seed --class=ProductSeeder             # Productos/planes
./vendor/bin/sail artisan db:seed --class=JournalSeeder             # 104 revistas reales
```

---

## Configuración de Stripe

Para activar los pagos en desarrollo:

1. Crea una cuenta en [stripe.com](https://stripe.com) y obtén las claves de prueba.
2. Agrega las claves al `.env`:

```env
STRIPE_KEY=pk_test_xxxxxxxxxxxx
STRIPE_SECRET=sk_test_xxxxxxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxx
```

3. Para recibir webhooks en local, instala la [Stripe CLI](https://stripe.com/docs/stripe-cli) y ejecuta:

```bash
stripe listen --forward-to http://localhost:5000/stripe/webhook
```

---

## Variables de entorno relevantes

| Variable | Descripción | Valor en desarrollo |
|----------|-------------|---------------------|
| `APP_PORT` | Puerto de la aplicación | `5000` |
| `DB_CONNECTION` | Driver de base de datos | `mysql` |
| `DB_HOST` | Host MySQL (Sail) | `mysql` |
| `DB_USERNAME` | Usuario MySQL (Sail) | `sail` |
| `DB_PASSWORD` | Contraseña MySQL (Sail) | `password` |
| `MAIL_MAILER` | Driver de correo | `smtp` |
| `MAIL_HOST` | Host SMTP (Sail) | `mailpit` |
| `STRIPE_KEY` | Clave pública de Stripe | `pk_test_...` |
| `STRIPE_SECRET` | Clave secreta de Stripe | `sk_test_...` |
| `STRIPE_WEBHOOK_SECRET` | Secreto del webhook de Stripe | `whsec_...` |

---

## Despliegue en producción

### Requisitos del servidor

- Ubuntu 22.04 / 24.04 LTS (recomendado)
- PHP 8.2+ con extensiones: `bcmath curl fileinfo gd mbstring openssl pdo pdo_mysql tokenizer xml zip`
- Nginx o Apache
- MySQL 8.0+
- Node.js 20+ (solo para compilar assets)
- Composer 2
- Supervisor (para el worker de colas)

---

### 1. Clonar y preparar

```bash
cd /var/www
git clone <url-del-repositorio> editorial-standards
cd editorial-standards

composer install --no-dev --optimize-autoloader
npm ci && npm run build
```

---

### 2. Configurar el entorno

```bash
cp .env.example .env
nano .env
```

Variables críticas para producción:

```env
APP_NAME="Editorial Standards Platform"
APP_ENV=production
APP_KEY=                          # se genera en el paso siguiente
APP_DEBUG=false
APP_URL=https://tudominio.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=editorial_standards
DB_USERNAME=db_user
DB_PASSWORD=db_password_segura

MAIL_MAILER=ses                   # o smtp para otro proveedor
MAIL_FROM_ADDRESS="noreply@tudominio.com"
MAIL_FROM_NAME="Editorial Standards Platform"

# Amazon SES (producción)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1

STRIPE_KEY=pk_live_xxxxxxxxxxxx
STRIPE_SECRET=sk_live_xxxxxxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxx

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

---

### 3. Generar clave, migrar y sembrar

```bash
php artisan key:generate
php artisan migrate --seed --force
php artisan shield:generate --all
php artisan storage:link
```

---

### 4. Optimizar para producción

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache
```

---

### 5. Permisos de directorios

```bash
chown -R www-data:www-data /var/www/editorial-standards
chmod -R 755 /var/www/editorial-standards/storage
chmod -R 755 /var/www/editorial-standards/bootstrap/cache
```

---

### 6. Configurar Nginx

```nginx
server {
    listen 80;
    server_name tudominio.com www.tudominio.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name tudominio.com www.tudominio.com;

    root /var/www/editorial-standards/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/tudominio.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tudominio.com/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
nginx -t && systemctl reload nginx
```

---

### 7. Configurar worker de colas con Supervisor

```bash
apt install supervisor -y
nano /etc/supervisor/conf.d/editorial-standards-worker.conf
```

```ini
[program:editorial-standards-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/editorial-standards/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/editorial-standards/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
supervisorctl reread
supervisorctl update
supervisorctl start editorial-standards-worker:*
```

---

### 8. Configurar webhook de Stripe

En el dashboard de Stripe → Developers → Webhooks, agrega el endpoint:

```
https://tudominio.com/stripe/webhook
```

Eventos a escuchar: `checkout.session.completed`

Copia el **Signing secret** y agrégalo al `.env`:

```env
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxx
```

---

### 9. SSL con Let's Encrypt

```bash
apt install certbot python3-certbot-nginx -y
certbot --nginx -d tudominio.com -d www.tudominio.com
```

---

### 10. Actualizar la aplicación (deploys futuros)

```bash
cd /var/www/editorial-standards
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
supervisorctl restart editorial-standards-worker:*
```

---

## Despliegue en Hostinger (servidor compartido)

Hostinger Business o superior incluye acceso SSH, PHP 8.2+, MySQL y SSL gratuito — suficiente para correr Laravel en producción sin VPS.

> Node.js **no está disponible** en hosting compartido. Debes compilar los assets **localmente** antes de subir.

---

### Antes de empezar: compilar assets en local

```bash
# En tu máquina local, con el .env apuntando a producción:
npm ci && npm run build
```

Esto genera la carpeta `public/build/`. La subirás junto con el resto del proyecto.

---

### 1. Configurar PHP en hPanel

1. Inicia sesión en [hPanel](https://hpanel.hostinger.com)
2. Ve a **Hosting → Administrar → Avanzado → Versión de PHP**
3. Selecciona **PHP 8.2** (o superior)
4. Activa las extensiones: `bcmath`, `gd`, `mbstring`, `pdo_mysql`, `zip`, `fileinfo`, `openssl`

---

### 2. Crear la base de datos MySQL

1. En hPanel → **Bases de datos → MySQL**
2. Crea una nueva base de datos, usuario y contraseña
3. Anota los datos: `host`, `database`, `username`, `password`

> El host suele ser `127.0.0.1` o `localhost` en Hostinger.

---

### 3. Subir los archivos del proyecto

**Opción A — Git via SSH (recomendado):**

Hostinger Business incluye SSH. Conéctate desde tu terminal:

```bash
ssh u123456789@tudominio.com -p 65002
```

Dentro del servidor:

```bash
cd ~
git clone <url-del-repositorio> editorial-standards
cd editorial-standards
composer install --no-dev --optimize-autoloader
```

**Opción B — FTP / Administrador de archivos:**

Sube todos los archivos a `~/editorial-standards/` usando el Administrador de archivos de hPanel o un cliente FTP (FileZilla). Asegúrate de incluir la carpeta `public/build/` compilada en el paso anterior.

---

### 4. Apuntar el dominio a la carpeta `/public`

En hPanel → **Dominios → tudominio.com → Editar** (o Configuración avanzada):

- Cambia la **Raíz del documento** (Document Root) a:
  ```
  /home/u123456789/editorial-standards/public
  ```

Guarda los cambios. Esto hace que el servidor web sirva únicamente la carpeta `public/` de Laravel, sin exponer el resto del código.

---

### 5. Configurar el archivo `.env`

```bash
# En el servidor via SSH:
cd ~/editorial-standards
cp .env.example .env
nano .env
```

```env
APP_NAME="Editorial Standards Platform"
APP_ENV=production
APP_KEY=                               # se genera en el paso siguiente
APP_DEBUG=false
APP_URL=https://tudominio.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u123456789_editorial      # nombre de tu BD en Hostinger
DB_USERNAME=u123456789_user
DB_PASSWORD=tu_password_segura

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=noreply@tudominio.com
MAIL_PASSWORD=tu_password_email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@tudominio.com"
MAIL_FROM_NAME="Editorial Standards Platform"

STRIPE_KEY=pk_live_xxxxxxxxxxxx
STRIPE_SECRET=sk_live_xxxxxxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxx

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

> **Email en Hostinger:** crea primero una cuenta de correo en hPanel → **Emails → Cuentas de correo** y usa esas credenciales SMTP.

---

### 6. Generar clave, migrar y sembrar

```bash
php artisan key:generate
php artisan migrate --seed --force
php artisan storage:link
```

---

### 7. Optimizar para producción

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache
```

---

### 8. Configurar `.htaccess` para URLs limpias

Laravel ya incluye un `public/.htaccess` correcto. Verifica que esté presente y que el módulo `mod_rewrite` esté activo (en Hostinger lo está por defecto).

Si el sitio muestra un error 500 o rutas no encontradas, agrega esto al `.htaccess` de la raíz del documento (`public/.htaccess`):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteRule ^index\.php$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /index.php [L]
</IfModule>
```

---

### 9. Configurar cron jobs (scheduler de Laravel)

En hPanel → **Avanzado → Cron Jobs**, agrega:

```
* * * * * php /home/u123456789/editorial-standards/artisan schedule:run >> /dev/null 2>&1
```

Para procesar la cola de trabajos (emails, notificaciones), agrega un segundo cron:

```
* * * * * php /home/u123456789/editorial-standards/artisan queue:work --once --queue=default >> /dev/null 2>&1
```

> En hosting compartido no hay Supervisor. El cron con `--once` es la alternativa: procesa un job por minuto.

---

### 10. SSL (HTTPS)

Hostinger incluye **Let's Encrypt gratuito**. Actívalo en:

hPanel → **Seguridad → SSL** → selecciona tu dominio → **Instalar**

Después de activarlo, verifica que `APP_URL` en `.env` use `https://`.

---

### 11. Configurar webhook de Stripe

En el dashboard de Stripe → **Developers → Webhooks → Add endpoint**:

```
https://tudominio.com/stripe/webhook
```

Evento: `checkout.session.completed`

Copia el **Signing secret** y agrégalo al `.env`:

```env
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxx
```

Vuelve a cachear la configuración:

```bash
php artisan config:cache
```

---

### 12. Actualizar la aplicación (deploys futuros)

En tu máquina local:

```bash
npm ci && npm run build
# Sube public/build/ via FTP o git push
```

En el servidor via SSH:

```bash
cd ~/editorial-standards
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### Resumen de rutas en el servidor Hostinger

```
/home/u123456789/
└── editorial-standards/       ← raíz del proyecto Laravel
    ├── app/
    ├── database/
    ├── public/                ← Document Root del dominio
    │   ├── build/             ← assets compilados (subir desde local)
    │   ├── index.php
    │   └── .htaccess
    ├── storage/
    └── .env
```

---

## Instalación sin Docker (manual)

Si prefieres no usar Docker, necesitas PHP 8.2+, Composer, Node.js 20+ y MySQL 8+ instalados localmente.

```bash
# 1. Instalar dependencias
composer install
npm install

# 2. Configurar entorno (ajusta DB_* con tus credenciales locales)
cp .env.example .env
php artisan key:generate

# 3. Migrar y sembrar
php artisan migrate --seed

# 4. Compilar assets
npm run build

# 5. Servir la aplicación
php artisan serve
```

---

## Tests

```bash
# Todos los tests
./vendor/bin/sail artisan test

# Filtrar por clase
./vendor/bin/sail artisan test --filter=AuthenticationTest

# Con reporte de cobertura
./vendor/bin/sail artisan test --coverage
```

---

## Licencia

MIT — consulta el archivo [LICENSE](LICENSE) para más detalles.
