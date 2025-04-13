<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import { ref } from "vue";

const isPageLoading = ref(false);

interface Props {
  isLoading?: boolean;
}

const props = defineProps<Props>()

const loadingMessages = [
  "Loading...",
  "Use code (ImBored) for a free item!",
  "Please wait... (unless you have better things to do)",
  "One moment... gathering magical space hamsters to power this up.",
  "Fetching data faster than a squirrel with a nut allergy!",
  "We are not crashing, we are just taking a very scenic route.",
  "Hold on, coding a tiny dance for the loading bar.",
  "Just making sure the internet isn't filled with cat videos first.",
  "Shh, be vewy vewy quiet. We're hunting bugs.",
  "Did you know? The inventor of the loading bar is still waiting for it to finish.",
  "Don't worry, we paused Netflix for this.",
  "Please wait patiently... or impatiently, we don't judge.",
  "If this takes too long, blame the internet, not us.",
  "Calibrating the awesome meter. It's almost there!",
  "Just making sure it looks good on your grandma's flip phone.",
  "Taking a coffee break. You should too!",
  "Unicorns are galloping to bring you the data.",
  "Would you prefer a hamster wheel or a dance break while you wait?",
  "Warning: May cause extreme boredom or existential dread.",
  "Success is just around the corner... maybe.",
  "We are currently out of patience. Please send more coffee.",
];

const randomMessage = loadingMessages[Math.floor(Math.random() * loadingMessages.length)];
</script>
<template>
  <Transition>
    <div v-if="isPageLoading || props.isLoading" class="loading-page flex-dir-column py-5">
      <div class="loader__container flex-container-lg align-center">
        <div class="mb-1">
          <img :src="usePage<any>().props.site.icon" width="150" height="150" />
          <div
            v-if="usePage<any>().props.site.frontend.loading_messages"
            class="text-md text-muted fw-semibold"
          >
            {{ randomMessage }}
          </div>
          <div class="progress"></div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
html,
body {
  margin: 0;
  height: 100%;
  overflow: hidden;
}

.loading-page {
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  height: 100vh;
  z-index: 9999;
  position: fixed;
  margin: 0;
  top: 0;
  bottom: 0;
  background: var(--section-bg);
  background-size: cover;
  width: 100%;
}

.progress {
  width: 300px;
  max-width: 85vw;
  height: 4px;
  position: absolute;
  bottom: auto;
  left: 50%;
  border-radius: 4px;
  background: rgba(255, 255, 255, 0.15);
  transform: translate(-50%, -50%);
  overflow: hidden;
}

.progress::after {
  /* Use double colon for pseudo-element */
  content: "";
  display: block;
  width: 100%; /* Inherit width from parent */
  height: 4px; /* Inherit height from parent */
  background-color: var(--info-600);
  animation: load 5s linear;
}

@keyframes rotate {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

@keyframes prixClipFix {
  0% {
    clip-path: polygon(50% 50%, 0 0, 0 0, 0 0, 0 0, 0 0);
  }
  50% {
    clip-path: polygon(50% 50%, 0 0, 100% 0, 100% 0, 100% 0, 100% 0);
  }
  75%,
  100% {
    clip-path: polygon(50% 50%, 0 0, 100% 0, 100% 100%, 100% 100%, 100% 100%);
  }
}
.pulse {
  animation: pulse 3s infinite ease-in-out alternate;
}

@keyframes pulse {
  from {
    transform: scale(0.8);
  }

  to {
    transform: scale(1);
  }
}

/* Keyframe animation for a loading effect */
@keyframes load {
  from {
    width: 0;
  } /* Start at 0% width */
  0% {
    width: 0;
  } /* Maintain 0% width for the first frame */

  10% {
    width: 5%;
  }
  20% {
    width: 15%;
  }
  30% {
    width: 25%;
  }
  40% {
    width: 30%;
  }
  50% {
    width: 44%;
  } /* Adjusted from 40% for smoother transition */

  60% {
    width: 50%;
  }
  70% {
    width: 72%;
  }
  80% {
    width: 84%;
  }
  90% {
    width: 92%;
  }

  to {
    width: 100%;
  } /* End at 100% width */
}

.loader__container {
  /* Sizing */
  width: auto;
  height: auto;
  overflow: hidden;

  /* Flexbox stuff */
  display: flex;
  margin: 0;
  justify-content: center;
  align-items: center;

  /* Text styles */
  text-align: center;
}

/* we will explain what these classes do next! */
.v-enter-active {
  transition: opacity 0.2s ease;
  animation: bounce-in 0.4s;
}

.v-leave-active {
  transition: opacity 1s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
}

@keyframes bounce-in {
  0% {
    transform: scale(0);
  }

  50% {
    transform: scale(1.25);
  }

  100% {
    transform: scale(1);
  }
}
</style>
