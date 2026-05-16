# Logo Design Brief — Editorial Standards Platform

**Versión:** 1.0 · **Fecha:** 2026-05-16 · **Para:** diseñador/a profesional contratado externamente · **Stack interno:** Laravel 12 + Tailwind 4

Este documento contiene todo lo que el diseñador necesita para producir el logo de **Editorial Standards Platform**. Va acompañado de `BRAND_BRIEF.md` (decisiones de marca) y `BRAND_DIRECTION.md` (dirección visual + moodboard).

---

## 1. Sobre Editorial Standards Platform

Editorial Standards Platform **certifica revistas académicas** contra estándares editoriales técnicos y de open access actualizados. Evalúa revistas, las puntúa contra criterios ponderados, y emite un sello editorial con validez anual que las revistas embeben en sus sitios para transmitir autoridad a autores, instituciones y lectores.

**Audiencia:** editores y directores de revistas académicas globales, e instituciones (universidades, sociedades científicas) que indexan sus revistas asociadas. Audiencia técnica sofisticada — entienden DOI, ORCID, peer-review, open access sin que les expliquemos los conceptos.

**Competencia directa:** DOAJ, SciELO, Latindex.

**Diferencial:** estándares más actualizados + sello visible/comercial que las otras plataformas no ofrecen.

**Modelo:** suscripción anual / pago por evaluación + sello.

---

## 2. Convenciones del nombre de marca

| Forma | Cuándo se usa |
|---|---|
| **Editorial Standards Platform** | Nombre legal y canónico. Documentos formales, footer legal, primer mención en cualquier comunicación. |
| **Editorial Standards** | Wordmark/marketing — uso casual y abreviación natural. Como "Stripe" vs "Stripe, Inc." |
| **ESP** | Initialism — sólo para monograma/favicon o referencias internas. No en comunicación al usuario sin contexto. |

El diseñador elige cuál forma trabaja mejor en el wordmark según legibilidad y composición. Idealmente las 3 formas conviven coherentemente (mismo carácter visual).

---

## 3. Lo que necesitamos

### Logo principal (lockup)
- **Wordmark** — la marca tipográfica principal de "Editorial Standards Platform" (o "Editorial Standards" si el diseñador recomienda acortar). Con o sin símbolo según concepto.
- **Símbolo/monograma** (opcional pero recomendado) — un mark autónomo, ~40×40px, que funcione sin texto. Para favicon, app icon, avatar social, mark embebido en el sello editorial.

### Variantes de lockup
- **Horizontal** — wide, para headers, navbar.
- **Stacked** — vertical, para footers, signature areas, heros centered.
- **Mark-only** — sólo símbolo, para contextos muy pequeños (favicon 16px) o cuando ya hay contexto (avatar de perfil).

### Versiones de color
Cada lockup en:
- **On-light:** marca en color sobre fondo blanco/claro (uso primario).
- **On-dark:** marca en blanco sobre fondo Editorial Blue Deep (`#172554`) — uso principal del sello editorial embebido en sitios de revistas.
- **Monochrome black** — versión 1-color para impresión b/n, signatures legales.

### Tamaños mínimos garantizados
- Favicon: 16px (el mark/monograma debe ser legible).
- Header de web: 80-200px de alto.
- Hero / marketing: hasta 800px sin pérdida (vector).
- Print: hasta 2 metros (vector escalable).

---

## 4. Atributos de marca (qué debe transmitir el logo)

### Sí

- **Técnico** — preciso, riguroso, no decorativo.
- **Transparente** — claro, legible, sin metáforas opacas.
- **Moderno** — contemporáneo, no nostálgico, no clásico-académico literal.

### No

- **Inaccesible** — el logo no debe leerse como elitista, hermético o intimidante.
- **Institucional viejo** — no escudos heráldicos, no sellos notariales del siglo XIX, no tipografías serif decimonónicas.
- **Amateur** — sin clipart, sin efectos default de software, sin elementos que se vean "hechos rápido".

### Esencia sintetizada
**"Tech minimalista con gravedad institucional."** El sweet spot que ocupan **Stripe Atlas**, **Vercel Security** y **Sentry Trust Center**: estética plana y moderna pero registro de autoridad técnica formal. No es ni startup canchera ni academia polvorienta.

---

## 5. Dirección visual heredada del brand book

### Color (paleta de marca)

| Rol | OKLCH | Hex aprox | Tailwind |
|---|---|---|---|
| **Editorial Blue** (primary) | `oklch(0.30 0.10 260)` | `#1E3A8A` | `blue-900` |
| **Editorial Blue Deep** (signature surfaces, sello) | `oklch(0.20 0.08 260)` | `#172554` | `blue-950` |
| **Editorial Blue Active** (hover, focus) | `oklch(0.40 0.13 260)` | `#1D4ED8` | `blue-700` |

