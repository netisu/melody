<script setup lang="ts">
import { ref, reactive, watch, nextTick, onMounted } from 'vue'; // Added onMounted
import { TresCanvas } from '@tresjs/core'; // Changed from @tresjs/cientos
import { OrbitControls } from '@tresjs/cientos';
import * as THREE from 'three';
import { OBJLoader } from 'three/examples/jsm/loaders/OBJLoader.js';

// Define props to match the new structure
const props = defineProps<{
    avatarConfig?: { // Prop is now optional
        colors: {
            head: string;
            torso: string;
            left_arm: string;
            right_arm: string;
            left_leg: string;
            right_leg: string;
        };
        shirt: { item: string | null };
        pants: { item: string | null };
        tshirt: { item: string | null };
        face: { item: string | null };
        addon: { item: string | null };
        hats: { item: string | null }[];
        tool: { item: string | null };
    };
}>();


// --- Configuration ---
const CDN_URL = "https://cdn.netisu.com";
const ASSETS_PATH = `${CDN_URL}/assets/`;
const UPLOADS_PATH = `${CDN_URL}/uploads/`;

// --- State Management (Composition API) ---
const isLoading = ref(true);
const loadingError = ref<string | null>(null);

// Reactive reference to hold the Three.js mesh data for dynamic rendering
const avatarMeshes = reactive<{
    [key: string]: {
        geometry: THREE.BufferGeometry;
        material: THREE.Material;
        position?: [number, number, number];
        rotation?: [number, number, number];
    };
}>({});

// Reactive variable to store the calculated offset for the entire avatar group
const avatarGroupPosition = reactive<[number, number, number]>([0, 0, 0]);

// Reactive variable for aspect ratio
const aspectRatio = ref(1);
const viewerContainer = ref<HTMLElement | null>(null); // Ref to the viewer container element

// Store camera and orbit control targets for dynamic updates
const cameraPosition = reactive<[number, number, number]>([0, 0, 0]);
const cameraLookAt = reactive<[number, number, number]>([0, 0, 0]);
const orbitTarget = reactive<[number, number, number]>([0, 0, 0]);


// --- Helper for safe access to prop data ---
// This ensures `currentAvatarConfig` always has a predictable structure
const currentAvatarConfig = reactive({
    colors: {
        head: "FFFFFF", torso: "800080", left_arm: "FFFFFF", right_arm: "FFFFFF", left_leg: "483D8B", right_leg: "483D8B",
    },
    shirt: { item: null },
    pants: { item: null },
    tshirt: { item: null },
    face: { item: null },
    addon: { item: null },
    hats: [],
    tool: { item: null },
});

// --- Three.js Loaders ---
const objLoader = new OBJLoader();
const textureLoader = new THREE.TextureLoader();

// --- Loading Functions ---
async function loadOBJModel(url: string): Promise<THREE.Mesh> {
    try {
        const object = await objLoader.loadAsync(url);
        if (object.children.length > 0 && object.children[0] instanceof THREE.Mesh) {
            if (object.children[0].geometry) {
                return object.children[0] as THREE.Mesh;
            }
            throw new Error(`OBJ model at ${url} did not contain valid geometry.`);
        }
        throw new Error(`OBJ model at ${url} did not contain a renderable mesh.`);
    } catch (error) {
        console.error(`Error loading OBJ model from ${url}:`, error);
        throw error;
    }
}

async function loadTexture(url: string): Promise<THREE.Texture | null> {
    try {
        const texture = await textureLoader.loadAsync(url);
        return texture;
    } catch (error) {
        console.error(`Error loading texture from ${url}:`, error);
        return null;
    }
}

