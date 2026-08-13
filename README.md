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
| Auditoría | spatie/laravel-activitylog |

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

## Exportación de datos (CSV / XLSX)

El panel admin permite exportar a CSV o XLSX desde tres recursos:

| Recurso | Ubicación del botón |
|---------|--------------------|
| **Revistas** | Botón "Exportar" en cabecera + bulk action "Exportar seleccionados" |
| **Libros** | Botón "Exportar" en cabecera + bulk action "Exportar seleccionados" |
| **Pagos** | Botón "Exportar" en cabecera + bulk action "Exportar seleccionados" |

### Cómo funciona

1. El admin pulsa "Exportar" → Filament abre un modal para elegir CSV o XLSX y las columnas a incluir.
2. Si la tabla tiene filtros activos, **solo se exporta lo filtrado**.
3. La exportación se procesa en segundo plano vía la cola `default` (las `ExportAction` no sobrescriben la cola). Requiere worker activo — ver [Worker de colas](#worker-de-colas--obligatorio).
4. Una notificación en el panel ofrece el botón "Descargar" cuando termina.

### Requisitos

- Tablas `exports`, `failed_import_rows` y `imports` en la BD (publicadas por Filament Actions).
- Disk `local` o el que defina `EXPORTS_DISK` con permisos de escritura en `storage/app/private/filament-exports/`.

### Columnas exportadas

Definidas en `app/Filament/Exports/`:

- `JournalExporter` — 21 campos (título, estado, score, evaluador, ISSN, sello, fechas, etc.).
- `BookExporter` — 25 campos (título, ISBN, DOI, editorial, idioma, licencia, etc.).
- `PaymentExporter` — 14 campos (fecha, comprador, producto, monto, proveedor, ID Stripe, etc.).

Para agregar/quitar columnas: editar el método `getColumns()` del Exporter correspondiente.

---

## Auditoría de cambios (Activity Log)

Las revistas, libros y pagos registran automáticamente un historial de cambios usando [spatie/laravel-activitylog](https://github.com/spatie/laravel-activitylog).

### Qué se registra

| Modelo | Atributos auditados | Eventos custom |
|--------|---------------------|----------------|
| `Journal` | `status`, `seal_status`, `assigned_evaluator_id`, `current_score`, `current_level`, `seal_expires_at`, `seal_notified_at`, `listed_at`, `evaluated_at`, `evaluation_notes` | Asignación de evaluador · Evaluación completada · Revisión de listado · Cosecha OAI · Recordatorio de sello · Transición automática de sello vencido |
| `Book` | `status`, `listed_at`, `approval_date`, `responsible_editor_id` | (solo cambios de atributos) |
| `Payment` | `status`, `amount`, `currency`, `stripe_session_id`, `product_id` | (solo cambios de atributos) |

### Dónde se consulta

Cada revista y libro tiene una pestaña **"Historial"** en su vista de Filament que muestra fecha, usuario causante, evento y diff de cambios. Los eventos sin usuario (causer null) son acciones automáticas del sistema (cron, webhook).

### Limpieza periódica

Para limitar el crecimiento de la tabla `activity_log` (Spatie purga registros antiguos según `config/activitylog.php`, por defecto a los 365 días):

```bash
php artisan activitylog:clean
```

Se puede agregar al scheduler si en el futuro se vuelve relevante.

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
command=php /var/www/editorial-standards/artisan queue:work database --queue=harvest,mail,default --sleep=3 --tries=3 --max-time=3600 --timeout=310
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

> **Las tres colas son obligatorias.** `harvest` lleva `HarvestJournalArticles`
> (cosecha OAI-PMH, encolada por la acción de Filament y por el cron semanal
> `oai:harvest --all --queue`); `mail` lleva todas las notificaciones; `default`
> lleva `RefreshJournalMetricsJob` y las exportaciones CSV/XLSX de Filament. Si
> omitís una, esos jobs se acumulan en la tabla `jobs` y no se procesan nunca.
> El orden `harvest,mail,default` es el de `docker/8.3/supervisord.conf`, salvo
> que ahí `harvest` va primero por ser el más lento; para producción priorizar
> el correo con `--queue=mail,default,harvest` también es válido.
>
> **`--timeout=310`** acompaña al `$timeout = 300` declarado en
> `HarvestJournalArticles`: el worker debe tolerar algo más que el job.
>
> **Cola `mail` y correo.** Todas las notificaciones extienden `QueuedNotification`
> (`ShouldQueue`, cola `mail`). Con
> `QUEUE_CONNECTION=database` el correo se envía en el worker: si SES tarda o
> falla, no bloquea el request del usuario ni el webhook de Stripe, y reintenta
> con backoff (hasta 6h; luego a `failed_jobs`, visible en **/admin → Monitor de
> cola**).
>
> **Mantener `numprocs=1`.** El rate-limit de SES (`RateLimiter::for('ses')`,
> ~12 msg/s) es por proceso sobre el cache. Con más de un worker sin un store
> compartido (Redis) el límite efectivo se multiplica y puede exceder SES.
>
> En local con `QUEUE_CONNECTION=sync` no hace falta worker: el correo se envía
> inline y Mailpit lo captura igual.

#### Cron del scheduler (obligatorio, aparte del worker)

El worker procesa jobs ya encolados; **no** dispara las tareas programadas. Son
dos piezas distintas y ambas hacen falta. Agregar la entrada de crontab del
usuario que corre la app:

```bash
crontab -u www-data -e
```

```
* * * * * cd /var/www/editorial-standards && php artisan schedule:run >> /dev/null 2>&1
```

Verificar con `php artisan schedule:list`. El listado completo de tareas y qué
rompe cada una si no corre está en la
[tabla de tareas programadas](#9-configurar-cron-jobs-scheduler-de-laravel) de la
sección de Hostinger — es el mismo `routes/console.php` en ambos entornos.

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
php artisan queue:restart               # el worker recarga el código nuevo (si no, corre el viejo)
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

> 🚨 **`QUEUE_CONNECTION=database` obliga a tener un worker corriendo.** Todas las notificaciones son `ShouldQueue`: si no configurás el worker del [paso 9](#worker-de-colas--obligatorio), los emails se encolan en la tabla `jobs` y **nunca se envían**, sin ningún error visible.

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

El scheduler de Laravel necesita una entrada cron que se ejecute **cada minuto**. Laravel decide internamente qué tareas correr según lo configurado en `routes/console.php`.

**Tareas programadas actualmente registradas** (fuente de verdad: `routes/console.php`):

| Comando | Frecuencia | Propósito |
|---------|------------|-----------|
| `oai:harvest --all --queue` | Semanal, lunes 02:30 | Encola un `HarvestJournalArticles` por revista `listed`/`certified` con OAI configurado. **Requiere worker de colas.** |
| `seal:check-expiration` | Diaria 03:00 | Marca sellos `expiring_soon` (30 días) y `expired`; revierte la revista de `certified` a `evaluated`. |
| `metrics:refresh-journals` | Mensual, día 1 a las 03:15 | Refresca h-index/citas vía OpenAlex + Crossref para journals certified vigentes. |
| `sitemap:generate` | Diaria 03:30 | Regenera `public/sitemap.xml`. |
| `email-logs:prune` | Diaria 03:45 | Purga `email_logs` según la retención de `config/mail_logging.php` (90 días). |
| `books:check-featured` | Diaria 04:00 | Baja `is_featured` en libros destacados vencidos. |
| `tasks:check-overdue` | Diaria 09:00 | Notifica `admin_tasks` vencidas al assignee (cooldown 24h). |
| `consulting:send-reminders` | Diaria 09:30 | `ConsultingReminder` 24h antes de cada sesión agendada. |
| `consulting:expire-proposals` | Diaria 09:45 | Expira propuestas de consultoría sin responder y devuelve la task a `pending`. |
| `messages:daily-digest` | Cada hora | Digest de conversaciones a las 9:00 hora local de cada usuario. |

> ⚠️ **Sin este cron, los sellos vencidos seguirán apareciendo como `certified` indefinidamente**, los libros destacados nunca dejarán de estarlo y no saldrá ningún digest de mensajes. Los comandos existen en el código pero solo se ejecutan si el servidor dispara el scheduler.

#### Pasos en hPanel → Avanzado → Cron Jobs

1. **Crear nuevo Cron job → seleccionar "Personalizado"** (no PHP).
2. En **"Comando a ejecutar"** pegar (ajustando la ruta a tu proyecto):

   ```
   cd /home/u123456789/domains/tudominio.com/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
   ```

3. En las 5 columnas de tiempo seleccionar **"Cada minuto (*)"** en todas.
4. Guardar.

> Para encontrar la ruta exacta del proyecto en tu servidor, conéctate por SSH y ejecuta `pwd` desde la raíz de Laravel.

#### Worker de colas — OBLIGATORIO

> 🚨 **Sin worker de colas el sitio funciona con normalidad pero NO envía un solo email.** Desde el issue #42 todas las notificaciones extienden `QueuedNotification` (`ShouldQueue`): se encolan en la tabla `jobs` y se quedan ahí para siempre si nadie las procesa. El editor no ve ningún error — simplemente nunca le llega la confirmación de pago, el aviso de evaluación ni el recordatorio de sello. Nadie se entera hasta que alguien reclama.

**Colas en uso y quién las llena:**

| Cola | Qué lleva | Origen |
|------|-----------|--------|
| `mail` | Todas las notificaciones (pagos, evaluación, sello, consultoría, mensajería) | `QueuedNotification::viaQueues()` |
| `harvest` | `HarvestJournalArticles` — cosecha OAI-PMH, un job por página con resumption token | Acción `harvest_oai` en Filament + cron `oai:harvest --all --queue` |
| `default` | `RefreshJournalMetricsJob`, exportaciones CSV/XLSX de Filament | `EvaluateJournal`, `ExportAction` |

El worker debe escuchar **las tres**: `--queue=harvest,mail,default`.

**Verificación rápida de que hay backlog sin procesar** (por SSH):

```bash
php artisan queue:monitor default,mail,harvest
```

O directo contra la BD: `SELECT queue, COUNT(*) FROM jobs GROUP BY queue;` — si los números suben y nunca bajan, no hay worker vivo.

---

**Opción A — VPS (recomendado): Supervisor**

Es la configuración con la que se desarrolla el proyecto (ver `docker/8.3/supervisord.conf`). Crear `/etc/supervisor/conf.d/editorial-standards-worker.conf`:

```ini
[program:editorial-standards-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/deploy/editorial-standards/artisan queue:work --queue=harvest,mail,default --sleep=3 --tries=3 --max-time=3600 --timeout=310
autostart=true
autorestart=true
stopwaitsecs=3600
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/home/deploy/editorial-standards/storage/logs/worker.log
```

```bash
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start editorial-standards-worker:*
```

`--timeout=310` es deliberado: `HarvestJournalArticles` declara `$timeout = 300`.

---

**Opción B — Hosting compartido (Hostinger): cron cada minuto**

En compartido no hay Supervisor ni procesos permanentes. La alternativa es un segundo cron que levante un worker efímero cada minuto y lo deje morir:

```
* * * * * cd /home/u123456789/domains/tudominio.com/public_html && flock -n storage/framework/queue.lock /usr/bin/php artisan queue:work --stop-when-empty --max-time=55 --tries=3 --queue=mail,default,harvest >> /dev/null 2>&1
```

- `--stop-when-empty` vacía toda la cola pendiente y sale (mucho mejor que `--once`, que procesaba un solo job por minuto y acumulaba backlog en cuanto había ráfagas).
- `--max-time=55` evita que dos workers se pisen entre minutos.
- `flock -n` es el seguro: si el worker anterior sigue vivo, el nuevo no arranca. Si `flock` no existe en tu servidor, quitá esa parte del comando.
- El orden `mail,default,harvest` prioriza los emails; el harvest queda último porque es el más lento.

**Limitaciones reales de esta opción — asumirlas antes de elegirla:**

- Latencia de hasta ~1 minuto en cada email.
- La cuota de CPU / procesos del hosting compartido puede matar un job largo a mitad. El harvest OAI aguanta bastante bien porque procesa **una página por job** y se re-encola con el resumption token, pero con `--tries=3` un job repetidamente interrumpido termina en `failed_jobs`. Revisar el panel QueueMonitor después de cada corrida semanal.
- `queue:restart` no tiene a quién avisar, pero tampoco hace falta: cada worker vive menos de un minuto, así que el código nuevo entra solo en el siguiente ciclo.

> ⚠️ **Nunca poner `QUEUE_CONNECTION=sync` como "solución"** en producción: los emails se enviarían dentro del request HTTP (checkout lento y con riesgo de timeout) y `oai:harvest --all --queue` bloquearía el cron durante minutos.

#### Verificar que el scheduler está activo

Por SSH, desde la raíz del proyecto:

```bash
/usr/bin/php artisan schedule:list
```

Debe listar `seal:check-expiration` con su próxima ejecución a las `03:00`.

Para forzar la ejecución manual sin esperar a las 03:00:

```bash
/usr/bin/php artisan seal:check-expiration
```

Salida esperada: `Done. Expiring soon: X, Expired: Y` (cualquier número, incluido 0, indica que el comando corrió correctamente).

#### Al agregar nuevas tareas programadas

Cuando se modifique `routes/console.php` para agregar nuevas tareas:

1. Hacer commit + push en local.
2. `git pull origin main` en el servidor.
3. `php artisan config:cache` (importante: el cache de configuración puede ocultar cambios).
4. Verificar con `php artisan schedule:list`.

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
php artisan queue:restart
```

> ⚠️ **`queue:restart` no es opcional en VPS.** El worker de Supervisor mantiene el código PHP en memoria: sin este comando sigue ejecutando la versión anterior del deploy indefinidamente, y los síntomas (notificaciones con el texto viejo, jobs que fallan por columnas que "no existen") son difíciles de diagnosticar. En hosting compartido con el cron efímero el comando es inofensivo — dejalo igual para que el checklist sea el mismo en ambos entornos.

> **Nota sobre paquetes nuevos:** cuando un deploy agrega un paquete de Composer (ej. `spatie/laravel-activitylog`), no es necesario correr `composer require` ni `vendor:publish` en el servidor — `composer install` ya instala el paquete porque está en `composer.lock`, y los archivos publicados (migraciones, configs) viajan en el repo. Solo asegúrate de correr `php artisan migrate --force` si el deploy incluye migraciones nuevas.

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
