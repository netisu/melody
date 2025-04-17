<script setup lang="ts">
import { reactive } from 'vue';

defineProps<{
    Objpath?: string,
    textureImage: string,
    viewWidth?: number,
    viewHeight?: number,
}>();

const rotation = reactive({
    x: -Math.PI / 2,
    y: 0,
    z: 0,
});
</script>

<template>
    <div id="container"></div>
</template>

<script lang="ts">
import * as Three from 'three'

export default {
  name: 'ThreeTest',
  data() {
    return {
      camera: null,
      scene: null,
      renderer: null,
      mesh: null
    }
  },
  methods: {
    init: function() {
        let container = document.getElementById('container');

        this.camera = new Three.PerspectiveCamera(70, container.clientWidth/container.clientHeight, 0.01, 10);
        this.camera.position.z = 1;

        this.scene = new Three.Scene();

        let geometry = new Three.BoxGeometry(0.2, 0.2, 0.2);
        let material = new Three.MeshNormalMaterial();

        this.mesh = new Three.Mesh(geometry, material);
        this.scene.add(this.mesh);

        this.renderer = new Three.WebGLRenderer({antialias: true});
        this.renderer.setSize(container.clientWidth, container.clientHeight);
        container.appendChild(this.renderer.domElement);

    },
    animate: function() {
        requestAnimationFrame(this.animate);
        rotation.z += 0.01;
        this.renderer.render(this.scene, this.camera);
    }
  },
  mounted() {
      this.init();
      this.animate();
  }
}
</script>

<style scoped>
  .container {
    height:256px;
  }
</style>