// --- Avatar Generation Logic ---
async function generateTresjsObjects() {
    isLoading.value = true;
    loadingError.value = null;

    const config = currentAvatarConfig;

    try {
        // Load all base models
        const models: { [key: string]: THREE.Mesh } = {
            cranium: await loadOBJModel(`${ASSETS_PATH}cranium.obj`),
            chesticle: await loadOBJModel(`${ASSETS_PATH}chesticle.obj`),
            armRight: await loadOBJModel(`${ASSETS_PATH}arm_right.obj`),
            armLeft: await loadOBJModel(`${ASSETS_PATH}arm_left.obj`),
            legLeft: await loadOBJModel(`${ASSETS_PATH}leg_left.obj`),
            legRight: await loadOBJModel(`${ASSETS_PATH}leg_right.obj`),
            tee: await loadOBJModel(`${ASSETS_PATH}tee.obj`),
        };

        // Clear existing meshes from the reactive object
        for (const key in avatarMeshes) {
            delete avatarMeshes[key];
        }

        // Temporary group to calculate overall bounding box
        const tempAvatarGroup = new THREE.Group();

        // Process base parts
        const parts = [
            { name: 'chesticle', model: models?.['chesticle'], color: config.colors.torso },
            { name: 'armRight', model: models?.['armRight'], color: config.colors.right_arm },
            { name: 'armLeft', model: models?.['armLeft'], color: config.colors.left_arm },
            { name: 'legLeft', model: models?.['legLeft'], color: config.colors.left_leg },
            { name: 'legRight', model: models?.['legRight'], color: config.colors.right_leg },
            { name: 'cranium', model: models?.['cranium'], color: config.colors.head },
        ];

        parts.forEach(part => {
            if (part.model) {
                const material = new THREE.MeshPhongMaterial({ color: new THREE.Color(`#${part.color}`) });
                const mesh = new THREE.Mesh(part.model.geometry, material);
                mesh.name = part.name; // Set name for easy retrieval from tempAvatarGroup
                avatarMeshes[part.name] = {
                    geometry: mesh.geometry,
                    material: mesh.material,
                    position: mesh.position.toArray() as [number, number, number],
                    rotation: mesh.rotation.toArray().slice(0, 3) as [number, number, number],
                };
                tempAvatarGroup.add(mesh);
            }
        });

        // --- Apply Textures & Accessories ---
        const updateMeshAndGroup = async (meshName: string, modelMesh: THREE.Mesh | undefined, textureItem: string | null, defaultColor: string) => {
            if (!modelMesh) return;

            if (textureItem !== null || textureItem !== "none") {
                try {
                    const texture = await loadTexture(`${UPLOADS_PATH}${textureItem}.png`);
                    if (texture) {
                        const material = new THREE.MeshPhongMaterial({ map: texture, transparent: true, alphaTest: 0.5 });
                        if (avatarMeshes[meshName]) {
                            avatarMeshes[meshName].material = material;
                            const tempMesh = tempAvatarGroup.getObjectByName(meshName);
                            if (tempMesh instanceof THREE.Mesh) tempMesh.material = material;
                        }
                    } else {
                        // Fallback to color if texture fails to load
                        const material = new THREE.MeshPhongMaterial({ color: new THREE.Color(`#${defaultColor}`) });
                        if (avatarMeshes[meshName]) avatarMeshes[meshName].material = material;
                        const tempMesh = tempAvatarGroup.getObjectByName(meshName);
                        if (tempMesh instanceof THREE.Mesh) tempMesh.material = material;
                    }
                } catch (error) {
                    console.error(`Failed to load texture for ${meshName}:`, error);
                    const material = new THREE.MeshPhongMaterial({ color: new THREE.Color(`#${defaultColor}`) });
                    if (avatarMeshes[meshName]) avatarMeshes[meshName].material = material;
                    const tempMesh = tempAvatarGroup.getObjectByName(meshName);
                    if (tempMesh instanceof THREE.Mesh) tempMesh.material = material;
                }
            } else {
                const material = new THREE.MeshPhongMaterial({ color: new THREE.Color(`#${defaultColor}`) });
                if (avatarMeshes[meshName]) avatarMeshes[meshName].material = material;
                const tempMesh = tempAvatarGroup.getObjectByName(meshName);
                if (tempMesh instanceof THREE.Mesh) tempMesh.material = material;
            }
        };

        // Use await for these texture loads to ensure they are done before bounding box calculation
        await updateMeshAndGroup('chesticle', models?.['chesticle'], config.shirt?.item, config.colors.torso);
        await updateMeshAndGroup('armRight', models?.['armRight'], config.shirt?.item, config.colors.right_arm);
        await updateMeshAndGroup('armLeft', models?.['armLeft'], config.shirt?.item, config.colors.left_arm);
        await updateMeshAndGroup('legLeft', models?.['legLeft'], config.pants?.item, config.colors.left_leg);
        await updateMeshAndGroup('legRight', models?.['legRight'], config.pants?.item, config.colors.right_leg);

        // T-shirt handling
        if (config.tshirt?.item !== null && config.tshirt?.item !== "none") {
            try {
                const tshirtTexture = await loadTexture(`${UPLOADS_PATH}${config.tshirt.item}.png`);
                if (tshirtTexture && models.tee) {
                    const material = new THREE.MeshPhongMaterial({ map: tshirtTexture, transparent: true, alphaTest: 0.5 });
                    const mesh = new THREE.Mesh(models?.['tee'].geometry, material);
                    mesh.name = 'tee';
                    avatarMeshes.tee = {
                        geometry: mesh.geometry,
                        material: mesh.material,
                        position: mesh.position.toArray() as [number, number, number],
                        rotation: mesh.rotation.toArray().slice(0, 3) as [number, number, number],
                    };
                    tempAvatarGroup.add(mesh);
                }
            } catch (error) {
                console.error(`Failed to load t-shirt texture:`, error);
                if (avatarMeshes.tee) delete avatarMeshes.tee; // Remove if loading fails
            }
        } else {
            if (avatarMeshes.tee) delete avatarMeshes.tee;
        }

        // Face handling (with "default" check)
        if (config.face !== null && config.face?.item !== null) {
            try {
                const faceTexture = await loadTexture(`${UPLOADS_PATH}${config.face?.item}.png`);
                if (faceTexture) {
                    const material = new THREE.MeshPhongMaterial({ map: faceTexture, color: new THREE.Color(`#${config.colors.head}`), transparent: true, alphaTest: 0.5 });
                    if (avatarMeshes?.['cranium']) {
                        avatarMeshes.cranium.material = material;
                        const tempMesh = tempAvatarGroup.getObjectByName('cranium');
                        if (tempMesh instanceof THREE.Mesh) tempMesh.material = material;
                    }
                } else {
                    // Fallback to default.png if texture fails to load
                    const DefaultfaceTexture = await loadTexture(`${ASSETS_PATH}default.png`);
                    if (DefaultfaceTexture) {
                        const material = new THREE.MeshPhongMaterial({ map: DefaultfaceTexture, color: new THREE.Color(`#${config.colors.head}`), transparent: true, alphaTest: 0.5 });
                        if (avatarMeshes.cranium) {
                            avatarMeshes.cranium.material = material;
                            const tempMesh = tempAvatarGroup.getObjectByName('cranium');
                            if (tempMesh instanceof THREE.Mesh) tempMesh.material = material;
                        }
                    }
                }
            } catch (error) {
                console.error(`Failed to load face texture:`, error);
                // Fallback to default.png if loading fails completely
                const DefaultfaceTexture = await loadTexture(`${ASSETS_PATH}default.png`);
                if (DefaultfaceTexture) {
                    const material = new THREE.MeshPhongMaterial({ map: DefaultfaceTexture, color: new THREE.Color(`#${config.colors.head}`), transparent: true, alphaTest: 0.5 });
                    if (avatarMeshes.cranium) {
                        avatarMeshes.cranium.material = material;
                        const tempMesh = tempAvatarGroup.getObjectByName('cranium');
                        if (tempMesh instanceof THREE.Mesh) tempMesh.material = material;
                    }
                }
            }
        } else {
            // Load default.png if face.item is null or "default"
            const DefaultfaceTexture = await loadTexture(`${ASSETS_PATH}default.png`);
            if (DefaultfaceTexture) {
                const material = new THREE.MeshPhongMaterial({ map: DefaultfaceTexture, color: new THREE.Color(`#${config.colors.head}`), transparent: true, alphaTest: 0.5 });
                if (avatarMeshes.cranium) {
                    avatarMeshes.cranium.material = material;
                    const tempMesh = tempAvatarGroup.getObjectByName('cranium');
                    if (tempMesh instanceof THREE.Mesh) tempMesh.material = material;
                }
            }
        }


        // Hats and Addons
        /*
        for (const hatItem of props.avatarConfig.hats || []) {
            if (hatItem?.item !== null && hatItem?.item !== "none") {
                try {
                    // Attempt to load the actual OBJ model for the hat
                    const hatModel = await loadOBJModel(`${UPLOADS_PATH}${hatItem.item}.obj`);
                    const hatTexture = await loadTexture(`${UPLOADS_PATH}${hatItem.item}.png`);

                    if (hatModel) {
                        const existingHatMeshName = `hat_${hatItem.item}`; // Example naming convention

                        const material = hatTexture
                            ? new THREE.MeshPhongMaterial({ map: hatTexture, transparent: true, alphaTest: 0.5 })
                            : new THREE.MeshPhongMaterial({ color: 0x888888 });

                        const mesh = new THREE.Mesh(hatModel.geometry, material);
                        mesh.name = existingHatMeshName;

                        mesh.position.set(0, 8.5, 0); // Adjust this based on actual hat model positioning needs

                        avatarMeshes[existingHatMeshName] = {
                            geometry: mesh.geometry,
                            material: mesh.material,
                            position: mesh.position.toArray() as [number, number, number]
                        };
                        tempAvatarGroup.add(mesh); // Add to temp group for bounding box calc

                    } else {
                        console.warn(`Hat item '${hatItem.item}' model failed to load. Using placeholder.`);
                        // Fallback to placeholder if OBJ model fails to load
                        const placeholderMesh = new THREE.Mesh(
                            new THREE.BoxGeometry(1.2, 0.5, 1.2),
                            new THREE.MeshPhongMaterial({ color: 0x888888 })
                        );
                        const placeholderName = `hat_placeholder_${hatItem.item}`;
                        placeholderMesh.name = placeholderName;
                        placeholderMesh.position.set(0, 8.5, 0); // Position relative to avatar
                        avatarMeshes[placeholderName] = {
                            geometry: placeholderMesh.geometry,
                            material: placeholderMesh.material,
                            position: placeholderMesh.position.toArray() as [number, number, number]
                        };
                        tempAvatarGroup.add(placeholderMesh);
                    }
                } catch (error) {
                    console.error(`Error processing hat item '${hatItem.item}':`, error);
                }
            }
        }
*/
        if (config.addon !== null && config.addon?.item !== null) { // Access directly from config.addon
            // For actual OBJ addons, loadOBJModel here and add to tempAvatarGroup
            const addonGeom = new THREE.SphereGeometry(0.5, 16, 16);
            const addonMat = new THREE.MeshPhongMaterial({ color: 0x996633 });
            const addonMesh = new THREE.Mesh(addonGeom, addonMat);
            addonMesh.name = 'addon';
            addonMesh.position.set(0, 7.5, -1);
            avatarMeshes.addon = {
                geometry: addonMesh.geometry,
                material: addonMesh.material,
                position: addonMesh.position.toArray() as [number, number, number]
            };
            tempAvatarGroup.add(addonMesh);
            console.warn(`Addon item '${config.addon?.item}' is a placeholder. Implement actual OBJ loading for addons.`);
        } else {
            if (avatarMeshes.addon) delete avatarMeshes.addon;
        }

        // --- Dynamic Camera Adjustment based on Bounding Box ---
        const box = new THREE.Box3().setFromObject(tempAvatarGroup);
        const size = new THREE.Vector3();
        const center = new THREE.Vector3();
        box.getSize(size);
        box.getCenter(center);

        // Calculate vertical offset to bring the avatar's lowest point (feet) to Y=0 in the scene
        const offsetY = -box.min.y;

        // Adjust the main avatar group's position
        avatarGroupPosition[0] = -center.x; // Center horizontally
        avatarGroupPosition[1] = offsetY;   // Move base to Y=0
        avatarGroupPosition[2] = -center.z; // Center depth

        // Calculate optimal camera distance and position
        const fov = 22.5; // Your fixed FOV
        const fovRad = THREE.MathUtils.degToRad(fov);

        // Get the effective height the camera needs to see (total height of the object)
        const objectHeight = size.y;

        // Calculate distance based on object height and camera FOV
        // d = (h/2) / tan(fov/2)
        let distance = (objectHeight / 2) / Math.tan(fovRad / 2);

        // Adjust distance for aspect ratio if width is the limiting factor (taller than wide viewport)
        // This part is crucial for making sure content fits horizontally if the aspect ratio is very wide.
        const objectWidth = size.x;
        const horizontalFovRad = 2 * Math.atan(Math.tan(fovRad / 2) * aspectRatio.value);
        const horizontalDistance = (objectWidth / 2) / Math.tan(horizontalFovRad / 2);
        distance = Math.max(distance, horizontalDistance); // Take the greater distance to fit both dimensions

        // Add some padding to the distance
        const padding = 1.2; // Increase this value to "zoom out" slightly more
        distance *= padding;

        // Set camera's new position and look-at target
        // Camera X position will be slightly negative to get the angled view from your example [-6, Y, Z]
        // The Y look-at target should be the new center Y of the avatar (center.y + offsetY)
        // The Z position should be the calculated distance
        cameraPosition[0] = -6; // angled view on X
        cameraPosition[1] = center.y + offsetY + (size.y * 0.2); // Look slightly above the center for full head visibility
        cameraPosition[2] = distance + 5; // Add some extra Z offset from your example

        cameraLookAt[0] = 0; // X is centered for look-at
        cameraLookAt[1] = center.y + offsetY + (size.y * 0.1); // Look slightly above the exact center
        cameraLookAt[2] = 0; // Z is centered for look-at

        // OrbitControls target should also match the new look-at target
        orbitTarget[0] = cameraLookAt[0];
        orbitTarget[1] = cameraLookAt[1];
        orbitTarget[2] = cameraLookAt[2];

    } catch (error: any) {
        loadingError.value = `Failed to load avatar: ${error.message}`;
        console.error("Full error:", error);
    } finally {
        isLoading.value = false;
    }
}

