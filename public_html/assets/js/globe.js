/* Hero globe — the globe from the Heaven of Web logo, in 3D.
   Wireframe meridians/parallels + a dotted surface, arcs leaving Sousse toward other cities,
   drag to spin, colours follow the light/dark theme. Falls back to the inline SVG without WebGL. */
import * as THREE from '../vendor/three.module.min.js';

const host = document.querySelector('[data-globe]');
const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;

function webglAvailable() {
  try {
    const c = document.createElement('canvas');
    return !!(window.WebGLRenderingContext && (c.getContext('webgl2') || c.getContext('webgl')));
  } catch { return false; }
}

if (host && webglAvailable()) init();

function cssColor(name) {
  const raw = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  const m = raw.match(/rgba?\(([^)]+)\)/);
  if (m) {
    const [r, g, b, a = '1'] = m[1].split(',').map((s) => s.trim());
    return { color: new THREE.Color(`rgb(${r}, ${g}, ${b})`), alpha: parseFloat(a) };
  }
  return { color: new THREE.Color(raw || '#dbdbdb'), alpha: 1 };
}

function latLon(lat, lon, r) {
  const phi = (90 - lat) * Math.PI / 180;
  const theta = (lon + 180) * Math.PI / 180;
  return new THREE.Vector3(-r * Math.sin(phi) * Math.cos(theta), r * Math.cos(phi), r * Math.sin(phi) * Math.sin(theta));
}

function dotTexture() {
  const c = document.createElement('canvas');
  c.width = c.height = 64;
  const g = c.getContext('2d');
  const grad = g.createRadialGradient(32, 32, 0, 32, 32, 32);
  grad.addColorStop(0, 'rgba(255,255,255,1)');
  grad.addColorStop(0.45, 'rgba(255,255,255,1)');
  grad.addColorStop(1, 'rgba(255,255,255,0)');
  g.fillStyle = grad;
  g.fillRect(0, 0, 64, 64);
  const t = new THREE.CanvasTexture(c);
  t.colorSpace = THREE.SRGBColorSpace;
  return t;
}

