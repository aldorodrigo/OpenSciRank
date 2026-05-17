# Brand Manual — Editorial Standards Platform

**Versión:** 1.0 · **Fecha:** 2026-05-17 · **Sprint:** 5 (EPIC #49 · Sub-issue #51)

Manual canónico de marca consolidado. Reemplaza/supera a `BRAND_BRIEF.md` (decisiones de discovery) y `BRAND_DIRECTION.md` (exploración visual) como fuente de verdad operativa: aquí están las decisiones finales y las reglas de uso.

Cuando haya conflicto: **este documento gana**. Los otros dos quedan como referencia histórica del proceso.

---

## 1. La marca en una frase

> **"Acreditación editorial técnica con sello reconocible."**

Editorial Standards Platform certifica revistas académicas contra estándares editoriales actualizados y emite un sello editorial visible que las revistas certificadas embeben en sus sitios.

**Esencia visual:** tech minimalista con gravedad institucional. Sweet spot entre Stripe Atlas y Cambridge University Press.

---

## 2. Convención del nombre de marca

| Forma | Cuándo se usa |
|---|---|
| **Editorial Standards Platform** | Nombre canónico/legal. Documentos formales, footer legal, primer mención en cualquier comunicación, SEO meta tags. |
| **Editorial Standards** | Wordmark / marketing — abreviación natural (como "Stripe" vs "Stripe, Inc."). Wordmark visual del logo, headers, navegación, copy informal. |
| **ESP** | Initialism — sólo para monograma/favicon o referencias internas. Nunca en comunicación al usuario sin contexto. |

**Implementación actual:** `AdminPanelProvider::brandName('Editorial Standards')` usa la forma abreviada como wordmark del admin. `config('app.name')` y meta tags usan "Editorial Standards Platform" (canónica).

---

## 3. Paleta de colores

### Primary — Editorial Blue (oxford-inspired)

Definida en `resources/css/app.css` como custom properties OKLCH, expuesta a Tailwind como `bg-brand`, `bg-brand-deep`, `bg-brand-active`.

| Rol | Token CSS | OKLCH | Hex | Equivalente Tailwind |
|---|---|---|---|---|
| **Editorial Blue** | `--brand` / `bg-brand` | `oklch(0.30 0.10 260)` | `#1E3A8A` | `blue-900` |
| **Editorial Blue Deep** | `--brand-deep` / `bg-brand-deep` | `oklch(0.20 0.08 260)` | `#172554` | `blue-950` |
| **Editorial Blue Active** | `--brand-active` / `bg-brand-active` | `oklch(0.40 0.13 260)` | `#1D4ED8` | `blue-700` |

**Uso:**
- `Editorial Blue` — primary dominante: CTAs, links destacados, navegación activa, sello (wordmark), badges institucionales.
- `Editorial Blue Deep` — superficies signature: fondo del hero principal full-bleed, fondo del sello editorial, header del admin si se usa modo oscuro.
- `Editorial Blue Active` — estados hover/active de CTAs, focus rings.

### Neutros (slate scale Tailwind, sin cambios)

| Rol | Tailwind | Hex |
|---|---|---|
| Background principal | `bg-white` | `#FFFFFF` |
| Background secundario | `bg-slate-50` | `#F8FAFC` |
| Hairlines / borders | `border-slate-200` | `#E2E8F0` |
| Text body | `text-slate-700` | `#334155` |
| Text secundario | `text-slate-500` | `#64748B` |
| Text headings | `text-slate-900` | `#0F172A` |

### Accents funcionales (no de marca)

Estos colores **no representan a la marca**. Sirven sólo para señalética semántica.

| Rol | Tailwind | Hex | Uso |
|---|---|---|---|
| Success / sello vigente | `emerald-600` | `#059669` | Sello activo, mensajes de éxito, accent strip del sello vigente |
| Warning / sello expirando | `amber-500` | `#F59E0B` | Aviso de renovación próxima, sello D-60 a D-30. **No usar como color de marca**. |
| Destructive / rechazado | `rose-600` | `#E11D48` | Status rejected, errores de validación, accent strip del sello expirado |

---

## 4. Tipografía

**Inter** como única familia tipográfica. Cargada desde fonts.bunny.net en pesos 400, 500, 600, 700 ([`layouts/app.blade.php:62`](resources/views/components/layouts/app.blade.php)).

### Jerarquía permitida

| Rol | Weight | Tamaño Tailwind | Uso |
|---|---|---|---|
| Hero h1 | 700 | `text-3xl` a `text-5xl` | Sólo heros signature (home, pricing, sello) |
| Section h1/h2 | 600 | `text-2xl` a `text-3xl` | Headings de sección |
| h3 | 600 | `text-xl` | Subheadings |
| h4-h6 | 600 | `text-base` a `text-lg` | Cards, labels destacados |
| Body | 400 | `text-base` | Texto corrido |
| Body énfasis | 500 | `text-base` | Negrita semántica leve |
| Caption / metadata | 400-500 | `text-xs` a `text-sm` | Pie de cards, timestamps, labels |
| Wordmark | 600 | variable | "Editorial Standards" en logo, sello, header |

### Reglas duras

- **PROHIBIDO `font-black` (peso 900).** Drift histórico fixeado parcialmente en commit #48; pendiente Sprint C (#52) para `review-listing.blade.php` y `journal/show.blade.php`.
- **PROHIBIDO `font-thin` y `font-extralight` (pesos 100-200).** Inter en pesos livianos pierde legibilidad y autoridad.
- **`tracking-tight` solo en heros `text-4xl+`.** Para tamaños body, Inter ya viene con tracking óptimo.
- **No usar serifs** en ningún contexto del sistema. La marca elige sans-serif explícitamente (Brand Brief #14).

---

## 5. Sello editorial

**Activo más estratégico de la marca** — es lo que las revistas certificadas embeben en sus sitios y transmite autoridad sin contexto adicional.

### Especificación visual

**Dimensiones:** 400 × 130 px (proporción ~3:1). Esquinas `rx="8"` (≈ `rounded-lg`).

**Composición** (3 elementos visuales máximo, sin decoración):
- Wordmark izquierda: "EDITORIAL STANDARDS" + "SEAL · CERTIFIED" (dos líneas, Inter 600/500, uppercase con letter-spacing).
- Tagline + URL de verificación debajo del wordmark.
- Score block derecho: rectángulo (NO círculo) con porcentaje + "SCORE" + año.

**Estados:**

| Estado | Background | Accent strip izquierda (6px) | Texto |
|---|---|---|---|
| **Vigente** | `#172554` (Editorial Blue Deep) | `#10B981` (emerald-500) | Blanco / blue-300 |
| **Expirado** | `#334155` (slate-700) | `#F43F5E` (rose-500) | Slate-300/400/500 (todo mutado) |
| **Borrador** (futuro) | `transparent` con outline | Sin strip | Editorial Blue sobre blanco |

**Tipografía:** `Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif`. El stack de fallback garantiza render decente en sitios de terceros donde Inter podría no estar cargado.

**Archivo:** [`resources/views/badge/seal.blade.php`](resources/views/badge/seal.blade.php).

### Reglas del sello

- **No modificar la composición** del sello al embeberse — sólo se ofrece como SVG completo o PNG generado.
- **No agregar fondos coloreados** detrás del sello. Si el sitio de la revista tiene un background oscuro, el sello mantiene su Editorial Blue Deep — alto contraste suficiente.
- **Tamaño mínimo:** 280 px de ancho (proporción mantenida). Por debajo de eso, el wordmark se vuelve ilegible.

---

## 6. Iconografía

- **Heroicons (outline)** como sistema preferido. Adopción gradual; los SVGs inline existentes se migran cuando se toque el archivo, no en masa (out of scope del EPIC #49).
- **Estilo:** outline 24×24, `stroke-width="1.5"` o `2`. Sin fills decorativos.
- **Color:** `currentColor` siempre. El icono hereda del contexto (`text-slate-500` para secundarios, `text-brand` para signature).
- **Tamaños:** 16, 20, 24 px estándar. Iconos custom (sello, score, evaluación) se diseñan caso por caso siguiendo el mismo grid 24×24.

---

## 7. Reglas de uso — Do / Don't

### DO

- Usar `bg-brand` / `text-brand` / `border-brand` (clases Tailwind generadas desde los tokens) para todos los elementos signature de la marca.
- Mantener jerarquía tipográfica clara: máximo 3 weights distintos por pantalla.
- Mucho espacio negativo. La densidad del layout debe ser media (más Stripe que DOAJ).
- Cards modulares planas: `border border-slate-200 bg-white` sin shadow decorativo (`shadow-sm` máximo si hay elevación).
- Tono "usted" + 3ª persona institucional en todo copy comercial.

### DON'T

- Usar `font-black`, `font-thin`, `font-extralight`.
- Usar `blur-3xl`, `blur-2xl`, `backdrop-blur-*` decorativos.
- Usar shadows coloreados (`shadow-brand-200`, `shadow-emerald-500/40`, etc.).
- Usar gradientes de marca (`bg-gradient-to-r from-... to-...`) — usar siempre fondos sólidos. La excepción es funcionalidad pura como progress bars.
- Mezclar `indigo-*` o `purple-*` en código nuevo. Existen como drift histórico; Sprint C los limpia.
- Usar emojis en UI institucional (Status badges, headers, navigation). Aceptable sólo en copy editorial conversacional (blog, notificaciones internas).
- Usar tipografías serif en ningún contexto del sistema.

---

## 8. Excepción documentada — Filament admin

**Decisión Sprint 5 (2026-05-17):** Filament admin cambia de `Color::Amber` (legacy) a `Color::hex('#1E3A8A')` (Editorial Blue) en [`app/Providers/Filament/AdminPanelProvider.php`](app/Providers/Filament/AdminPanelProvider.php).

**Por qué es una excepción:** El EPIC #49 estableció como restricción "Filament queda intacto salvo que una decisión de marca lo exija". El brand brief resolvió la dualidad amber/indigo unificando a un único primary (Editorial Blue), lo que obliga a tocar Filament — la alternativa (mantener amber en admin) perpetuaría el drift de marca que este EPIC busca resolver.

**Alcance del cambio:** únicamente el `'primary'` color de Filament. Todo el resto del admin (componentes, layouts, tipografías) queda igual — Filament aplica el nuevo primary automáticamente a botones, tabs, focus rings y navegación.

**Verificación pendiente (Sprint C #52):** capturar screenshots before/after de 5 vistas admin clave (Journals list, Evaluate, AdminTasks, Payments, Dashboard) para confirmar que no hay regresiones de contraste o expectativa visual.

---

## 9. Tokens en código — referencia rápida

### CSS (resources/css/app.css)

```css
@theme {
    --color-brand: var(--brand);
    --color-brand-deep: var(--brand-deep);
    --color-brand-active: var(--brand-active);
}

:root {
    --brand: oklch(0.30 0.10 260);
    --brand-deep: oklch(0.20 0.08 260);
    --brand-active: oklch(0.40 0.13 260);
}
```

### Tailwind utilities resultantes

- `bg-brand`, `text-brand`, `border-brand`, `ring-brand`
- `bg-brand-deep`, `text-brand-deep`
- `bg-brand-active`, `hover:bg-brand-active`

### Filament admin

```php
->colors([
    'primary' => Color::hex('#1E3A8A'),
])
```

---

## 10. Documentos relacionados

- [`BRAND_BRIEF.md`](BRAND_BRIEF.md) — proceso de discovery (15 respuestas + síntesis).
- [`BRAND_DIRECTION.md`](BRAND_DIRECTION.md) — exploración visual + moodboard de 19 referencias.
- [`LOGO_BRIEF.md`](LOGO_BRIEF.md) — brief para diseñador profesional externo de logo.
- `DESIGN.md` (a crear Sprint C #52) — sistema de diseño operativo: patrones, componentes existentes, regla de aplicación de tokens.

---

## 11. Mantenimiento del manual

Este manual se actualiza:

- Cada vez que se toma una decisión de marca nueva en código (commit) que afecta paleta, tipografía, sello o componentes signature.
- Cada vez que aparece una excepción documentada (como Filament).
- Versionado: incrementar `Versión` al inicio del archivo. Cambios menores: 1.0 → 1.1. Cambios mayores (paleta, tipografía): 1.x → 2.0.

Cualquier conflicto entre este manual y el código gana **el manual** — el código se ajusta. Si el manual está mal, se discute, se actualiza, y luego se aplica al código.
