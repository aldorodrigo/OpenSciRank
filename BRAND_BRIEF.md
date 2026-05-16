# Brand Brief — Editorial Standards

**Versión:** 1.0 · **Fecha:** 2026-05-16 · **Sprint:** 5 (EPIC #49 · Sub-issue #50)

Este documento captura las decisiones de marca tomadas en la sesión de Brand Brief del Sprint A. Es la base sobre la que se construye `BRAND_DIRECTION.md` (dirección visual elegida) y eventualmente `BRAND.md` (manual de marca consolidado).

Las respuestas son del usuario; los comentarios y síntesis son del proceso de facilitación.

---

## Bloque 1 — Audiencia

### 1. Audiencia primaria
**Editores/directores de revistas académicas + Instituciones** (universidades, asociaciones, sociedades científicas que indexan sus revistas).

Ambos perfiles deciden compra. Los editores/directores son el caso individual; las instituciones contratan en bulk para sus revistas asociadas.

### 2. Geografía
**Global.** No restringimos a LATAM. La marca debe funcionar en castellano, inglés y portugués, y ser interpretable por lectores académicos de cualquier región.

### 3. Sofisticación digital
**(a) Ya conocen DOAJ, DOI, ORCID, peer-review, OJS.** Hablan nuestro idioma técnico. No hay que explicar conceptos básicos del rubro; podemos usar vocabulario operativo sin temor.

### 4. Audiencia secundaria
**Revisores externos y bibliotecarios.**
- Los revisores externos interactúan con la plataforma cuando evalúan revistas (flujo de admin tasks).
- Los bibliotecarios consultan el directorio público para decidir suscripciones / curaduría / referencias.

---

## Bloque 2 — Posicionamiento

### 5. Competencia directa
**DOAJ, SciELO, Latindex.** Confirmado por el usuario como las 3 referencias principales contra las que el mercado nos va a comparar.

(No descartamos que aparezcan en mensajería puntual Scimago, Redalyc, Web of Science, Scopus, ERIH+, pero no son la comparación primaria.)

### 6. Diferencial frente a esa competencia
1. **Estándares más actualizados** — criterios técnicos y de open access modernos, no la lógica de 10 años atrás.
2. **Sello visible y comercial** — las otras plataformas indexan; nosotros además emitimos un sello que la revista puede pegar en su sitio, transmitiendo autoridad a sus lectores externos.

### 7. Promesa central
**Versión completa (manifiesto):**
> "Editorial Standards es el lugar donde una revista acredita el cumplimiento de buenas prácticas editoriales mediante evaluación técnica independiente, y se distingue en el mercado académico con un sello reconocible."

**Versión hero (titular):**
> "Acreditación editorial técnica con sello reconocible."

**Versión subtítulo (under-hero):**
> "Evaluamos tu revista contra los estándares editoriales vigentes. Si los cumples, obtenés un sello que el mercado académico reconoce."

### 8. Cara visible del producto
**El sello es la cara visible, fuertemente acompañado de la evaluación.**

Decisión clave: en la jerarquía de comunicación, **el sello viene primero** (es lo tangible, lo coleccionable, lo embebible en sitios de revistas). La evaluación es el sustento técnico que le da peso al sello — sin ella el sello no tiene valor, pero comercialmente el sello es el activo más visible.

Implicación para diseño: el sello editorial es el componente que más atención merece visualmente. Tiene que poder vivir solo en sitios de terceros y transmitir autoridad sin contexto adicional.

---

## Bloque 3 — Personalidad y tono

### 9. Tres adjetivos que SÍ describen la marca
1. **Técnico**
2. **Transparente**
3. **Moderno**

### 10. Tres adjetivos que NO somos
1. **Inaccesible**
2. **Institucional viejo**
3. **Amateur**

Síntesis de tensión: somos técnicos y rigurosos sin ser distantes ni anticuados. No somos un sello gubernamental polvoriento ni un emprendimiento improvisado.

### 11. Tono editorial
- **Voz:** Usted (formal académico). Aplica a textos del sitio público, emails transaccionales y comunicación con editores.
- **Persona:** Tercera persona institucional ("Editorial Standards evalúa...", "La plataforma certifica...").
- **No usar:** Tú/vos, ni primera persona conversacional ("Te ayudamos a...", "Sabemos que...").

Esto vale para textos comerciales y operativos. En documentación técnica interna o mensajería con el equipo, puede relajarse.

### 12. Referencias de marca/medio
**Stripe / Linear — tech con autoridad visual.**

Implicación: estética minimalista, plana, con espacio negativo generoso, tipografía limpia, paleta acotada, fotografía/ilustración sobria si la hay. Lo opuesto: ornamentación, gradientes vibrantes, tipografía decorativa, lenguaje cool/casual.

---

## Bloque 4 — Atributos visuales

### 13. Color de marca
**Azul autoridad — Oxford blue / Royal.**

Comunica: confianza, conocimiento, gobierno, institucional clásico. Es el color del mundo editorial académico tradicional (Oxford, Cambridge, presses universitarias) traído a un registro contemporáneo.

Implicación inmediata: la dualidad amber (admin Filament) / indigo (sitio público) que detectamos como drift se va a **resolver a favor de un azul oxford profundo** como primary unificado. Amber e indigo actuales quedan como deuda técnica a refactorizar en Sprint C.

### 14. Tipografía
**(a) Mantener todo Inter** — limpio, moderno, neutro, sin serif para títulos editoriales.

Decisión consciente: descartamos el camino "editorial-académico clásico" (Nature, The Conversation) que habría incorporado serif (Fraunces, Source Serif, EB Garamond) en heros y h1. Se prioriza coherencia con la referencia Stripe/Linear elegida.

Inter trabaja en pesos 400, 500, 600, 700 (los ya cargados desde fonts.bunny.net). Para wordmark y heros se puede explorar Inter Display si más adelante hace falta más presencia.

### 15. Sello editorial
**(b) Certificado oficial — rectangular sobrio, tipo gobierno/notarial.**

Implicación de diseño:
- Forma rectangular (no circular tipo medalla, no pill horizontal tipo tech badge).
- Sobriedad: jerarquía tipográfica clara, sin decoración, paleta acotada al primary + neutros.
- Debe leerse como "documento oficial" sin caer en lo polvoriento. La inspiración correcta es un certificado moderno (tipo emisión de Stripe Atlas, o sellos de organismos como Berkeley Open Source Office), no un sello notarial del siglo XIX.
- Versionado: vigente / expirado, idealmente con discriminación visual obvia.

---

## Síntesis — qué marca estamos definiendo

Cruzando las 15 respuestas, la identidad que emerge es:

> **"Tech minimalista con gravedad institucional."**

No es una marca académica nostálgica (descarta serif y feel histórico).
No es una startup canchera (descarta tú/voz cercana y colores vibrantes).
Es el sweet spot que ocupan **Stripe, Linear, Vercel, Sentry** cuando hablan de compliance, seguridad o auditoría: estética plana y moderna, pero registro de lenguaje formal y peso institucional.

Las decisiones clave que sostienen esta síntesis:
- Azul oxford (gravedad) + Inter sans-serif (modernidad) + minimalismo (tech)
- Tono usted/3ª persona (autoridad institucional) + audiencia técnica sofisticada (no hay que explicar nada)
- Sello rectangular oficial (gravedad) + estilo Stripe (modernidad)
- Diferencial = estándares actualizados (no "mejor que SciELO porque más viejos" sino "mejor porque más nuevos")

## Tensiones productivas a tener consciente

Hay tres tensiones a navegar en el resto del sprint:

1. **Moderno vs institucional.** No caer del lado tech-startup, no caer del lado gubernamental-polvoriento. Cada decisión visual concreta tendrá que ubicarse en este eje.
2. **Sello primary vs evaluación-sustento.** Aunque el sello es la cara visible, no podemos descuidar la comunicación de la evaluación detrás. El diseño debe permitir contar ambas historias.
3. **Audiencia técnica global vs marca con calor.** Audiencia sofisticada + tono formal + tres adjetivos que NO incluyen "cercano" o "cálido" → marca distante por diseño. Aceptado, pero hay que vigilar que la distancia no se lea como frialdad o falta de servicio.

---

## Próximos pasos en el Sprint A

1. **Moodboard de referencias** (siguiente en la sesión) — recopilo 15-20 ejemplos visuales que materialicen "tech minimalista con gravedad institucional" en oxford blue + Inter + sello rectangular oficial. Mezcla de referencias del rubro (DOAJ, eLife, JSTOR), tech-compliance (Stripe Atlas, Vercel Security, Linear) y certificados modernos (Stripe verified, Berkeley OS).
2. **Direcciones visuales contrastantes** — armo 2-3 variantes dentro del territorio elegido (ej: más cercano a Stripe / más cercano a JSTOR / un híbrido), vos elegís una.
3. **`BRAND_DIRECTION.md`** — documento con la dirección elegida + referencias linkeadas + decisión preliminar sobre paleta concreta (qué hex de oxford blue, qué color de apoyo, neutros).

Recién después de cerrar `BRAND_DIRECTION.md` se ejecuta Sprint B (Identidad: paleta definitiva en `app.css`, tipografía, refinamiento del sello en `resources/views/badge/seal.blade.php`, `BRAND.md`).