// Function to update aspect ratio
function updateAspectRatio() {
    if (viewerContainer.value) {
        const width = viewerContainer.value.offsetWidth;
        const height = viewerContainer.value.offsetHeight;
        if (width > 0 && height > 0) {
            aspectRatio.value = width / height;
            // Re-trigger avatar generation on aspect ratio change to re-calculate camera
            generateTresjsObjects();
        }
    }
}

onMounted(() => {
    nextTick(() => {
        updateAspectRatio();
        window.addEventListener('resize', updateAspectRatio);
    });
    // Initial generation is already triggered by watch(props.avatarConfig, ...) with immediate: true
});

// Watcher: Re-generate avatar whenever avatarConfig prop changes
watch(() => props.avatarConfig, (newConfig) => {
    if (newConfig) {
        // Deep merge the incoming prop into the reactive local config
        Object.assign(currentAvatarConfig.colors, newConfig.colors);
        currentAvatarConfig.shirt = newConfig.shirt;
        currentAvatarConfig.pants = newConfig.pants;
        currentAvatarConfig.tshirt = newConfig.tshirt;
        currentAvatarConfig.face = newConfig.face;
        currentAvatarConfig.addon = newConfig.addon;
        currentAvatarConfig.tool = newConfig.tool;
        currentAvatarConfig.hats = newConfig.hats || [];
    } else {
        // If newConfig is null/undefined, reset to default internal values
        Object.assign(currentAvatarConfig.colors, {
            head: "FFFFFF", torso: "800080", left_arm: "FFFFFF", right_arm: "FFFFFF", left_leg: "483D8B", right_leg: "483D8B",
        });
        currentAvatarConfig.shirt = { item: null };
        currentAvatarConfig.pants = { item: null };
        currentAvatarConfig.tshirt = { item: null };
        currentAvatarConfig.face = { item: null };
        currentAvatarConfig.addon = { item: null };
        currentAvatarConfig.tool = { item: null };
        currentAvatarConfig.hats = [];
    }
    generateTresjsObjects();
}, { deep: true, immediate: true });

