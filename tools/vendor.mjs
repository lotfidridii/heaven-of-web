// Copies the frontend libraries the site needs from node_modules into public_html/assets/vendor
// and the self-hosted fonts into public_html/assets/fonts. Run with `npm run vendor`.
import { copyFileSync, mkdirSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const nm = (p) => join(root, 'node_modules', p);
const out = (p) => join(root, 'public_html', 'assets', p);

const files = {
  'three/build/three.module.min.js': 'vendor/three.module.min.js',
  'gsap/dist/gsap.min.js': 'vendor/gsap.min.js',
  'gsap/dist/ScrollTrigger.min.js': 'vendor/ScrollTrigger.min.js',
  'lenis/dist/lenis.min.js': 'vendor/lenis.min.js',
  'chart.js/dist/chart.umd.min.js': 'vendor/chart.umd.min.js',
};
for (const w of [400, 500, 600, 700, 800]) {
  for (const sub of ['latin', 'latin-ext']) {
    if (sub === 'latin-ext' && w === 500) continue;
    files[`@fontsource/poppins/files/poppins-${sub}-${w}-normal.woff2`] = `fonts/poppins-${sub}-${w}.woff2`;
  }
}
for (const w of [400, 600, 700, 800]) {
  files[`@fontsource/cairo/files/cairo-arabic-${w}-normal.woff2`] = `fonts/cairo-arabic-${w}.woff2`;
}

for (const [src, dest] of Object.entries(files)) {
  mkdirSync(dirname(out(dest)), { recursive: true });
  copyFileSync(nm(src), out(dest));
  console.log('copied', dest);
}
