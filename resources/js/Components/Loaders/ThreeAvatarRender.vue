<script setup lang="ts">
import { ref, reactive, watch, nextTick, onMounted } from "vue";
import { TresCanvas } from "@tresjs/core";
import { OrbitControls } from "@tresjs/cientos";
import * as THREE from "three";
import { OBJLoader } from "three/examples/jsm/loaders/OBJLoader.js";

// Define props to match the new structure
interface ItemRenderData {
    item: string;
    edit_style: {
        hash: string;
        is_model: boolean;
        is_texture: boolean;
    } | null;
}

const props = defineProps<{
    avatarConfig: {
        colors: {
            Head: string;
            Torso: string;
            LeftArm: string;
            RightArm: string;
            LeftLeg: string;
            RightLeg: string;
        };
        items: {
            shirt: ItemRenderData;
            pants: ItemRenderData;
            tshirt: ItemRenderData;
            face: ItemRenderData;
            addon: ItemRenderData;
            hats: ItemRenderData[];
            tool: ItemRenderData;
        },

    };
}>();

// --- Configuration ---
const CDN_URL = "https://cdn.netisu.com";
const ASSETS_PATH = `${CDN_URL}/assets/`;
const UPLOADS_PATH = `${CDN_URL}/uploads/`;

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
const aspectRatio = ref(512);
const viewerContainer = ref<HTMLElement | null>(null); // Ref to the viewer container element

// Store camera and orbit control targets for dynamic updates
const cameraPosition = reactive<[number, number, number]>([0, 0, 0]);
const cameraLookAt = reactive<[number, number, number]>([0, 0, 0]);
const orbitTarget = reactive<[number, number, number]>([0, 0, 0]);

// --- Helper for safe access to prop data ---
const currentAvatarConfig = reactive({
    colors: {
        Head: "FFFFFF",
        Torso: "800080",
        LeftArm: "FFFFFF",
        RightArm: "FFFFFF",
        LeftLeg: "483D8B",
        RightLeg: "483D8B",
    },
    items: {
        shirt: { item: "none", edit_style: null },
        pants: { item: "none", edit_style: null },
        tshirt: { item: "none", edit_style: null },
        face: { item: "none", edit_style: null },
        addon: { item: "none", edit_style: null },
        hats: [],
        tool: { item: "none", edit_style: null },
    }
});

// --- Three.js Loaders ---
const objLoader = new OBJLoader();
const textureLoader = new THREE.TextureLoader();

