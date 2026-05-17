# Design System — Editorial Standards Platform

**Versión:** 1.0 · **Fecha:** 2026-05-17 · **Sprint:** 5 (EPIC #49 · Sub-issue #52 / Sprint C)

Documenta el **sistema de diseño operativo** que YA existe en el código. No inventa abstracciones nuevas. Sirve como referencia rápida cuando alguien quiera saber: "¿qué tokens hay disponibles? ¿cómo se aplica la paleta? ¿qué patrones puedo copiar de otro lado?"

Para decisiones de **marca** (paleta canónica, tipografía, sello, reglas do/don't): ver [`BRAND.md`](BRAND.md). Este documento es la cara técnica/operativa; `BRAND.md` es la cara de identidad.

---

## 1. Tokens (referencia rápida)

Definidos en [`resources/css/app.css`](resources/css/app.css), expuestos a Tailwind como utility classes.

### Brand

| Token CSS | Utility Tailwind | Valor | Uso |
|---|---|---|---|
| `--brand` | `bg-brand` / `text-brand` / `border-brand` | `oklch(0.30 0.10 260)` ≈ `#1E3A8A` | Editorial Blue — primary dominante |
| `--brand-deep` | `bg-brand-deep` / `text-brand-deep` | `oklch(0.20 0.08 260)` ≈ `#172554` | Editorial Blue Deep — heros, sello, signature surfaces |
| `--brand-active` | `bg-brand-active` | `oklch(0.40 0.13 260)` ≈ `#1D4ED8` | Hover/active de CTAs (úsalo cuando necesites distinguir state) |

### Neutros (slate scale Tailwind)

`white`, `slate-50` (bg secondary), `slate-200` (borders), `slate-500` (text secondary), `slate-700` (text body), `slate-900` (text headings).

### Accents funcionales (NO de marca — semánticos)

| Color | Tailwind | Uso |
|---|---|---|
| Success / vigente | `emerald-600`, `emerald-500` | Sello activo, mensajes de éxito, accent strip vigente |
| Warning / expiring | `amber-500` | Renovación próxima D-60..D-30, warnings |
| Destructive / rejected | `rose-600`, `rose-500` | Status rejected, errores, accent strip expirado |
| Info secondary (Filament) | `'info'` color name | Discriminación secundaria en badges admin (era `'purple'` legacy) |

### Tipografía

Inter desde fonts.bunny.net, weights 400, 500, 600, 700. Reglas de jerarquía en `BRAND.md` sección 4. **PROHIBIDO** `font-black`, `font-thin`, `font-extralight`.

### Radii

| Token | Tailwind | Uso |
|---|---|---|
| `--radius: 0.625rem` | `rounded-lg` | Default para botones, inputs |
| `rounded-xl` | 12px | Cards |
| `rounded-2xl` | 16px | Cards de contenedor / modales |

---

## 2. Patrones existentes en el código

Lo que sigue **ya está repetido en el codebase**. Si necesitás algo parecido, copiá de uno de estos ejemplos en vez de inventar uno nuevo.

### 2.1 Botón primario (Editorial Blue)

```html
<a class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
    Action
</a>
```

**Aparece en:** `site-header.blade.php`, `pricing.blade.php`, `home.blade.php`, `seal-renewal-info.blade.php`, `search.blade.php`. **Conteo grep:** ~30 ocurrencias.

### 2.2 Botón secundario / link

```html
<a class="text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-brand">Link</a>
```

**Aparece en:** site-header nav, footer links. **Conteo:** ~40 ocurrencias.

### 2.3 Card con shadow sutil (estilo BRAND.md)

```html
<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    ...
</div>
```

**Aparece en:** `badge/show.blade.php` (post Sprint C). **Conteo:** 3 ocurrencias.

### 2.4 Hero block Editorial Blue Deep

```html
<section class="bg-brand-deep py-16 text-white">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold sm:text-5xl">Título</h1>
        <p class="mx-auto mt-4 max-w-2xl text-blue-200">Subtítulo</p>
    </div>
</section>
```

**Aparece en:** `pricing.blade.php`, `methodology.blade.php` (variante emerald), `seal-renewal-info.blade.php`, `search.blade.php`, `about.blade.php`. **Conteo:** 5 heros públicos post-Sprint C.

### 2.5 Pill / badge informativo

```html
<span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-brand dark:bg-blue-900/30 dark:text-blue-400">
    Label
</span>
```

**Aparece en:** ~25 lugares (search filters, status badges, áreas temáticas).

### 2.6 Banner de aviso (amber warning)

```html
<div class="rounded-xl border border-amber-300 bg-amber-50 p-4 dark:border-amber-700 dark:bg-amber-950">
    <div class="flex items-start gap-3">
        <svg class="h-5 w-5 text-amber-600">...</svg>
        <p class="text-sm text-amber-800 dark:text-amber-200">Mensaje warning</p>
    </div>
</div>
```

**Aparece en:** `layouts/app.blade.php` (renewal toast), `editor-dashboard.blade.php`, banners de Sprint 3 #28.

### 2.7 Sello editorial (logo lockup en SVG)

```svg
<g fill="#FFFFFF" transform="translate(22 22) scale(0.4)">
    <rect x="8" y="8" width="14" height="84"/>
    <rect x="22" y="8" width="56" height="14"/>
    <rect x="78" y="8" width="14" height="32"/>
    <rect x="22" y="43" width="30" height="14"/>
    <rect x="22" y="78" width="56" height="14"/>
    <rect x="78" y="60" width="14" height="32"/>
</g>
```

**Referencia única:** [`resources/views/badge/seal.blade.php`](resources/views/badge/seal.blade.php) — composición canónica del sello (400×130, Editorial Blue Deep, accent strip lateral por estado).

### 2.8 Mark inline (logo en header/footer)

```html
<svg class="h-8 w-8 shrink-0" viewBox="0 0 100 100" fill="currentColor">
    <rect x="8" y="8" width="14" height="84"/>
    <rect x="22" y="8" width="56" height="14"/>
    <rect x="78" y="8" width="14" height="32"/>
    <rect x="22" y="43" width="30" height="14"/>
    <rect x="22" y="78" width="56" height="14"/>
    <rect x="78" y="60" width="14" height="32"/>
</svg>
```

**Aparece en:** `site-header.blade.php`, `layouts/app.blade.php` footer. Si necesitás la mark en otro lado, copiá este patrón y dejá que `text-brand` o `text-blue-300` controle el color via `currentColor`.

---

## 3. Reglas de aplicación

### 3.1 Cuándo usar `bg-brand` vs `bg-brand-deep`

- **`bg-brand`** (Editorial Blue #1E3A8A): CTAs principales, navegación activa, accent dots, indicadores compactos.
- **`bg-brand-deep`** (Editorial Blue Deep #172554): superficies grandes signature — heros, fondo del sello, blocks "promo" full-width. **Texto blanco obligatorio** + subtítulos `text-blue-200`.
- **No mezclar** los dos en el mismo block sin un divisor claro.

### 3.2 Indigo, purple, pink, violet — prohibidos

Eliminados del codebase en Sprint C (commit que cierra #52). Si aparecen en código nuevo, error.

### 3.3 Amber, emerald, rose — solo semánticos

- Amber: warning / atención (sello expiring, banners D-60).
- Emerald: success / vigente.
- Rose: destructive / rejected.

**No usar** como color de marca, ni en heros, ni en branding signature.

### 3.4 Tipografía

Ver `BRAND.md` sección 4. Resumen: Inter 400/500/600/700. h1 hero `text-3xl lg:text-4xl font-bold`. Body `text-base font-normal`. **Prohibido** `font-black`, `font-thin`, `font-extralight`.

### 3.5 Dark mode

EPIC #49 decidió **no tocar `dark:` del público**. Si tocás un archivo del público, mantené las variantes dark existentes. Filament admin tiene su propio dark mode nativo (toggle en topbar), Independent del público.

---

## 4. Deuda técnica conocida

Lo que NO se tocó en el EPIC #49 / Sprint 5 y queda como backlog:

### 4.1 Drift mediano no fixeado (~60 archivos)

Auditoría inicial (Sprint 5 #50) detectó 715 ocurrencias `indigo-` en 31 archivos blade y 245 `purple-` en 21. Sprint C fixeó el peor 25% (heros públicos + Filament admin + headers + sello + journal/show + review-listing). El resto sigue:

- `resources/views/livewire/editor-dashboard.blade.php` (1243 líneas) — todavía tiene indigo/purple en cards y badges
- `resources/views/livewire/book-submission-wizard.blade.php` (1051 líneas) — usa purple como "primary" semántico de books (decisión legacy)
- `resources/views/livewire/book-payment-checkout.blade.php` — idem
- `resources/views/livewire/submission-wizard.blade.php` (771 líneas) — wizard journals
- `resources/views/livewire/message-thread.blade.php` (685 líneas)
- `resources/views/livewire/editor-consulting-panel.blade.php` (490 líneas) — usa purple como accent de consultorías
- `resources/views/livewire/search-journals.blade.php` (386 líneas) — usa indigo vs purple para discriminación journals/books
- `resources/views/livewire/my-payments.blade.php`
- `resources/views/livewire/editor-messages-inbox.blade.php`

Razón de exclusión: el EPIC #49 declaró out-of-scope "refactor vistas Livewire grandes" para limitar superficie de cambio.

### 4.2 Biblioteca de componentes blade base

NO creada. Patrones se duplican manualmente (130x inputs, 65x botones primarios, 23x stat cards). El día que se decida crear `<x-button>`, `<x-card>`, `<x-modal>`, etc., será un sprint separado con migración gradual.

### 4.3 Modales Livewire propios

6 modales hechos a mano sin wrapper común:
- `components/cookie-banner.blade.php`
- `livewire/editor-consulting-panel.blade.php` (modales internos)
- `livewire/editor-dashboard.blade.php` (modales internos)
- `livewire/editor-messages-inbox.blade.php`
- `livewire/message-thread.blade.php`
- `livewire/my-payments.blade.php`

Patrón actual: `<div class="fixed inset-0 z-50 ...">`. Un futuro `<x-modal>` los unificaría.

### 4.4 Iconografía

**0 uso de Heroicons**. 214 SVGs inline crudos repetidos. Migración a `<x-heroicon-*>` (paquete `blade-heroicons`) queda en backlog.

### 4.5 Dark mode del público

2260 ocurrencias `dark:` en 50 archivos del sitio público, sin toggle visible al usuario. La decisión en Sprint A fue **conservar y NO deprecar**. Si en el futuro se decide quitarlas (porque nadie usa el modo oscuro), es un sprint dedicado.

### 4.6 Rasterización de assets

Los archivos PNG/ICO de marca (favicon.ico, apple-touch-icon.png, og-default.png, todas las variantes PNG de los lockups) **no fueron regenerados** porque WSL no tiene ImageMagick/Inkscape. Ver `BRAND.md` sección 11 para el TODO completo. Mientras tanto los browsers modernos usan `favicon.svg` directamente.

### 4.7 Logo source real

Los SVGs en `public/brand/` son **reconstrucciones internas** por inspección visual del brand guide entregado por el diseñador. Cuando llegue el zip con `.fig`/`.ai`/SVGs optimizados, swap directo sin cambios de integración.

---

## 5. Roadmap de evolución del sistema

Hoy el sistema es **plano** (tokens + patrones documentados, sin biblioteca de componentes). Si el proyecto crece y se decide evolucionar:

| Etapa | Trigger | Esfuerzo |
|---|---|---|
| **Hoy** (post EPIC #49) | — | — |
| **+1: Biblioteca de componentes blade** | Cuando aparezca la 4ª vista que necesite el mismo patrón de input/button/modal duplicado. | ~1 sprint |
| **+2: Migración Heroicons** | Cuando aparezca una decisión de iconografía custom que beneficie de un set unificado. | ~3-5 días |
| **+3: Refactor vistas Livewire grandes** | Cuando una de las vistas grandes tenga un bug visual que requiera tocar varias zonas. Refactor + tokens en el mismo PR. | ~1-2 sprints |
| **+4: Cleanup `dark:` público** | Si se decide deprecar dark mode del público (no se usa). | ~2-3 días |
| **+5: Storybook / preview de componentes** | Solo si la biblioteca de componentes crece a >15 elementos. Antes de eso, ejemplos en este `DESIGN.md` bastan. | ~1 semana |

Cada etapa se evalúa por separado. **Ninguna está priorizada hoy** — el EPIC #49 cerró con el sistema "plano" funcional, y la decisión del usuario fue priorizar producto sobre infraestructura visual.

---

## 6. Cómo agregar algo nuevo al sistema

Si vas a crear un nuevo componente / patrón:

1. **Primero buscá si ya existe.** Grep en `resources/views/` y `app/Filament/` por algo parecido.
2. **Si existe en 3+ lugares duplicado**, candidate para abstraer (ver sección 5, etapa +1).
3. **Si es nuevo**, seguí las reglas de `BRAND.md`:
   - Paleta: `bg-brand`, `bg-brand-deep`, `bg-brand-active` + neutros slate + accents funcionales.
   - Tipografía: Inter 400-700, sin `font-black`.
   - Espacios: `rounded-lg` botones, `rounded-xl` cards, `shadow-sm` máximo.
4. **Documentalo acá** (sección 2) cuando lo uses en al menos 2 lugares.
5. **No usés indigo/purple/pink/violet** — están vetados desde Sprint C.

---

## 7. Verificación rápida (CI futuro)

Comandos para validar que el codebase respeta el sistema:

```bash
# Cero indigo/purple/pink/violet en archivos nuevos del público (excluye Livewire grandes)
grep -rE "(indigo|purple|pink|violet)-" resources/views/ \
    --exclude="*editor-dashboard*" \
    --exclude="*submission-wizard*" \
    --exclude="*book-*" \
    --exclude="*message-thread*" \
    --exclude="*search-journals*" \
    --exclude="*my-payments*" \
    --exclude="*editor-messages*" \
    --exclude="*editor-consulting*" \
  | wc -l   # debe ser 0

# Cero font-black
grep -r "font-black" resources/views/ | wc -l   # debe ser 0

# Cero indigo/purple en admin Filament
grep -rE "(indigo|purple)" app/Filament/ | wc -l   # debe ser 0

# Cero gradientes purple
grep -rE "from-indigo.*to-purple|from-purple.*to-indigo" resources/views/ | wc -l   # debe ser 0
```

Si alguno de esos comandos devuelve >0 después de Sprint C cerrado, hay regresión.

---

## 8. Documentos relacionados

- [`BRAND.md`](BRAND.md) — manual canónico de marca (paleta, tipografía, sello, do/don't).
- [`BRAND_BRIEF.md`](BRAND_BRIEF.md) — proceso de discovery (15 respuestas + síntesis).
- [`BRAND_DIRECTION.md`](BRAND_DIRECTION.md) — exploración visual + moodboard.
- [`LOGO_BRIEF.md`](LOGO_BRIEF.md) — brief para diseñador externo.
- [`resources/css/app.css`](resources/css/app.css) — tokens CSS de la verdad.
