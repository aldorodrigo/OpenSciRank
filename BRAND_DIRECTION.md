# Brand Direction — Editorial Standards Platform

**Versión:** 1.1 · **Fecha:** 2026-05-16 · **Sprint:** 5 (EPIC #49 · Sub-issue #50)

Documento de cierre del Sprint A. Captura la dirección visual elegida que va a guiar Sprint B (Identidad: paleta, tipografía, sello) y Sprint C (Unificación quirúrgica + `DESIGN.md`).

Construido sobre [`BRAND_BRIEF.md`](BRAND_BRIEF.md). Si las respuestas del brief cambian, este documento debe revisarse.

**Convención de naming:** "Editorial Standards Platform" = nombre canónico. "Editorial Standards" = wordmark/marketing. "ESP" = initialism para monograma. Ver `LOGO_BRIEF.md` para detalle de uso en el sistema de identidad.

---

## Dirección elegida — "Compliance Editorial"

**Espíritu:** Stripe Atlas + Sentry Trust Center + B Corp Certification. Arquitectura modular y minimalista del lado tech, con presencia institucional de azul oxford dominante en zonas signature.

**Por qué esta dirección:** Es el cruce literal del eje "tech minimalista ↔ gravedad institucional" que definió el brief. No nos paramos del lado SaaS-puro (Stripe.com) ni del lado académico-clásico (Cambridge Press). Vivimos en el territorio que ocupan Stripe Atlas (producto legal con autoridad) o Vercel Security (compliance moderno).

### Decisiones de aplicación

| Aspecto | Decisión |
|---|---|
| **Oxford blue presence** | Estratégica: dominante en hero principal y bloques signature (página sello, evaluación, certificados); accent en el resto |
| **Backgrounds** | Blanco/cream dominante; oxford blue en bloques signature; slate-50 para secciones secundarias |
| **Cards y componentes** | Modulares estilo Stripe: espacio + hairlines 1px, sin shadows decorativos, sin gradientes |
| **Tipografía (Inter)** | Pesos 400 body, 500-600 énfasis, 700 solo en heros. Jerarquía clara sin pesos extremos (sin `font-black`) |
| **Densidad** | Media — más Stripe que Cambridge, sin caer en DOAJ-density |
| **Anchors institucionales** | Oxford blue en navegación activa, divisores decorativos en heros, badge "Certified", iconografía clave |
| **Sello editorial** | Rectangular notarial moderno (B Corp + Stripe Atlas), no plano tech badge ni notarial siglo XIX |

---

## Paleta preliminar (a refinar/aprobar en Sprint B)

Working values usando OKLCH (coherente con `app.css` actual) y mapeo a Tailwind utility para legibilidad.

### Primary — Editorial Blue (Oxford-inspired)

| Rol | Nombre | OKLCH | Hex aprox | Tailwind equivalente |
|---|---|---|---|---|
| Primary | Editorial Blue | `oklch(0.30 0.10 260)` | `#1E3A8A` | `blue-900` |
| Primary deep | Editorial Blue Deep | `oklch(0.20 0.08 260)` | `#172554` | `blue-950` |
| Primary accent | Editorial Blue Active | `oklch(0.40 0.13 260)` | `#1D4ED8` | `blue-700` |

**Uso esperado:**
- `Editorial Blue` — color de marca dominante: heros signature, navegación activa, CTAs primarios, links destacados, sello de fondo.
- `Editorial Blue Deep` — versión para fondos extensos donde necesitamos peso (hero principal full-bleed, header del admin si decidimos cambiar de amber).
- `Editorial Blue Active` — estados hover/active de CTAs, focus rings.

### Neutros (slate scale — sin cambios respecto al stack actual)

| Rol | Tailwind |
|---|---|
| Background principal | `white` |
| Background secundario | `slate-50` (`#F8FAFC`) |
| Hairlines / borders | `slate-200` (`#E2E8F0`) |
| Text body | `slate-700` (`#334155`) |
| Text secundario | `slate-500` (`#64748B`) |
| Text headings | `slate-900` (`#0F172A`) |

### Accents funcionales (no de marca)

| Rol | Tailwind | Uso |
|---|---|---|
| Success / sello vigente | `emerald-600` (`#059669`) | Sello activo, mensajes de éxito |
| Warning / sello expirando | `amber-500` (`#F59E0B`) | Aviso de renovación, sello próximo a vencer |
| Destructive / rechazado | `rose-600` (`#E11D48`) | Status rejected, errores de validación |

**Decisión clave sobre la dualidad amber/indigo del drift actual:**

> La dualidad se resuelve en favor de **Editorial Blue (oxford-inspired)** como primary único.
> - **Admin Filament** (`Color::Amber` hoy): se cambia a un color custom basado en Editorial Blue en Sprint B. Esto contradice ligeramente la restricción "minimum changes to Filament" pero es necesario: mantener amber sería conservar el drift que este EPIC busca resolver.
> - **Sitio público** (`indigo-600` hoy): se reemplaza por Editorial Blue en Sprint C.
> - **Amber pasa a ser color funcional de "warning"** (sello expirando, llamados a renovar), no de marca.

---

## Tipografía

**Inter, sin serif.** Confirmado en brief #14 — descartamos camino editorial-clásico (Nature/Conversation con serif).

| Rol | Weight | Tamaño aprox | Uso |
|---|---|---|---|
| Hero h1 | 700 | `text-3xl` a `text-5xl` | Heros signature solamente |
| Section h1/h2 | 600 | `text-2xl` a `text-3xl` | Headings de sección |
| h3 | 600 | `text-xl` | Subheadings |
| h4-h6 | 600 | `text-base` a `text-lg` | Cards, labels destacados |
| Body | 400 | `text-base` | Texto corrido |
| Body énfasis | 500 | `text-base` | Negrita semántica leve |
| Caption / metadata | 400-500 | `text-xs` a `text-sm` | Pie de cards, timestamps, labels |
| Wordmark / brand | 600 | variable | "Editorial Standards Platform" (canónico) o "Editorial Standards" (wordmark) en logo, sello, header. Decisión final en `LOGO_BRIEF.md`. |

**Reglas:**
- **No usar `font-black` (peso 900) nunca más.** Restricción dura — apareció en el drift actual de `evaluate-journal.blade.php` (fixeado en #48), `review-listing.blade.php` y `journal/show.blade.php` (pendientes de fixear en Sprint C).
- **No usar `font-thin`/`font-extralight` (100-200).** Inter en pesos livianos no se lee bien en dispositivos no-retina, pierde autoridad.
- **`tracking-tight` solo en heros muy grandes** (`text-4xl+`). Para `text-base`, Inter ya viene con el tracking correcto.
- **Inter Display** queda en backlog. Sprint B no lo carga; si más adelante el wordmark del logo lo necesita, se evalúa.

---

## Iconografía

- **Adopción gradual de Heroicons** (`@blade-heroicons` o copy-paste según necesidad). Out-of-scope migrar los 214 SVGs inline en este EPIC — se hace en sprint futuro.
- **Estilo:** outline 24x24, `stroke-width="1.5"` o `2`. Sin fills decorativos.
- **Color de icono:** `currentColor` siempre. Hereda del contexto (text-slate-500 para iconos secundarios, text-blue-900 para iconos signature).

---

## Sello editorial — referencia visual

**Cluster de referencias:** Vercel Security badges, B Corp Certification badge, Stripe Climate, CNCF Certified, OWASP Trusted.

**Forma:**
- Rectangular horizontal (~3:1 aprox, ~400×130px referencia).
- Esquinas redondeadas suaves (`rounded-lg` ≈ 0.5rem ≈ 8px), no pill ni circular.
- Versión square (~1:1, ~160×160px) para embebidos en cards y sitios con poco espacio.

**Composición:**
- Background: Editorial Blue Deep (`oklch(0.20 0.08 260)` / `#172554`) sólido. Sin gradientes.
- Wordmark "Editorial Standards Seal" en Inter 600/700, white. Alineado a la izquierda.
- Score (porcentaje) en rectángulo destacado a la derecha — no en círculo (círculo se asocia a medalla/B+ Corp).
- Año de vigencia debajo del score, Inter 500, slate-300.
- Microcopy de verificación (`editorialstandards.org/verify/{id}` o similar) en pie, Inter 400, blue-300 con opacidad.

**Estados:**
- **Vigente:** background Editorial Blue Deep + accent strip emerald-500 a la izquierda (4-6px de ancho).
- **Expirado:** background slate-700 + accent strip rose-500. Texto en slate-300. Visualmente "apagado" pero legible.
- **Vista previa / borrador:** outline en oxford blue sobre fondo blanco, no fill.

**Archivo a refinar:** [`resources/views/badge/seal.blade.php`](resources/views/badge/seal.blade.php). Ya tiene una base SVG con texto, score, year, versión expirada. Sprint B reescribe la composición siguiendo esta dirección.

---

## Referencias visuales (moodboard)

### Cluster 1 — Tech con autoridad institucional (dominante)

1. [Stripe Atlas](https://stripe.com/atlas) — referencia ancla. Producto legal con autoridad + estética Stripe.
2. **[Vercel Security](https://vercel.com/security)** ⭐ — modelo directo de cómo se ve una página de compliance/auditoría moderna. Banners de certificación, whitespace, structure tabular. **Priorizado por el usuario.**
3. [Sentry Trust Center](https://sentry.io/trust) — grilla de banderas de compliance, copy formal.
4. [Stripe](https://stripe.com) — referencia base de la estética Stripe.
5. [Linear](https://linear.app) — minimalismo y autoridad sin decoración.
6. [Plausible Analytics](https://plausible.io) — minimalismo riguroso, demuestra que se puede ser plano sin ser aburrido.

### Cluster 2 — Universidades editoriales modernas (calibración institucional)

7. [Cambridge University Press](https://www.cambridge.org) — oxford blue profundo, jerarquía editorial clásica modernizada.
8. [MIT Press](https://mitpress.mit.edu) — tipografía con peso, layouts editoriales, paleta acotada.
9. [Oxford University Press](https://global.oup.com) — origen del "oxford blue" como categoría visual.
10. [Princeton University Press](https://press.princeton.edu) — editorial académica seria con web moderna.

### Cluster 3 — Sellos / certificados modernos (referencia directa del seal)

11. **[B Corp Certification](https://www.bcorporation.net)** ⭐ — el mejor ejemplo contemporáneo de sello rectangular oficial sobrio que las marcas exhiben con orgullo. **Priorizado por el usuario.** Mirar cómo lo usan Patagonia, Allbirds, Ben & Jerry's.
12. [Stripe Climate badge](https://stripe.com/climate) — sello plano embebible.
13. [CNCF Certified (Kubernetes)](https://www.cncf.io/certification) — sello "verified compliance" en cards de productos.
14. [Berkeley Open Source Office](https://opensource.berkeley.edu) — institucional moderno.
15. [OWASP Trusted](https://owasp.org) — sellos rectangulares de cumplimiento técnico.

### Cluster 4 — Competencia directa (diferenciación)

16. [DOAJ](https://doaj.org) — institucional un poco viejo, layouts densos, paleta vibrante. Lo que NO queremos.
17. [SciELO](https://scielo.org) — colorido, naranja-rojo dominante. Lo opuesto a nuestra dirección.
18. [Latindex](https://www.latindex.org) — institucional clásico marcado, ergonomía vieja.
19. [ORCID](https://orcid.org) — referencia indirecta. Verde como primary, layouts limpios.

---

## Tensiones a vigilar en Sprint B

Heredadas del brief:

1. **Moderno vs institucional** — vivimos en el medio. Cada decisión concreta (un componente, un margen, un color) tiene que justificar por qué se inclina hacia un lado.
2. **Sello primary vs evaluación-sustento** — el sello es la cara visible pero no podemos descuidar la evaluación. Reservar espacio comunicacional para ambos.
3. **Distancia formal vs frialdad** — tono usted + 3ª persona puede leerse frío. Compensar con claridad de servicio, no con calidez de copy.

Específica de esta dirección:

4. **Cambio de primary en Filament admin** — la decisión de unificar a Editorial Blue va contra la restricción "minimum Filament changes" del EPIC. Está justificado porque mantener amber perpetúa el drift. Documentar la excepción explícitamente en `BRAND.md` (Sprint B) y aplicarla en Sprint C.

---

## Próximos pasos — Sprint B (issue #51)

Outputs esperados:

1. **`resources/css/app.css`** — actualizar tokens OKLCH para introducir Editorial Blue como primary. Verificar contraste WCAG AA en backgrounds.
2. **`app/Providers/Filament/AdminPanelProvider.php`** — cambiar `Color::Amber` por un Color custom o `Color::Blue` con override OKLCH a Editorial Blue.
3. **`resources/views/badge/seal.blade.php`** — refinamiento mayor del sello según las especificaciones de este documento.
4. **Tipografía** — confirmar que Inter cargado desde fonts.bunny.net incluye los weights necesarios (400, 500, 600, 700 ya cargados). No agregar Inter Display por ahora.
5. **`BRAND.md`** — manual de marca consolidado: este documento + `BRAND_BRIEF.md` + decisiones tomadas en Sprint B, con ejemplos do/don't.
6. **Opcional:** brief para diseñador profesional si se quiere logo nuevo. Yo armo el brief, vos contratás (out of scope sin ese paso).

Sprint C (issue futura, post Sprint B) aplica las decisiones al código existente con commits surgicales.