function init() {
  const isSmall = innerWidth < 900;
  const R = 1;
  const CORAL = new THREE.Color('#FF4848');

  const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, powerPreference: 'low-power' });
  renderer.setPixelRatio(Math.min(devicePixelRatio, 2));
  renderer.setClearColor(0x000000, 0);
  host.appendChild(renderer.domElement);

  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(32, 1, 0.1, 50);
  camera.position.set(0, 0, 4.9);

  const tilt = new THREE.Group();
  const spin = new THREE.Group();
  tilt.add(spin);
  scene.add(tilt);
  tilt.rotation.x = 0.42;
  spin.rotation.y = -Math.PI / 2 - 0.12; // the Mediterranean (Sousse) faces the viewer

  // Depth-only sphere: hides the far side of lines and dots without painting anything.
  const occluder = new THREE.Mesh(
    new THREE.SphereGeometry(R * 0.985, 48, 48),
    new THREE.MeshBasicMaterial({ colorWrite: false })
  );
  spin.add(occluder);

  // Meridians and parallels, like the logo.
  const gridMat = new THREE.LineBasicMaterial({ transparent: true });
  const gridGeo = new THREE.BufferGeometry();
  const gridPts = [];
  const SEG = 96;
  for (let lat = -60; lat <= 60; lat += 30) {
    for (let i = 0; i < SEG; i++) {
      gridPts.push(latLon(lat, (i / SEG) * 360, R), latLon(lat, ((i + 1) / SEG) * 360, R));
    }
  }
  for (let lon = 0; lon < 180; lon += 30) {
    for (let i = 0; i < SEG; i++) {
      const a = (i / SEG) * 360, b = ((i + 1) / SEG) * 360;
      gridPts.push(latLon(90 - a, lon, R), latLon(90 - b, lon, R));
    }
  }
  gridGeo.setFromPoints(gridPts);
  const grid = new THREE.LineSegments(gridGeo, gridMat);
  spin.add(grid);

  // Dotted surface (Fibonacci sphere).
  const N = isSmall ? 900 : 1800;
  const dotPos = new Float32Array(N * 3);
  const golden = Math.PI * (3 - Math.sqrt(5));
  for (let i = 0; i < N; i++) {
    const y = 1 - (i / (N - 1)) * 2;
    const r = Math.sqrt(1 - y * y);
    const th = golden * i;
    dotPos.set([Math.cos(th) * r * R * 1.002, y * R * 1.002, Math.sin(th) * r * R * 1.002], i * 3);
  }
  const dotGeo = new THREE.BufferGeometry();
  dotGeo.setAttribute('position', new THREE.BufferAttribute(dotPos, 3));
  const dotMat = new THREE.PointsMaterial({ size: isSmall ? 0.022 : 0.018, map: dotTexture(), transparent: true, depthWrite: false });
  spin.add(new THREE.Points(dotGeo, dotMat));

  // Cities: Sousse is home; arcs fly to the places our clients and visitors come from.
  const home = { lat: 35.83, lon: 10.64 };
  const cities = [
    { lat: 36.81, lon: 10.18 },  // Tunis
    { lat: 34.74, lon: 10.76 },  // Sfax
    { lat: 47.47, lon: -0.55 },  // Angers (our French clients)
    { lat: 48.86, lon: 2.35 },   // Paris
    { lat: 45.76, lon: 4.84 },   // Lyon
    { lat: 43.30, lon: 5.37 },   // Marseille
    { lat: 50.85, lon: 4.35 },   // Brussels
    { lat: 51.51, lon: -0.13 },  // London
    { lat: 36.75, lon: 3.06 },   // Algiers
    { lat: 33.57, lon: -7.59 },  // Casablanca
    { lat: 25.20, lon: 55.27 },  // Dubai
    { lat: 45.50, lon: -73.57 }, // Montréal
  ];
  const homeV = latLon(home.lat, home.lon, R);
  const nodeMat = new THREE.PointsMaterial({ size: 0.055, map: dotTexture(), color: CORAL, transparent: true, depthWrite: false });
  const nodeGeo = new THREE.BufferGeometry().setFromPoints([homeV, ...cities.map((c) => latLon(c.lat, c.lon, R * 1.004))]);
  spin.add(new THREE.Points(nodeGeo, nodeMat));

  const ARC_PTS = 80;
  const arcs = cities.map((c, i) => {
    const end = latLon(c.lat, c.lon, R);
    const dist = homeV.distanceTo(end);
    const mid = homeV.clone().add(end).multiplyScalar(0.5).normalize().multiplyScalar(R * (1 + Math.min(0.55, dist * 0.42)));
    const curve = new THREE.QuadraticBezierCurve3(homeV, mid, end);
    const pts = curve.getPoints(ARC_PTS);
    const base = new THREE.Line(
      new THREE.BufferGeometry().setFromPoints(pts),
      new THREE.LineBasicMaterial({ color: CORAL, transparent: true, opacity: 0.22, depthWrite: false })
    );
    const comet = new THREE.Line(
      new THREE.BufferGeometry().setFromPoints(pts),
      new THREE.LineBasicMaterial({ color: CORAL, transparent: true, opacity: 0.95, depthWrite: false, blending: THREE.AdditiveBlending })
    );
    spin.add(base, comet);
    return { comet, phase: i * 0.37, speed: 0.35 + (i % 4) * 0.06 };
  });

  // Pulsing ring around Sousse.
  const ring = new THREE.Mesh(
    new THREE.RingGeometry(0.03, 0.04, 40),
    new THREE.MeshBasicMaterial({ color: CORAL, transparent: true, side: THREE.DoubleSide, depthWrite: false })
  );
  ring.position.copy(homeV.clone().multiplyScalar(1.006));
  ring.lookAt(homeV.clone().multiplyScalar(2));
  spin.add(ring);

  // Soft coral halo (fresnel glow on a slightly larger back-facing sphere).
  const halo = new THREE.Mesh(
    new THREE.SphereGeometry(R * 1.14, 64, 64),
    new THREE.ShaderMaterial({
      uniforms: { uColor: { value: CORAL }, uStrength: { value: 0.55 } },
      vertexShader: 'varying vec3 vN; void main(){ vN = normalize(normalMatrix * normal); gl_Position = projectionMatrix * modelViewMatrix * vec4(position,1.0); }',
      fragmentShader: 'uniform vec3 uColor; uniform float uStrength; varying vec3 vN; void main(){ float i = pow(clamp(-dot(vN, vec3(0.0,0.0,1.0)), 0.0, 1.0), 2.2); gl_FragColor = vec4(uColor, i * uStrength); }',
      side: THREE.BackSide, transparent: true, depthWrite: false, blending: THREE.AdditiveBlending,
    })
  );
  tilt.add(halo);

  function applyTheme() {
    // The globe always sits on the navy hero overlay: light lines, coral accents.
    gridMat.color.set('#DBDBDB');
    gridMat.opacity = 0.32;
    dotMat.color.set('#FFFFFF');
    dotMat.opacity = 0.55;
    halo.material.uniforms.uStrength.value = 0.45;
    requestRender();
  }

  // Size to the container.
  function resize() {
    const { width, height } = host.getBoundingClientRect();
    if (!width || !height) return;
    renderer.setSize(width, height, false);
    camera.aspect = width / height;
    camera.updateProjectionMatrix();
    requestRender();
  }
  new ResizeObserver(resize).observe(host);

  // Drag to spin with inertia.
  let dragging = false, lastX = 0, lastY = 0, velY = 0, velX = 0;
  const baseTilt = tilt.rotation.x;
  host.addEventListener('pointerdown', (e) => {
    dragging = true; lastX = e.clientX; lastY = e.clientY;
    host.setPointerCapture(e.pointerId);
  });
  host.addEventListener('pointermove', (e) => {
    if (!dragging) return;
    const dx = e.clientX - lastX, dy = e.clientY - lastY;
    lastX = e.clientX; lastY = e.clientY;
    velY = dx * 0.006;
    velX = dy * 0.004;
    spin.rotation.y += velY;
    tilt.rotation.x = THREE.MathUtils.clamp(tilt.rotation.x + velX, baseTilt - 0.6, baseTilt + 0.6);
    requestRender();
  });
  const endDrag = () => { dragging = false; };
  host.addEventListener('pointerup', endDrag);
  host.addEventListener('pointercancel', endDrag);

  // Only animate while the hero is visible and the tab is active.
  let visible = true;
  new IntersectionObserver(([entry]) => { visible = entry.isIntersecting; if (visible) loop(); }, { threshold: 0 }).observe(host);
  document.addEventListener('visibilitychange', () => { if (!document.hidden) loop(); });
  document.addEventListener('themechange', applyTheme);

  const clock = new THREE.Clock();
  let running = false;
  let needsRender = true;
  function requestRender() {
    needsRender = true;
    if (reduceMotion) renderer.render(scene, camera);
  }

  const hero = host.closest('.hero');
  function frame() {
    const t = clock.getElapsedTime();
    const dt = Math.min(clock.getDelta(), 0.05);

    if (!dragging) {
      spin.rotation.y += velY + (reduceMotion ? 0 : 0.0016);
      velY *= 0.94;
      tilt.rotation.x += (baseTilt - tilt.rotation.x) * 0.02;
    }

    // Scroll: the globe drifts up, shrinks a little and keeps turning as the hero leaves the screen.
    const p = hero ? THREE.MathUtils.clamp(scrollY / hero.offsetHeight, 0, 1) : 0;
    tilt.position.y = p * 0.6;
    tilt.scale.setScalar(1 - p * 0.18);

    if (!reduceMotion) {
      arcs.forEach((a) => {
        const k = ((t * a.speed + a.phase) % 1.6) / 1.6; // 0..1 loop with a short pause
        const len = 18;
        const start = Math.floor(k * (ARC_PTS + len)) - len;
        a.comet.geometry.setDrawRange(Math.max(0, start), Math.max(0, Math.min(len, start + len, ARC_PTS + 1 - Math.max(0, start))));
      });
      const s = (t * 0.6) % 1;
      ring.scale.setScalar(1 + s * 2.4);
      ring.material.opacity = 1 - s;
    }
    void dt;
    renderer.render(scene, camera);
    needsRender = false;
  }

  function loop() {
    if (running) return;
    running = true;
    const tick = () => {
      if (!visible || document.hidden || (reduceMotion && !dragging && !needsRender)) { running = false; return; }
      frame();
      requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  }

  if (reduceMotion) arcs.forEach((a) => a.comet.geometry.setDrawRange(0, 0));
  applyTheme();
  resize();
  frame();
  host.classList.add('globe-ready');
  loop();
  if (reduceMotion) host.addEventListener('pointermove', () => { if (dragging) loop(); });
}
