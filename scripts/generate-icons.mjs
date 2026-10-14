/**
 * Coding First — brand icon generator (logo variants A "CF dalam hanko" and B "C1 caret").
 *
 * HOW TO RUN (no npm script needed; run from the project root):
 *
 *     node scripts/generate-icons.mjs
 *
 * Uses the devDependencies `sharp` and `png-to-ico` that are already in package.json
 * (run `npm install` once if node_modules is missing). Output is committed to git.
 *
 * Writes for BOTH variants under public/brand/{a,b}/:
 *   mark.svg, logo.svg, logo-dark.svg, favicon.svg, monochrome.svg,
 *   favicon-16.png, favicon-32.png, favicon.ico (16/32/48),
 *   apple-touch-icon.png (180, opaque, full-bleed), icon-192.png, icon-512.png,
 *   maskable-192.png, maskable-512.png (full-bleed tile, glyph at 90% inside the 80% safe zone),
 *   og-image.png (1200×630 social preview)
 * plus the default (variant A) public/favicon.ico and public/favicon.svg.
 *
 * After regenerating, bump CACHE_VERSION in public/sw.js so installed PWAs refresh their icons.
 * Geometry comes from the design spec (64-unit grid); both marks were checked at 16/24/32/48px.
 */
import { mkdir, writeFile } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import sharp from 'sharp';
import pngToIco from 'png-to-ico';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..');
const PUBLIC = join(ROOT, 'public');

/** Brand constants per variant (never theme- or dark-mode dependent). */
const VARIANTS = {
    a: {
        tile: '#AE4027',
        glyph: (fg = '#FFFFFF') =>
            `<path d="M33.39 23.19A11.5 11.5 0 1 0 33.39 40.81" fill="none" stroke="${fg}" stroke-width="8"/>` +
            `<path d="M40 18h13v6.5h-6v4.5h4.5v6.5h-4.5V46h-7z" fill="${fg}"/>`,
        canvas: '#FAF8F4',
        ink: '#1C1B19',
        inkDark: '#EEEAE4',
        word: (fill) =>
            `<text x="43" y="27" font-family="Newsreader, Georgia, 'Times New Roman', serif" font-size="20" font-weight="500" letter-spacing="-0.3" fill="${fill}">Coding <tspan font-style="italic">First</tspan></text>`,
        width: 176,
    },
    b: {
        tile: '#09090B',
        glyph: (fg = '#FAFAFA', caret = '#EE4D1C') =>
            `<path d="M38.4 21.6A14 14 0 1 0 38.4 42.4" fill="none" stroke="${fg}" stroke-width="8" stroke-linecap="round"/>` +
            `<rect x="46" y="20" width="7" height="24" rx="2" fill="${caret}"/>`,
        canvas: '#FAFAFA',
        ink: '#09090B',
        inkDark: '#FAFAFA',
        word: (fill) =>
            `<text x="42" y="26.5" font-family="Geist, Inter, 'Segoe UI', Arial, sans-serif" font-size="18" font-weight="600" letter-spacing="-0.45" fill="${fill}">Coding First</text>`,
        width: 168,
    },
};

const svg = (viewBox, body, extra = '') =>
    `<svg xmlns="http://www.w3.org/2000/svg" viewBox="${viewBox}"${extra}>${body}</svg>\n`;

/** Rounded tile mark (64×64). keyline adds the dark-UI edge for variant B. */
function mark(v, { radius = 14, keyline = false, scale = 1 } = {}) {
    const def = VARIANTS[v];
    const glyph = scale === 1 ? def.glyph() : `<g transform="translate(${32 - 32 * scale} ${32 - 32 * scale}) scale(${scale})">${def.glyph()}</g>`;
    const edge = keyline ? '<rect x="1" y="1" width="62" height="62" rx="13" fill="none" stroke="#3F3F46" stroke-width="2"/>' : '';

    return svg('0 0 64 64', `<rect width="64" height="64"${radius ? ` rx="${radius}"` : ''} fill="${def.tile}"/>${edge}${glyph}`);
}

/** Favicon: B swaps to the inverse tile when the browser UI is dark. */
function favicon(v) {
    if (v === 'a') {
        return mark('a').replace('<svg ', '<svg role="img" aria-label="Coding First" ');
    }

    const style = '<style>@media (prefers-color-scheme: dark){rect:first-of-type{fill:#FAFAFA}path{stroke:#09090B}rect:last-of-type{fill:#D03A03}}</style>';

    return mark('b').replace('<svg ', '<svg role="img" aria-label="Coding First" ').replace('viewBox="0 0 64 64">', `viewBox="0 0 64 64">${style}`);
}

/** Horizontal lockup with live text (fonts fall back to system serif/sans in <img>). */
function logo(v, dark = false) {
    const def = VARIANTS[v];
    const tile = mark(v, { keyline: dark && v === 'b' }).replace(/<\/?svg[^>]*>\n?/g, '');

    return svg(`0 0 ${def.width} 40`, `<g transform="translate(0 4) scale(.5)">${tile}</g>${def.word(dark ? def.inkDark : def.ink)}`,
        ' role="img" aria-label="Coding First"');
}