</script>

<template>
    <div class="full-screen-container" ref="viewerContainer">
        <div id="viewer-container">
            <div class="gap-3 text-center flex-container flex-dir-column" v-if="isLoading || loadingError">
                <i v-show="loadingError == null" class="text-8xl fas fa-spin fa-spinner text-muted "></i>
                <div style="line-height: 5px">
                    <div class="text-sm text-muted fw-semibold">
                        {{ loadingError ? loadingError : '' }}
                    </div>
                </div>
            </div>
            <TresCanvas :alpha="true" :shadows="true" render-mode="on-demand" v-if="loadingError == null && !isLoading"
                :antialias="false">
                <!-- Camera parameters are now dynamically set based on avatar bounding box -->
                <TresPerspectiveCamera :position="cameraPosition" :look-at="cameraLookAt" :up="[0, 1, 0]" :fov="22.5"
                    :aspect="aspectRatio" :near="0.1" :far="1000" />
                <!-- OrbitControls target also dynamically set -->
                <OrbitControls :target="orbitTarget" :enable-damping="true" :damping-factor="0.05" :min-distance="2"
                    :max-distance="20" :auto-rotate="true" :auto-rotate-speed="2" :enable-pan="true"
                    :enable-zoom="true" />
                <TresAmbientLight :intensity="2.5" :color="0xb0b0b0" />
                <TresDirectionalLight :position="[-1, 3, 5]" :intensity="6" :color="0x808080"
                    :shadow-map-size="[512, 512]" />

                <!-- Main TresGroup for the avatar, its position will be adjusted for centering -->
                <TresGroup :position="avatarGroupPosition">
                    <!-- Dynamically rendered avatar parts -->
                    <template v-for="(meshData, name) in avatarMeshes" :key="name">
                        <TresMesh v-if="meshData.geometry" :geometry="meshData.geometry" :material="meshData.material"
                            :position="meshData.position || [0, 0, 0]" :rotation="meshData.rotation || [0, 0, 0]" />
                    </template>
                </TresGroup>
            </TresCanvas>
        </div>
    </div>
</template>

<style scoped>
/* Scoped styles for the Vue component */
.full-screen-container {
    width: 100vw;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: "Inter", sans-serif;
    /* Add these styles to your canvas in your main CSS file/component, NOT here directly */
    /* This ensures the outer container sizes correctly */
    max-width: 100%;
    height: 261.5px !important;
    /* The !important is from your example. Consider if it's strictly needed. */
    display: inline-block;
    /* from your example */
    vertical-align: middle;
    /* from your example */
    -ms-interpolation-mode: bicubic;
    /* from your example, for image scaling, not directly for 3D */
}

#viewer-container {
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: transparent;
    /* Dark background */
    position: relative;
    /* Needed for loading overlay */
}
</style>
