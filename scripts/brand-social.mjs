// Genera los assets de marca para redes sociales (SVG + PNG) en public/brand/social/.
//
// Requiere un rasterizador que no es dependencia del proyecto — instalarlo aparte:
//   mkdir -p ~/.cache/brand-render && cd ~/.cache/brand-render && npm i @resvg/resvg-js
//   node scripts/brand-social.mjs
//
// Las fuentes Inter (400/500/600) se descargan una vez a ~/.cache/brand-render/fonts:
//   curl -A 'Mozilla/4.0' 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600' -o inter.css
//   for u in $(grep -o 'https://[^)]*\.ttf' inter.css | sort -u); do curl -sS "$u" -o fonts/$(basename $u); done
//
// Paleta y geometría: BRAND.md §3 (Editorial Blue) y public/brand/mark.svg.

import { mkdirSync, writeFileSync, readdirSync, rmSync } from 'node:fs';
import { join } from 'node:path';
import { homedir } from 'node:os';
import { createRequire } from 'node:module';
import { fileURLToPath } from 'node:url';

const CACHE = join(homedir(), '.cache', 'brand-render');
const { Resvg } = createRequire(join(CACHE, 'package.json'))('@resvg/resvg-js');

const FONT_DIR = join(CACHE, 'fonts');
const FONT_FILES = readdirSync(FONT_DIR).map((f) => join(FONT_DIR, f));

const OUT = fileURLToPath(new URL('../public/brand/social/', import.meta.url));

const DEEP = '#172554';   // Editorial Blue Deep
const BLUE = '#1E3A8A';   // Editorial Blue
const EMERALD = '#10B981'; // accent del sello
const SKY = '#93C5FD';
const SLATE = '#CBD5E1';
const MUTED = '#94A3B8';

const FONT = "Inter, system-ui, sans-serif";
const TAGLINE = 'Acreditación editorial técnica con sello reconocible.';
const DOMAIN = 'EDITORIALSTANDARDS.ORG';

/** Mark en su grilla nativa de 100×100, colocada y escalada. */
const mark = (x, y, size, fill) => `<g fill="${fill}" transform="translate(${x} ${y}) scale(${size / 100})">
  <rect x="8" y="8" width="14" height="84"/><rect x="22" y="8" width="56" height="14"/><rect x="78" y="8" width="14" height="32"/>
  <rect x="22" y="43" width="30" height="14"/><rect x="22" y="78" width="56" height="14"/><rect x="78" y="60" width="14" height="32"/>
</g>`;

const text = (x, y, content, { size, weight = 500, fill = '#FFFFFF', anchor = 'start', tracking = 0 }) =>
  `<text x="${x}" y="${y}" font-family="${FONT}" font-size="${size}" font-weight="${weight}" fill="${fill}"` +
  ` text-anchor="${anchor}"${tracking ? ` letter-spacing="${tracking}"` : ''}>${content}</text>`;

const svg = (w, h, body) =>
  `<svg xmlns="http://www.w3.org/2000/svg" width="${w}" height="${h}" viewBox="0 0 ${w} ${h}" role="img" aria-label="Editorial Standards Platform"><title>Editorial Standards Platform</title>${body}</svg>`;

// Los dos lockups reproducen la geometría de public/brand/logo-{horizontal,stacked}-dark.svg
// escalada: 480×100 el horizontal, 320×200 el apilado. No inventar proporciones nuevas —
// la relación mark/wordmark es parte de la marca (BRAND.md §10).

/** Lockup horizontal (480×100 en unidades locales) con su esquina superior izquierda en (x, y). */
const lockupH = (x, y, scale) =>
  `<g transform="translate(${x} ${y}) scale(${scale})">` +
  mark(0, 0, 100, '#FFFFFF') +
  text(120, 58, 'Editorial Standards', { size: 40, weight: 600 }) +
  text(120, 85, 'PLATFORM', { size: 14, fill: SKY, tracking: 4 }) +
  '</g>';

/** Lockup apilado (320×200 en unidades locales) con su esquina superior izquierda en (x, y). */
const lockupV = (x, y, scale) =>
  `<g transform="translate(${x} ${y}) scale(${scale})">` +
  mark(110, 0, 100, '#FFFFFF') +
  text(160, 148, 'Editorial Standards', { size: 32, weight: 600, anchor: 'middle' }) +
  text(160, 180, 'PLATFORM', { size: 14, fill: SKY, anchor: 'middle', tracking: 6 }) +
  '</g>';

/** Centra un lockup de ancho w (unidades locales) en cx. */
const centerX = (cx, w, scale) => cx - (w * scale) / 2;