// --- Loading Functions ---
async function loadOBJModel(url: string): Promise<THREE.Mesh | null> {
    try {
        const object = await objLoader.loadAsync(url);
        if (object.children.length > 0 && object.children[0] instanceof THREE.Mesh) {
            const mesh = object.children[0] as THREE.Mesh;
            if (mesh.geometry) {
                return mesh;
            }
            console.warn(`OBJ model at ${url} loaded but has no valid geometry.`);
            return null;
        }
        console.warn(`OBJ model at ${url} did not contain a renderable mesh as first child.`);
        return null;
    } catch (error) {
        console.error(`Error loading OBJ model from ${url}:`, error);
        return null;
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

    const config = currentAvatarConfig;

    // Clear existing meshes from the reactive object before starting
    for (const key in avatarMeshes) {
        delete avatarMeshes[key];
    }

    // Temporary array to hold all successfully loaded THREE.Mesh instances for bounding box calculation
    const loadedSceneMeshes: THREE.Mesh[] = [];

    try {
        // Load all base models in parallel for efficiency
        const [
            craniumModel,
            chesticleModel,
            armRightModel,
            armLeftModel,
            legLeftModel,
            legRightModel,
            teeModel,
        ] = await Promise.all([
            loadOBJModel(`${ASSETS_PATH}cranium.obj`),
            loadOBJModel(`${ASSETS_PATH}chesticle.obj`),
            loadOBJModel(`${ASSETS_PATH}arm_right.obj`),
            loadOBJModel(`${ASSETS_PATH}arm_left.obj`),
            loadOBJModel(`${ASSETS_PATH}leg_left.obj`),
            loadOBJModel(`${ASSETS_PATH}leg_right.obj`),
            loadOBJModel(`${ASSETS_PATH}tee.obj`),
        ]);

        const models = {
            cranium: craniumModel,
            chesticle: chesticleModel,
            armRight: armRightModel,
            armLeft: armLeftModel,
            legLeft: legLeftModel,
            legRight: legRightModel,
            tee: teeModel,
        };

        // Process base parts and add to loadedSceneMeshes
        const parts = [
            { name: "chesticle", model: models.chesticle, color: config.colors.Torso },
            { name: "armRight", model: models.armRight, color: config.colors.RightArm },
            { name: "armLeft", model: models.armLeft, color: config.colors.LeftArm },
            { name: "legLeft", model: models.legLeft, color: config.colors.LeftLeg },
            { name: "legRight", model: models.legRight, color: config.colors.RightLeg },
            { name: "cranium", model: models.cranium, color: config.colors.Head },
        ];

        for (const part of parts) {
            if (part.model) {
                // model is already null if no valid geometry
                const material = new THREE.MeshPhongMaterial({
                    color: new THREE.Color(`#${part.color}`),
                });
                const mesh = new THREE.Mesh(part.model.geometry, material); // Use geometry directly
                mesh.name = part.name;
                avatarMeshes[part.name] = {
                    geometry: mesh.geometry,
                    material: mesh.material,
                    position: mesh.position.toArray() as [number, number, number],
                    rotation: mesh.rotation.toArray().slice(0, 3) as [number, number, number],
                };
                loadedSceneMeshes.push(mesh); // Add to temp array for bounding box
            }
        }

        // --- Apply Textures & Accessories ---
        const updateMeshMaterial = async (
            meshName: string,
            textureItem: ItemRenderData,
            defaultColor: string
        ) => {
            const currentMeshRef = avatarMeshes[meshName];
            if (!currentMeshRef) return; // Mesh reference must exist

            const meshInScene = loadedSceneMeshes.find((m) => m.name === meshName);
            if (!meshInScene) return; // Ensure the mesh is in the actual list for bounding box

            if (textureItem?.item && textureItem?.item !== "none") {
                try {
                    let texture;
                    if (textureItem?.edit_style) {
                        texture = await loadTexture(
                            `${UPLOADS_PATH}${textureItem?.edit_style?.hash}.png`
                        );
                    } else {
                        texture = await loadTexture(
                            `${UPLOADS_PATH}${textureItem?.item}.png`
                        );
                    }

                    if (texture) {
                        const material = new THREE.MeshPhongMaterial({
                            map: texture,
                            transparent: false,
                            alphaTest: 0.5,
                        });
                        currentMeshRef.material = material;
                        meshInScene.material = material; // Update material for bounding box mesh
                    } else {
                        const material = new THREE.MeshPhongMaterial({
                            color: new THREE.Color(`#${defaultColor}`),
                        });
                        currentMeshRef.material = material;
                        meshInScene.material = material;
                    }
                } catch (error) {
                    console.error(`Failed to load texture for ${meshName}:`, error);
                    const material = new THREE.MeshPhongMaterial({
                        color: new THREE.Color(`#${defaultColor}`),
                    });
                    currentMeshRef.material = material;
                    meshInScene.material = material;
                }
            } else {
                const material = new THREE.MeshPhongMaterial({
                    color: new THREE.Color(`#${defaultColor}`),
                });
                currentMeshRef.material = material;
                meshInScene.material = material;
            }
        };

        await Promise.all([
            updateMeshMaterial("chesticle", config.items?.shirt, config.colors.Torso),
            updateMeshMaterial("armRight", config.items?.shirt, config.colors.RightArm),
            updateMeshMaterial("armLeft", config.items?.shirt, config.colors.LeftArm),
            updateMeshMaterial("legLeft", config.items?.pants, config.colors.LeftLeg),
            updateMeshMaterial("legRight", config.items?.pants, config.colors.RightLeg),
        ]);

        // T-shirt handling
        if (config.items?.tshirt && config.items.tshirt?.item !== "none" && models.tee) {
            try {

                let tshirtTexture;
                if (config.items?.tshirt.edit_style) {
                    tshirtTexture = await loadTexture(
                        `${UPLOADS_PATH}${config.items?.tshirt?.edit_style?.hash}.png`
                    );
                } else {
                    tshirtTexture = await loadTexture(
                        `${UPLOADS_PATH}${config.items?.tshirt.item}.png`
                    );
                }

                const material = tshirtTexture
                    ? new THREE.MeshPhongMaterial({
                        map: tshirtTexture,
                        transparent: true,
                        alphaTest: 0.5,
                    })
                    : new THREE.MeshPhongMaterial({ color: 0xcccccc });
                const mesh = new THREE.Mesh(models.tee.geometry, material);
                mesh.name = "tee";
                avatarMeshes["tee"] = {
                    geometry: mesh.geometry,
                    material: mesh.material,
                    position: mesh.position.toArray() as [number, number, number],
                    rotation: mesh.rotation.toArray().slice(0, 3) as [number, number, number],
                };
                loadedSceneMeshes.push(mesh);
            } catch (error) {
                console.error(`Failed to load t-shirt texture or model:`, error);
                if (avatarMeshes["tee"]) delete avatarMeshes["tee"]; // Remove from reactive state
            }
        } else {
            if (avatarMeshes["tee"]) delete avatarMeshes["tee"];
        }

        // Face handling (with "default" check)
        if (models['cranium']) {
            // cranimum model should exist
            const cranimumMeshInScene = loadedSceneMeshes.find((m) => m.name === "cranium");
            if (cranimumMeshInScene) {
                if (config.items?.face && config.items?.face?.item !== "none") {
                    try {
                        let faceTexture;
                        if (config.items?.face?.edit_style) {
                            faceTexture = await loadTexture(
                                `${UPLOADS_PATH}${config.items?.face?.edit_style?.hash}.png`
                            );
                        } else {
                            faceTexture = await loadTexture(
                                `${UPLOADS_PATH}${config.items?.face?.item}.png`
                            );
                        }

                        const material = faceTexture
                            ? new THREE.MeshPhongMaterial({
                                map: faceTexture,
                                color: new THREE.Color(`#${config.colors.Head}`),
                                transparent: false,
                                depthWrite: false,
                                blending: THREE.NormalBlending,
                                side: THREE.FrontSide,
                            })
                            : new THREE.MeshPhongMaterial({
                                color: new THREE.Color(`#${config.colors.Head}`),
                                depthWrite: true,
                                transparent: false,
                                side: THREE.FrontSide,
                            });
                        avatarMeshes["cranium"].material = material;
                        cranimumMeshInScene.material = material;
                    } catch (error) {
                        console.error(`Failed to load face texture:`, error);
                        const DefaultfaceTexture = await loadTexture(`${ASSETS_PATH}default.png`);
                        const material = DefaultfaceTexture
                            ? new THREE.MeshPhongMaterial({
                                map: DefaultfaceTexture,
                                color: new THREE.Color(`#${config.colors.Head}`),
                                transparent: false,
                                depthWrite: false,
                                blending: THREE.NormalBlending,
                                side: THREE.FrontSide,
                            })
                            : new THREE.MeshPhongMaterial({
                                color: new THREE.Color(`#${config.colors.Head}`),
                            });
                        avatarMeshes["cranium"].material = material;
                        cranimumMeshInScene.material = material;
                    }
                } else {
                    const DefaultfaceTexture = await loadTexture(`${ASSETS_PATH}default.png`);
                    const material = DefaultfaceTexture
                        ? new THREE.MeshPhongMaterial({
                            map: DefaultfaceTexture,
                            color: new THREE.Color(`#${config.colors.Head}`),
                            transparent: false,
                            depthWrite: false,
                            blending: THREE.NormalBlending,
                            side: THREE.FrontSide,
                        })
                        : new THREE.MeshPhongMaterial({
                            color: new THREE.Color(`#${config.colors.Head}`),
                        });
                    avatarMeshes["cranium"].material = material;
                    cranimumMeshInScene.material = material;
                }
            }
        }

        // Hats (Iterate and load actual models/textures)
        for (let i = 0; i < (config.items?.hats || []).length; i++) {
            const hatItem = config.items?.hats[i];
            const hatMeshName = `hat_${i}`;

            // Ensure cleanup of previous hat for this index
            if (avatarMeshes[hatMeshName]) delete avatarMeshes[hatMeshName];
            const oldMeshIdx = loadedSceneMeshes.findIndex((m) => m.name === hatMeshName);
            if (oldMeshIdx > -1) loadedSceneMeshes.splice(oldMeshIdx, 1);

            if (hatItem?.item && hatItem?.item !== "none") {
                try {
                    let loadedHatModel;
                    if (hatItem?.edit_style?.is_model) {
                        loadedHatModel = await loadOBJModel(
                            `${UPLOADS_PATH}${hatItem?.edit_style?.hash}.obj`
                        );
                    } else {
                        loadedHatModel = await loadOBJModel(
                            `${UPLOADS_PATH}${hatItem?.item}.obj`
                        );
                    }
                    if (loadedHatModel) {
                        // Only proceed if model loaded successfully
                        let loadedHatTexture;
                        if (hatItem.edit_style.is_texture) {
                            loadedHatTexture = await loadTexture(
                                `${UPLOADS_PATH}${hatItem?.edit_style?.hash}.png`
                            );
                        } else {
                            loadedHatTexture = await loadTexture(
                                `${UPLOADS_PATH}${hatItem?.item}.png`
                            );
                        }
                        const material = loadedHatTexture
                            ? new THREE.MeshPhongMaterial({
                                map: loadedHatTexture,
                                transparent: false,
                                alphaTest: 0.5,
                            })
                            : new THREE.MeshPhongMaterial({ color: 0x888888 });

                        const mesh = new THREE.Mesh(loadedHatModel.geometry, material);
                        mesh.name = hatMeshName;
                        mesh.position.set(0, 8.5 + i * 0.6, 0); // Default position, adjust per model if needed

                        avatarMeshes[hatMeshName] = {
                            geometry: mesh.geometry,
                            material: mesh.material,
                            position: mesh.position.toArray() as [number, number, number],
                        };
                        loadedSceneMeshes.push(mesh); // Add actual mesh
                    } else {
                        console.warn(
                            `Hat model for '${hatItem?.item}' could not be loaded. Skipping this hat.`
                        );
                    }
                } catch (error) {
                    console.error(`Error loading hat item '${hatItem?.item}':`, error);
                }
            }
        }

        // Addon handling
        if (config.items?.addon && config.items?.addon?.item !== "none") {
            const addonMeshName = "addon";

            try {
                let loadedAddonModel;
                if (config.items?.addon?.edit_style && config.items?.addon?.edit_style?.is_model) {
                    loadedAddonModel = await loadOBJModel(
                        `${UPLOADS_PATH}${config.items?.addon?.edit_style?.hash}.obj`
                    );
                } else {
                    loadedAddonModel = await loadOBJModel(
                        `${UPLOADS_PATH}${config.items?.addon?.item}.obj`
                    );
                }

                if (loadedAddonModel) {
                    let loadedAddonTexture;

                    // Only proceed if model loaded successfully
                    if (config.items?.addon?.edit_style?.is_texture) {
                        loadedAddonTexture = await loadTexture(
                            `${UPLOADS_PATH}${config.items?.addon?.edit_style?.hash}.png`
                        );
                    } else {
                        loadedAddonTexture = await loadTexture(
                            `${UPLOADS_PATH}${config.items?.addon?.item}.png`
                        );
                    }
                    const material = loadedAddonTexture
                        ? new THREE.MeshPhongMaterial({
                            map: loadedAddonTexture,
                            transparent: false,
                            alphaTest: 0.5,
                        })
                        : new THREE.MeshPhongMaterial({ color: 0x996633 });

                    const mesh = new THREE.Mesh(loadedAddonModel.geometry, material);
                    mesh.name = addonMeshName;
                    // mesh.position.set(0, 7.5, -1);
                    avatarMeshes["addon"] = {
                        geometry: mesh.geometry,
                        material: mesh.material,
                        position: mesh.position.toArray() as [number, number, number],
                    };
                    loadedSceneMeshes.push(mesh); // Add actual mesh
                } else {
                    console.warn(
                        `Addon model for '${config.items?.addon?.item}' could not be loaded. Skipping this addon.`
                    );
                }
            } catch (error) {
                console.error(`Error loading addon item '${config.items?.addon?.item}':`, error);
            }
        } else {
            if (avatarMeshes["addon"]) delete avatarMeshes["addon"];
        }

        // --- Create tempAvatarGroup with collected meshes and calculate bounding box ---
        const tempAvatarGroup = new THREE.Group();
        // Add only the successfully loaded and processed meshes to the temp group
        loadedSceneMeshes.forEach((mesh) => tempAvatarGroup.add(mesh));

        // If tempAvatarGroup has no children (e.g., all models failed to load),
        // set a default camera view and exit.
        if (tempAvatarGroup.children.length === 0) {
            console.warn("No valid avatar parts loaded. Using default camera view.");
            cameraPosition[0] = -6;
            cameraPosition[1] = 14;
            cameraPosition[2] = 32;
            cameraLookAt[0] = 0;
            cameraLookAt[1] = 3.5;
            cameraLookAt[2] = 0;
            orbitTarget[0] = -0.5;
            orbitTarget[1] = 5;
            orbitTarget[2] = 0;
            return;
        }

        const box = new THREE.Box3().setFromObject(tempAvatarGroup);

        // Final check for an empty bounding box, which can happen if geometries are degenerate
        if (box.isEmpty()) {
            console.warn(
                "Bounding box is empty even with children present. Likely invalid or degenerate geometry. Using default camera view."
            );
            cameraPosition[0] = -6;
            cameraPosition[1] = 14;
            cameraPosition[2] = 32;
            cameraLookAt[0] = 0;
            cameraLookAt[1] = 3.5;
            cameraLookAt[2] = 0;
            orbitTarget[0] = -0.5;
            orbitTarget[1] = 5;
            orbitTarget[2] = 0;
            return;
        }

        const size = new THREE.Vector3();
        const center = new THREE.Vector3();
        box.getSize(size);
        box.getCenter(center);

        const offsetY = -box.min.y; // Bring base to Y=0

        avatarGroupPosition[0] = -center.x;
        avatarGroupPosition[1] = offsetY;
        avatarGroupPosition[2] = -center.z;

        const fov = 22.5;
        const fovRad = THREE.MathUtils.degToRad(fov);

        const objectHeight = size.y;
        let distance = (objectHeight / 2) / Math.tan(fovRad / 2);

        const objectWidth = size.x;
        const horizontalFovRad = 2 * Math.atan(Math.tan(fovRad / 2) * aspectRatio.value);
        const horizontalDistance = (objectWidth / 2) / Math.tan(horizontalFovRad / 2);
        distance = Math.max(distance, horizontalDistance);


        const padding = 1.5;
        distance *= padding;

        cameraPosition[0] = -6;
        cameraPosition[1] = center.y + offsetY + (size.y * 0.2);
        cameraPosition[2] = distance;

        cameraLookAt[0] = 0;
        cameraLookAt[1] = center.y + offsetY + (size.y * 0.2);
        cameraLookAt[2] = 0;

        orbitTarget[0] = cameraLookAt[0];
        orbitTarget[1] = cameraLookAt[1];
        orbitTarget[2] = cameraLookAt[2];
    } catch (error: any) {
        console.error("Failed to load avatar:", error);
    }
}

// Function to update aspect ratio
function updateAspectRatio() {
    if (viewerContainer.value) {
        const width = viewerContainer.value.offsetWidth;
        const height = viewerContainer.value.offsetHeight;
        if (width > 0 && height > 0) {
            aspectRatio.value = width / height;
            generateTresjsObjects(); // Re-trigger on resize
        }
    }
}

onMounted(() => {
    nextTick(() => {
        updateAspectRatio();
        window.addEventListener("resize", updateAspectRatio);
    });
});

// Watcher: Re-generate avatar whenever avatarConfig prop changes
watch(
    () => props.avatarConfig,
    (newConfig) => {
        if (newConfig) {
            Object.assign(currentAvatarConfig.colors, newConfig.colors);
            currentAvatarConfig['items'].shirt = newConfig.items?.shirt;
            currentAvatarConfig['items'].pants = newConfig.items?.pants;
            currentAvatarConfig['items'].tshirt = newConfig.items?.tshirt;
            currentAvatarConfig['items'].face = newConfig.items?.face;
            currentAvatarConfig['items'].addon = newConfig.items?.addon;
            currentAvatarConfig['items'].tool = newConfig.items?.tool;
            currentAvatarConfig['items'].hats = newConfig.items?.hats || [];
        } else {
            Object.assign(currentAvatarConfig.colors, {
                Head: "FFFFFF",
                Torso: "800080",
                LeftArm: "FFFFFF",
                RightArm: "FFFFFF",
                LeftLeg: "483D8B",
                RightLeg: "483D8B",
            });
            currentAvatarConfig['items'].shirt = { item: "none", edit_style: null };
            currentAvatarConfig['items'].pants = { item: "none", edit_style: null };
            currentAvatarConfig['items'].tshirt = { item: "none", edit_style: null };
            currentAvatarConfig['items'].face = { item: "none", edit_style: null };
            currentAvatarConfig['items'].addon = { item: "none", edit_style: null };
            currentAvatarConfig['items'].tool = { item: "none", edit_style: null };
            currentAvatarConfig['items'].hats = [];
        }
        generateTresjsObjects();
    },
    { deep: true, immediate: true }
);
</script>

<template>
    <TresCanvas :alpha="true" :shadows="true" render-mode="on-demand">
        <!-- Camera parameters are now dynamically set based on avatar bounding box -->
        <TresPerspectiveCamera :position="cameraPosition" :look-at="cameraLookAt" :up="[0, 1, 0]" :fov="22.5"
            :aspect="aspectRatio" :near="0.1" :far="1000" />
        <!-- OrbitControls target also dynamically set -->
        <OrbitControls :target="orbitTarget" :enable-damping="true" :damping-factor="0.05" :min-distance="2"
            :max-distance="20" :auto-rotate="true" :auto-rotate-speed="2" :enable-pan="true" :enable-zoom="true" />
        <TresAmbientLight :intensity="2.5" :color="0xb0b0b0" />
        <TresDirectionalLight :position="[-1, 3, 5]" :intensity="6" :color="0x808080" />

        <!-- Main TresGroup for the avatar, its position will be adjusted for centering -->
        <TresGroup :position="avatarGroupPosition">
            <!-- Dynamically rendered avatar parts -->
            <template v-for="(meshData, name) in avatarMeshes" :key="name">
                <TresMesh v-if="meshData.geometry" :geometry="meshData.geometry" :material="meshData.material"
                    :position="meshData.position || [0, 0, 0]" :rotation="meshData.rotation || [0, 0, 0]" />
            </template>
        </TresGroup>
    </TresCanvas>
</template>
