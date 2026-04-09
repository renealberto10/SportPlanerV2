<template>
  <canvas ref="canvasEl" class="hero-canvas" />
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'
import * as THREE from 'three'

const canvasEl = ref<HTMLCanvasElement | null>(null)
let animId = 0
let renderer: THREE.WebGLRenderer
let scene: THREE.Scene
let camera: THREE.PerspectiveCamera
let particles: THREE.Points
let lines: THREE.LineSegments
let mouse = { x: 0, y: 0 }

const PARTICLE_COUNT = 120
const CONNECTION_DIST = 2.8
const SPREAD = 12

function init() {
  const canvas = canvasEl.value!
  const w = canvas.clientWidth
  const h = canvas.clientHeight

  // Renderer
  renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true })
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
  renderer.setSize(w, h, false)
  renderer.setClearColor(0x000000, 0)

  // Scene & camera
  scene = new THREE.Scene()
  camera = new THREE.PerspectiveCamera(55, w / h, 0.1, 100)
  camera.position.set(0, 0, 16)

  // ── Particles ──────────────────────────────────────────
  const positions = new Float32Array(PARTICLE_COUNT * 3)
  const velocities: THREE.Vector3[] = []
  const sizes = new Float32Array(PARTICLE_COUNT)

  for (let i = 0; i < PARTICLE_COUNT; i++) {
    const x = (Math.random() - 0.5) * SPREAD * 2
    const y = (Math.random() - 0.5) * SPREAD
    const z = (Math.random() - 0.5) * SPREAD * 0.5
    positions[i * 3]     = x
    positions[i * 3 + 1] = y
    positions[i * 3 + 2] = z
    velocities.push(new THREE.Vector3(
      (Math.random() - 0.5) * 0.006,
      (Math.random() - 0.5) * 0.006,
      (Math.random() - 0.5) * 0.003,
    ))
    sizes[i] = Math.random() * 2.5 + 1.5
  }

  const geo = new THREE.BufferGeometry()
  geo.setAttribute('position', new THREE.BufferAttribute(positions, 3))
  geo.setAttribute('size', new THREE.BufferAttribute(sizes, 1))

  // Circle texture
  const tex = buildCircleTexture()

  const mat = new THREE.PointsMaterial({
    size: 0.14,
    map: tex,
    transparent: true,
    depthWrite: false,
    blending: THREE.AdditiveBlending,
    vertexColors: false,
    color: new THREE.Color('#60a5fa'),
    opacity: 0.75,
  })

  particles = new THREE.Points(geo, mat)
  scene.add(particles)

  // ── Lines ──────────────────────────────────────────────
  const lineGeo = new THREE.BufferGeometry()
  const linePositions = new Float32Array(PARTICLE_COUNT * PARTICLE_COUNT * 6)
  lineGeo.setAttribute('position', new THREE.BufferAttribute(linePositions, 3))

  const lineMat = new THREE.LineBasicMaterial({
    color: new THREE.Color('#3b82f6'),
    transparent: true,
    opacity: 0.18,
    blending: THREE.AdditiveBlending,
    depthWrite: false,
  })
  lines = new THREE.LineSegments(lineGeo, lineMat)
  scene.add(lines)

  // ── Floating orbs ──────────────────────────────────────
  const orbData = [
    { r: 0.55, color: '#6366f1', x: -5,  y: 2,   z: -2 },
    { r: 0.35, color: '#3b82f6', x:  6,  y: -2,  z: -1 },
    { r: 0.25, color: '#818cf8', x:  3,  y: 3.5, z:  1 },
    { r: 0.18, color: '#60a5fa', x: -7,  y: -3,  z:  0 },
  ]
  orbData.forEach(o => {
    const mesh = new THREE.Mesh(
      new THREE.SphereGeometry(o.r, 32, 32),
      new THREE.MeshBasicMaterial({
        color: new THREE.Color(o.color),
        transparent: true,
        opacity: 0.5,
        blending: THREE.AdditiveBlending,
      }),
    )
    mesh.position.set(o.x, o.y, o.z)
    scene.add(mesh)
  })

  // ── Ring ───────────────────────────────────────────────
  const ring = new THREE.Mesh(
    new THREE.TorusGeometry(3.5, 0.008, 16, 120),
    new THREE.MeshBasicMaterial({
      color: new THREE.Color('#6366f1'),
      transparent: true,
      opacity: 0.25,
      blending: THREE.AdditiveBlending,
    }),
  )
  ring.rotation.x = Math.PI / 2.8
  ring.position.set(0, 0.5, -1)
  scene.add(ring)

  const ring2 = new THREE.Mesh(
    new THREE.TorusGeometry(5, 0.005, 16, 120),
    new THREE.MeshBasicMaterial({
      color: new THREE.Color('#3b82f6'),
      transparent: true,
      opacity: 0.12,
      blending: THREE.AdditiveBlending,
    }),
  )
  ring2.rotation.x = Math.PI / 3
  ring2.rotation.z = 0.4
  scene.add(ring2)

  // ── Resize ─────────────────────────────────────────────
  const ro = new ResizeObserver(() => resize())
  ro.observe(canvas)

  // ── Mouse parallax ─────────────────────────────────────
  window.addEventListener('mousemove', onMouse, { passive: true })

  // ── Animate ────────────────────────────────────────────
  let t = 0
  function animate() {
    animId = requestAnimationFrame(animate)
    t += 0.005

    const pos = particles.geometry.attributes.position.array as Float32Array

    // Move particles
    for (let i = 0; i < PARTICLE_COUNT; i++) {
      pos[i * 3]     += velocities[i].x
      pos[i * 3 + 1] += velocities[i].y
      pos[i * 3 + 2] += velocities[i].z
      // Wrap
      if (pos[i * 3]     >  SPREAD) pos[i * 3]     = -SPREAD
      if (pos[i * 3]     < -SPREAD) pos[i * 3]     =  SPREAD
      if (pos[i * 3 + 1] >  SPREAD * 0.5) pos[i * 3 + 1] = -SPREAD * 0.5
      if (pos[i * 3 + 1] < -SPREAD * 0.5) pos[i * 3 + 1] =  SPREAD * 0.5
    }
    particles.geometry.attributes.position.needsUpdate = true

    // Rebuild lines
    const lp = lines.geometry.attributes.position.array as Float32Array
    let lIdx = 0
    for (let a = 0; a < PARTICLE_COUNT; a++) {
      for (let b = a + 1; b < PARTICLE_COUNT; b++) {
        const dx = pos[a*3] - pos[b*3]
        const dy = pos[a*3+1] - pos[b*3+1]
        const dz = pos[a*3+2] - pos[b*3+2]
        const dist = Math.sqrt(dx*dx + dy*dy + dz*dz)
        if (dist < CONNECTION_DIST) {
          lp[lIdx++] = pos[a*3]; lp[lIdx++] = pos[a*3+1]; lp[lIdx++] = pos[a*3+2]
          lp[lIdx++] = pos[b*3]; lp[lIdx++] = pos[b*3+1]; lp[lIdx++] = pos[b*3+2]
        }
      }
    }
    // zero out unused
    for (let i = lIdx; i < lp.length; i++) lp[i] = 0
    lines.geometry.setDrawRange(0, lIdx / 3)
    lines.geometry.attributes.position.needsUpdate = true

    // Rotate rings
    ring.rotation.z  = t * 0.15
    ring2.rotation.z = -t * 0.08

    // Camera parallax
    camera.position.x += (mouse.x * 1.5 - camera.position.x) * 0.04
    camera.position.y += (mouse.y * 0.8 - camera.position.y) * 0.04
    camera.lookAt(0, 0, 0)

    renderer.render(scene, camera)
  }
  animate()

  function resize() {
    const cw = canvas.clientWidth
    const ch = canvas.clientHeight
    camera.aspect = cw / ch
    camera.updateProjectionMatrix()
    renderer.setSize(cw, ch, false)
  }

  return () => { ro.disconnect(); window.removeEventListener('mousemove', onMouse) }
}

function onMouse(e: MouseEvent) {
  mouse.x = (e.clientX / window.innerWidth  - 0.5) * 2
  mouse.y = -(e.clientY / window.innerHeight - 0.5) * 2
}

function buildCircleTexture(): THREE.Texture {
  const size = 64
  const c = document.createElement('canvas')
  c.width = c.height = size
  const ctx = c.getContext('2d')!
  const g = ctx.createRadialGradient(size/2, size/2, 0, size/2, size/2, size/2)
  g.addColorStop(0, 'rgba(255,255,255,1)')
  g.addColorStop(1, 'rgba(255,255,255,0)')
  ctx.fillStyle = g
  ctx.fillRect(0, 0, size, size)
  return new THREE.CanvasTexture(c)
}

let cleanup: (() => void) | null = null

onMounted(() => { cleanup = init() })
onBeforeUnmount(() => {
  cancelAnimationFrame(animId)
  renderer?.dispose()
  cleanup?.()
})
</script>

<style scoped>
.hero-canvas {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
}
</style>