**Neutros:** slate scale (`white`, `#F8FAFC`, `#E2E8F0`, `#334155`, `#0F172A`).

**El logo NO puede usar colores fuera de esta paleta.** No purples, no oranges, no greens como elemento principal.

### Tipografía del sistema (referencia)

El sitio y admin usan **Inter** (sans-serif), pesos 400-700. El logo no tiene obligación de usar Inter en el wordmark — el diseñador puede explorar tipografías de display (Söhne, Untitled Sans, Inter Display, GT America, Söhne Mono, geometric humanist sans-serif moderna). Pero el carácter del wordmark debe sentirse coherente con Inter en el resto del sistema. Idealmente, una sans-serif moderna sin demasiada personalidad propia (no rounded, no condensed, no display script).

**Prohibido:** serifs decorativas, tipografías script, tipografías con personalidad muy marcada (tipo Comic Sans pero también tipo Bebas Neue).

---

## 6. Referencias visuales (moodboard)

### Cluster principal — el territorio donde queremos vivir

- [Stripe Atlas](https://stripe.com/atlas) — wordmark + minimalismo + autoridad legal
- [Vercel Security](https://vercel.com/security) — compliance moderno, layouts editoriales
- [Sentry Trust Center](https://sentry.io/trust) — grilla de banderas de compliance
- [Stripe](https://stripe.com) — referencia base de estética Stripe
- [Linear](https://linear.app) — minimalismo y autoridad sin decoración
- [Plausible Analytics](https://plausible.io) — minimalismo riguroso

### Cluster secundario — autoridad institucional moderna

- [Cambridge University Press](https://www.cambridge.org) — oxford blue, jerarquía editorial moderna
- [MIT Press](https://mitpress.mit.edu) — tipografía con peso, paleta acotada
- [Princeton University Press](https://press.princeton.edu) — editorial académica seria con web moderna

### Cluster sello/certificación — para inspiración del monograma y aplicación

- [B Corp Certification](https://www.bcorporation.net) — el mejor ejemplo de "sello rectangular oficial sobrio que las marcas exhiben con orgullo"
- [Stripe Climate badge](https://stripe.com/climate) — sello plano embebible
- [CNCF Certified (Kubernetes)](https://www.cncf.io/certification) — sello "verified compliance"
- [OWASP Trusted](https://owasp.org) — sellos rectangulares de cumplimiento técnico

### Cluster anti-referencia — explícitamente lo que NO queremos

- [DOAJ](https://doaj.org) — institucional un poco viejo, layouts densos, paleta vibrante. NO queremos este feel.
- [SciELO](https://scielo.org) — colorido naranja-rojo dominante. NO queremos.
- [Latindex](https://www.latindex.org) — institucional clásico, ergonomía vieja. NO queremos.

---

## 7. Editorial Standards Seal (contexto importante)

El producto principal de la plataforma es un **sello editorial rectangular** (~400×130px) que las revistas certificadas embeben en sus sitios. **El sello se diseña internamente siguiendo la identidad de marca** que produzca el diseñador; no es parte de esta entrega.

Sin embargo, el logo/monograma que diseñe **debe funcionar bien colocado dentro del sello** (top-left, en blanco sobre fondo Editorial Blue Deep), porque el sello es el activo más estratégico y más visible de la marca — es lo que las revistas certificadas exhiben en sus sitios externos.

Imaginar el contexto:
- El sello aparece en sitios de terceros (revistas académicas), sobre fondos que no controlamos.
- Debe transmitir autoridad por sí solo, sin contexto adicional.
- El logo dentro del sello debe ser reconocible incluso a tamaño reducido.

---

## 8. Entregables esperados

**Master source:**
- Archivo vectorial editable: `.fig` (Figma preferido) o `.ai` (Illustrator). Outlines u texto editable, ambos OK siempre que el texto editable use fuente disponible públicamente o se incluya el archivo de fuente.

**Exports:**
- Cada lockup (horizontal, stacked, mark-only) × cada versión de color (on-light, on-dark, monochrome) × cada formato (SVG, PNG @1x/@2x/@3x) — aproximadamente **27-36 archivos**.
- Favicon set: `.ico` + PNG 16/32/180/512 + `apple-touch-icon.png` + SVG.

**Brand guide PDF (4-8 páginas):**
- Logo y variantes
- Clear space / espacio mínimo alrededor
- Tamaños mínimos por contexto
- Paleta de color con OKLCH + Hex + Tailwind class
- Do / don't con ejemplos
- Aplicación en 3 contextos: header web (light), favicon, sello embebido en sitio de tercero (Editorial Blue Deep background)

---

## 9. Don'ts (no hacer)

- **No tipografías serif** para el wordmark (excepción para tagline si tiene sentido conceptual, pero el wordmark principal debe ser sans).
- **No gradientes, sombras, efectos 3D, glow, glassmorphism.**
- **No clichés académicos** — nada de libros abiertos, plumas de escribir, columnas griegas, búhos, birretes, balanzas, globos terráqueos, átomos, hojas de laurel, sellos de cera.
- **No símbolos genéricos de "verificado"** — nada de checkmarks tipo Trustpilot/Adobe Approved/Twitter Verified. Demasiado obvio y poco distintivo.
- **No mascotas, ilustraciones, personajes.** Tono institucional.
- **No colores fuera de la paleta** (no purples, no oranges, no greens como protagonistas).
- **No initials-only** como logo principal — el monograma puede vivir solo en contextos pequeños, pero el lockup principal debe incluir nombre legible.
- **No tipografías que sean tendencia 2024-2026** — el logo tiene que envejecer bien (5+ años).

---

## 10. Proceso esperado

### Ronda 1 — Concepts (estimado: 1-2 semanas)
Diseñador presenta **3-4 conceptos distintos** como PNG mockups (sin entregar source). Cada concepto incluye:
- Wordmark + monograma propuesto
- Lockup horizontal y stacked
- Aplicación en 2 contextos: header web + sello editorial (mock)
- 2-3 líneas explicando el concepto

Diseñador **recomienda 1 concepto** y justifica.

### Ronda 2 — Refinement (estimado: 1 semana)
Sobre el concepto elegido (o un híbrido), refinamiento hasta versión final:
- Todas las variantes de lockup
- Todas las versiones de color
- Tipografía finalizada
- Sistema de tamaños y clear space
- Hasta **2 ciclos de revisión** incluidos en la cotización.

### Ronda 3 — Delivery (estimado: 3-5 días)
- Source files
- Exports completos
- Brand guide PDF

**Timeline total esperado:** 3-4 semanas trabajo de freelance individual.

---

## 11. Presupuesto orientativo

| Tipo de proveedor | Rango USD | Notas |
|---|---|---|
| Freelance individual (LATAM) con portfolio sólido | $500 - 1,500 | Buen balance de calidad y costo |
| Freelance senior / estudio boutique | $2,000 - 5,000 | Para múltiples revisiones, sistema completo, alta personalización |
| Plataformas tipo 99designs, Fiverr | $300 - 800 | Calidad muy variable, **NO recomendado** para nuestro caso por la importancia del sello |

**Recomendación:** freelance individual con experiencia en identidad para tech / SaaS / compliance / publishing. Evitar generalistas y diseñadores sin portfolio de identidad publicado.

---

## 12. Criterios para seleccionar diseñador/a

**Debe tener:**
- Portfolio con al menos **3 proyectos de identidad** publicados (no solo de redes sociales).
- Trabajos previos en al menos uno de estos rubros: tech / SaaS, academic publishing, fintech, legal services, compliance, audit, biotech.
- Capacidad de entregar source en formato moderno (Figma preferido, AI aceptado).
- Comunicación clara en español (idioma principal del cliente).

**Bonus:**
- Experiencia con sistemas de diseño, no solo logos sueltos.
- Comodidad con OKLCH y paletas en color spaces modernos.
- Portfolio que incluya badges/certificados/sellos (no obligatorio pero relevante).

**Red flags:**
- Sólo muestra trabajos para redes sociales o YouTube thumbnails.
- Portfolio dominado por templates personalizados (sin identidades originales).
- Tiempos prometidos < 1 semana (calidad sospechosa).
- Sin contrato propuesto / sin clear terms de revisiones y derechos.

---

## 13. Derechos y licencia

El cliente (Editorial Standards Platform) debe recibir:
- **Cesión completa de derechos** sobre el logo final y todas sus variantes.
- **Source files editables** sin restricciones de uso futuro.
- **Confirmación escrita** de que el logo es original y no infringe marcas existentes (el diseñador hace búsqueda básica de trademark).

---

## 14. Archivos a enviarle al diseñador

Al contratar, envíale:
- `LOGO_BRIEF.md` (este documento)
- `BRAND_BRIEF.md` (15 respuestas de marca + síntesis)
- `BRAND_DIRECTION.md` (dirección visual + 19 referencias completas)
- Acceso (read-only) al sitio actual en staging si querés que vea el contexto donde el logo va a vivir
- Link al sello actual `resources/views/badge/seal.blade.php` para que entienda el contexto del producto

---

## 15. Punto de contacto interno

El brief lo aprobó el equipo de Editorial Standards Platform (Sprint 5 — EPIC #49, sub-issue #51). Decisiones de marca tomadas el 2026-05-16. Cualquier duda del diseñador se canaliza al equipo de producto vía email/Linear/issue tracking del cliente.
