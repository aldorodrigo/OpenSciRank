# Assets de marca para redes sociales

Generados con [`scripts/brand-social.mjs`](../../../scripts/brand-social.mjs) a partir de la mark y los
lockups de [`public/brand/`](../). Cada asset existe en `.svg` (fuente editable) y `.png` (lo que piden
las plataformas — ninguna acepta SVG en subida).

Paleta y proporciones: [`BRAND.md`](../../../BRAND.md). Fondo Editorial Blue Deep `#172554`, mark blanca,
acento emerald `#10B981`, tagline en `#CBD5E1`. Sin gradientes.

| Archivo | Tamaño | Dónde va |
|---|---|---|
| `avatar-dark-1000` | 1000×1000 | Foto de perfil — X, LinkedIn, Instagram, Facebook, WhatsApp Business. Recorte circular seguro. |
| `avatar-blue-1000` | 1000×1000 | Alternativa en Editorial Blue `#1E3A8A` cuando el feed es oscuro. |
| `avatar-light-1000` | 1000×1000 | Mark azul sobre blanco — plataformas o docs con fondo oscuro. |
| `og-1200x630` | 1200×630 | Preview de links (Open Graph / Twitter card). Copia servida en `public/images/og-default.png`. |
| `x-header-1500x500` | 1500×500 | Header de X. La foto de perfil tapa la esquina inferior izquierda: esa zona queda vacía a propósito. |
| `linkedin-banner-1128x191` | 1128×191 | Banner de página de empresa. El logo de la página tapa hasta ~x=300. |
| `linkedin-cover-1584x396` | 1584×396 | Portada de perfil personal. |
| `facebook-cover-820x312` | 820×312 | Portada de página. En móvil se recorta a los 640 px centrales — el lockup entra completo. |
| `instagram-post-1080` | 1080×1080 | Post cuadrado / primer post de presentación. |
| `instagram-story-1080x1920` | 1080×1920 | Story. Contenido dentro de la zona segura central (250 px libres arriba y abajo). |
| `youtube-banner-2560x1440` | 2560×1440 | Art del canal. Todo el contenido cae dentro del área segura de 1546×423. |

## Regenerar

```bash
mkdir -p ~/.cache/brand-render && cd ~/.cache/brand-render && npm i @resvg/resvg-js
node scripts/brand-social.mjs
```

Las fuentes Inter (400/500/600) van en `~/.cache/brand-render/fonts`; el script las usa para rasterizar
sin depender de las fuentes del sistema. Instrucciones de descarga en la cabecera del script.
