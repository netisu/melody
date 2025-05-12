<template>
  <div ref="container" style="width: 100%; height: inherit;"></div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, onUnmounted } from 'vue';
import * as THREE from 'three';
import { OBJLoader } from 'three/examples/jsm/loaders/OBJLoader.js';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';
import { debounce } from 'lodash'; // Import debounce

const container = ref<HTMLElement | null>(null);
const scene = ref<THREE.Scene | null>(null);
const camera = ref<THREE.PerspectiveCamera | null>(null);
const renderer = ref<THREE.WebGLRenderer | null>(null);
const controls = ref<OrbitControls | null>(null);
const model = ref<THREE.Group | null>(null);

interface Props {
  hash: string;
}

const props = defineProps<Props>();

onMounted(() => {
  initScene();
  loadItem(props.hash);
  animate();
});

const debouncedLoadItem = debounce(async (hash: string) => {
  if (scene.value && model.value) {
    scene.value.remove(model.value);
    model.value.traverse((object: THREE.Object3D) => {
      if ((object as THREE.Mesh).isMesh) {
        const mesh = object as THREE.Mesh;
        mesh.geometry.dispose();
        if (mesh.material) {
          if (Array.isArray(mesh.material)) {
            mesh.material.forEach((m) => m.dispose());
          } else {
            (mesh.material as THREE.Material).dispose();
          }
        }
      }
    });
    model.value = null;
  }
  await loadItem(hash);
}, 100);

watch(
  () => props.hash,
  (newHash) => {
    debouncedLoadItem(newHash);
  }
);

function initScene(): void {
  if (!container.value) return;

  scene.value = new THREE.Scene();
  scene.value.background = new THREE.Color(0xeeeeee);

  const aspectRatio = container.value.clientWidth / container.value.clientHeight;
  camera.value = new THREE.PerspectiveCamera(75, aspectRatio, 0.1, 1000);
  camera.value.position.z = 5;

  renderer.value = new THREE.WebGLRenderer({ antialias: true });
  renderer.value.setSize(container.value.clientWidth, container.value.clientHeight);
  container.value.appendChild(renderer.value.domElement);

  const ambientLight = new THREE.AmbientLight(0x404040);
  scene.value.add(ambientLight);

  const directionalLight = new THREE.DirectionalLight(0xffffff, 0.5);
  directionalLight.position.set(5, 5, 5);
  scene.value.add(directionalLight);

  controls.value = new OrbitControls(camera.value, renderer.value.domElement);
  controls.value.enableDamping = true;
  controls.value.dampingFactor = 0.05;
  controls.value.rotateSpeed = 0.5;
  controls.value.zoomSpeed = 0.8;
  controls.value.panSpeed = 0.3;
}

async function loadItem(hash: string): Promise<void> {
  if (!hash || !scene.value) return;

  const objUrl = `https://cdn.netisu.com/uploads/${hash}.obj`;
  const textureUrl = `https://cdn.netisu.com/uploads/${hash}.png`;

  const objLoader = new OBJLoader();
  const textureLoader = new THREE.TextureLoader();

  try {
    const object = await objLoader.loadAsync(objUrl);
    model.value = object;

    // Load texture and apply to the model
    textureLoader.load(textureUrl, (texture) => {
      model.value?.traverse((child) => {
        if ((child as THREE.Mesh).isMesh && (child as THREE.Mesh).material) {
          const material = Array.isArray((child as THREE.Mesh).material)
            ? (child as THREE.Mesh).material[0]
            : (child as THREE.Mesh).material;

          if (material instanceof THREE.Material) {
            material.map = texture;
            material.needsUpdate = true;
          }
        }
      });
    });

    // Center the model
    const boundingBox = new THREE.Box3().setFromObject(model.value);
    const center = boundingBox.getCenter(new THREE.Vector3());
    model.value.position.sub(center);

    scene.value.add(model.value);

    // Adjust camera to fit the model
    const size = boundingBox.getSize(new THREE.Vector3()).length();
    const far = (size * 2) + boundingBox.min.distanceTo(boundingBox.max);
    if (camera.value) {
      camera.value.far = far;
      camera.value.updateProjectionMatrix();
      camera.value.position.z = size * 1.5;
    }
    if (controls.value && model.value) {
      controls.value.target.copy(model.value.position);
      controls.value.update();
    }
  } catch (error) {
    console.error('Error loading item from CDN:', error);
    // Display an error message to the user
  }
}

function animate(): void {
  if (renderer.value && scene.value && camera.value && controls.value) {
    requestAnimationFrame(animate);
    controls.value.update();
    renderer.value.render(scene.value, camera.value);
  }
}

onUnmounted(() => {
  if (renderer.value) {
    renderer.value.dispose();
  }
  if (model.value) {
    model.value.traverse((object: THREE.Object3D) => {
      if ((object as THREE.Mesh).isMesh) {
        const mesh = object as THREE.Mesh;
        mesh.geometry.dispose();
        if (mesh.material) {
          if (Array.isArray(mesh.material)) {
            mesh.material.forEach((m) => m.dispose());
          } else {
            (mesh.material as THREE.Material).dispose();
          }
        }
      }
    });
  }
});
</script>