/** Glyph only, single colour — for manifest purpose "monochrome", watermarks and print. */
function monochrome(v) {
    const glyph = v === 'a' ? VARIANTS.a.glyph('#000000') : VARIANTS.b.glyph('#000000', '#000000');

    return svg('0 0 64 64', glyph);
}

/** 1200×630 social preview image. */
function ogImage(v) {
    const def = VARIANTS[v];
    const dark = v === 'b';
    const tile = mark(v, { keyline: dark }).replace(/<\/?svg[^>]*>\n?/g, '');
    const bg =dark ? '#09090B' : def.canvas;
    const fg = dark ? '#FAFAFA' : def.ink;
    const sub = dark ? '#A1A1AA' : '#4A4642';
    const grid = dark
        ? Array.from({ length: 38 }, (_, i) => `<path d="M${i * 32} 0V630" stroke="#27272A" stroke-width="1"/>`).join('') +
          Array.from({ length: 20 }, (_, i) => `<path d="M0 ${i * 32}H1200" stroke="#27272A" stroke-width="1"/>`).join('')
        : '<path d="M96 520H1104" stroke="#D6CFC4" stroke-width="1"/>';
    const family = v === 'a' ? "Newsreader, Georgia, 'Times New Roman', serif" : "Geist, Inter, 'Segoe UI', Arial, sans-serif";
    const headline = v === 'a'
        ? ['Belajar coding dengan', 'standar industri Jepang.']
        : ['Belajar coding dengan', 'standar engineer Jepang.'];
    const word = v === 'a' ? 'Coding <tspan font-style="italic">First</tspan>' : 'Coding First';

    return svg('0 0 1200 630',
        `<rect width="1200" height="630" fill="${bg}"/>${grid}` +
        `<g transform="translate(96 120) scale(1.5)">${tile}</g>` +
        `<text x="216" y="190" font-family="${family}" font-size="56" font-weight="${v === 'a' ? 500 : 600}" fill="${fg}">${word}</text>` +
        `<text x="96" y="350" font-family="${family}" font-size="60" font-weight="500" fill="${fg}"><tspan x="96">${headline[0]}</tspan><tspan x="96" dy="76">${headline[1]}</tspan></text>` +
        `<text x="96" y="580" font-family="Arial, sans-serif" font-size="24" fill="${sub}" letter-spacing="2">CODINGFIRST.ID</text>`);
}

const png = (source, size, background) => {
    let img = sharp(Buffer.from(source), { density: Math.max(72, Math.ceil((72 * size) / 64) * 2) }).resize(size, size);
    if (background) {
        img = img.flatten({ background });
    }
    return img.png({ compressionLevel: 9 }).toBuffer();
};

async function write(path, data) {
    await mkdir(dirname(path), { recursive: true });
    await writeFile(path, data);
    console.log('  wrote', path.replace(ROOT, '').replaceAll('\\', '/'));
}

for (const v of Object.keys(VARIANTS)) {
    const dir = join(PUBLIC, 'brand', v);
    const base = mark(v);
    const fullBleed = mark(v, { radius: 0 });
    const maskable = mark(v, { radius: 0, scale: 0.9 });

    console.log(`Variant ${v.toUpperCase()}`);
    await write(join(dir, 'mark.svg'), base);
    await write(join(dir, 'favicon.svg'), favicon(v));
    await write(join(dir, 'logo.svg'), logo(v));
    await write(join(dir, 'logo-dark.svg'), logo(v, true));
    await write(join(dir, 'monochrome.svg'), monochrome(v));

    const [p16, p32, p48] = await Promise.all([png(base, 16), png(base, 32), png(base, 48)]);
    await write(join(dir, 'favicon-16.png'), p16);
    await write(join(dir, 'favicon-32.png'), p32);
    await write(join(dir, 'favicon.ico'), await pngToIco([p16, p32, p48]));
    await write(join(dir, 'apple-touch-icon.png'), await png(fullBleed, 180, VARIANTS[v].tile));
    await write(join(dir, 'icon-192.png'), await png(base, 192));
    await write(join(dir, 'icon-512.png'), await png(base, 512));
    await write(join(dir, 'maskable-192.png'), await png(maskable, 192, VARIANTS[v].tile));
    await write(join(dir, 'maskable-512.png'), await png(maskable, 512, VARIANTS[v].tile));
    await write(join(dir, 'og-image.png'),
        await sharp(Buffer.from(ogImage(v)), { density: 72 }).resize(1200, 630).png({ compressionLevel: 9 }).toBuffer());

    if (v === 'a') {
        await write(join(PUBLIC, 'favicon.svg'), favicon('a'));
        await write(join(PUBLIC, 'favicon.ico'), await pngToIco([p16, p32, p48]));
    }
}

console.log('Done. Remember to bump CACHE_VERSION in public/sw.js.');