const assets = {
  // Foto de perfil — todas las redes usan recorte circular, la mark queda dentro del círculo seguro.
  'avatar-dark-1000': svg(1000, 1000, `<rect width="1000" height="1000" fill="${DEEP}"/>${mark(250, 250, 500, '#FFFFFF')}`),
  'avatar-blue-1000': svg(1000, 1000, `<rect width="1000" height="1000" fill="${BLUE}"/>${mark(250, 250, 500, '#FFFFFF')}`),
  'avatar-light-1000': svg(1000, 1000, `<rect width="1000" height="1000" fill="#FFFFFF"/>${mark(250, 250, 500, BLUE)}`),

  // Open Graph / Twitter card — preview de links en FB, LinkedIn, X, WhatsApp, Slack.
  'og-1200x630': svg(1200, 630,
    `<rect width="1200" height="630" fill="${DEEP}"/><rect width="12" height="630" fill="${EMERALD}"/>` +
    lockupH(96, 150, 1.6) +
    text(96, 400, TAGLINE, { size: 30, fill: SLATE }) +
    text(96, 448, 'Evaluamos revistas académicas contra estándares editoriales vigentes.', { size: 21, weight: 400, fill: MUTED }) +
    `<line x1="96" y1="540" x2="1104" y2="540" stroke="#FFFFFF" stroke-opacity="0.15" stroke-width="1"/>` +
    text(96, 580, DOMAIN, { size: 16, fill: MUTED, tracking: 2 })),

  // X / Twitter header. La foto de perfil tapa la esquina inferior izquierda: contenido centrado y alto.
  'x-header-1500x500': svg(1500, 500,
    `<rect width="1500" height="500" fill="${DEEP}"/><rect width="1500" height="8" fill="${EMERALD}"/>` +
    lockupH(centerX(750, 480, 1.5), 150, 1.5) +
    text(750, 375, TAGLINE, { size: 26, fill: SLATE, anchor: 'middle' })),

  // LinkedIn — banner de página de empresa. El logo de la página tapa la izquierda hasta ~x=300.
  'linkedin-banner-1128x191': svg(1128, 191,
    `<rect width="1128" height="191" fill="${DEEP}"/><rect width="8" height="191" fill="${EMERALD}"/>` +
    lockupH(360, 46, 1)),

  // LinkedIn — portada de perfil personal.
  'linkedin-cover-1584x396': svg(1584, 396,
    `<rect width="1584" height="396" fill="${DEEP}"/><rect width="1584" height="8" fill="${EMERALD}"/>` +
    lockupH(centerX(880, 480, 1.5), 110, 1.5) +
    text(880, 330, TAGLINE, { size: 26, fill: SLATE, anchor: 'middle' })),

  // Facebook — portada. En móvil se recorta a los 640 px centrales.
  'facebook-cover-820x312': svg(820, 312,
    `<rect width="820" height="312" fill="${DEEP}"/><rect width="820" height="6" fill="${EMERALD}"/>` +
    lockupH(centerX(410, 480, 1.05), 80, 1.05) +
    text(410, 260, TAGLINE, { size: 19, fill: SLATE, anchor: 'middle' })),

  // Instagram — post cuadrado.
  'instagram-post-1080': svg(1080, 1080,
    `<rect width="1080" height="1080" fill="${DEEP}"/><rect y="1074" width="1080" height="6" fill="${EMERALD}"/>` +
    lockupV(centerX(540, 320, 2.2), 230, 2.2) +
    text(540, 820, TAGLINE, { size: 30, fill: SLATE, anchor: 'middle' }) +
    text(540, 980, DOMAIN, { size: 20, fill: MUTED, anchor: 'middle', tracking: 3 })),

  // Instagram / Facebook — story vertical. Contenido dentro de la zona segura central.
  'instagram-story-1080x1920': svg(1080, 1920,
    `<rect width="1080" height="1920" fill="${DEEP}"/><rect y="1914" width="1080" height="6" fill="${EMERALD}"/>` +
    lockupV(centerX(540, 320, 2.2), 640, 2.2) +
    text(540, 1230, TAGLINE, { size: 30, fill: SLATE, anchor: 'middle' }) +
    text(540, 1560, DOMAIN, { size: 20, fill: MUTED, anchor: 'middle', tracking: 3 })),

  // YouTube — art del canal. Todo dentro del área segura de 1546×423 centrada en el lienzo.
  'youtube-banner-2560x1440': svg(2560, 1440,
    `<rect width="2560" height="1440" fill="${DEEP}"/>` +
    lockupH(centerX(1280, 480, 2), 580, 2) +
    text(1280, 870, TAGLINE, { size: 32, fill: SLATE, anchor: 'middle' })),
};

mkdirSync(OUT, { recursive: true });

const rendered = {};

for (const [name, source] of Object.entries(assets)) {
  writeFileSync(join(OUT, `${name}.svg`), source + '\n');

  const png = new Resvg(source, {
    font: { fontFiles: FONT_FILES, loadSystemFonts: false, defaultFontFamily: 'Inter' },
  }).render().asPng();

  rendered[name] = png;
  writeFileSync(join(OUT, `${name}.png`), png);
  console.log(`${name}  ${(png.length / 1024).toFixed(0)} KB`);
}

// El og:image del sitio (components/layouts/app.blade.php) apunta a este archivo.
// El legacy quedó como root desde un contenedor: se borra antes de reescribirlo.
const OG = fileURLToPath(new URL('../public/images/og-default.png', import.meta.url));
rmSync(OG, { force: true });
writeFileSync(OG, rendered['og-1200x630']);
console.log('public/images/og-default.png  actualizado');
