<script setup lang="ts">
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import { route } from "ziggy-js";

defineProps<{
    achievements: Array<any>,
}>();

</script>

<template>
<AppHead pageTitle="Acheivements" description="A List of all Achievements" :url="route('acheivements')" />
    <Navbar />
    <Sidebar>
        <div class="cell medium-9">
            <div class="text-xl fw-semibold mb-1">Achievements</div>
            <div class="card card-body mb-3">

                <div class="section flex-container align-middle gap-3">
                    <img src="file:///home/chronos/u-b99c190e11ee42e371d918a0399d97cb71a388b8/MyFiles/eclipse-frontend/assets/img/achievement_dummy.png" width="100">
                    <div>
                        <div class="text-lg fw-semibold">
                            Administrator<span class="text-xs fw-semibold text-muted mx-1">•</span><span class="text-xs fw-semibold text-muted">
                                2 users have made this achievement.
                            </span>
                        </div>
                        <div class="text-muted text-sm">
                            Lorem ipsum dolor sit amet, consectetur
                            adipiscing elit.
                        </div>
                    </div>
                </div>
                <div class="section flex-container align-middle gap-3">
                    <img src="file:///home/chronos/u-b99c190e11ee42e371d918a0399d97cb71a388b8/MyFiles/eclipse-frontend/assets/img/achievement_dummy.png" width="100">
                    <div>
                        <div class="text-lg fw-semibold">
                            Administrator<span class="text-xs fw-semibold text-muted mx-1">•</span><span class="text-xs fw-semibold text-muted">
                                2 users have made this achievement.
                            </span>
                        </div>
                        <div class="text-muted text-sm">
                            Lorem ipsum dolor sit amet, consectetur
                            adipiscing elit.
                        </div>
                    </div>
                </div>
                <div class="section flex-container align-middle gap-3">
                    <img src="file:///home/chronos/u-b99c190e11ee42e371d918a0399d97cb71a388b8/MyFiles/eclipse-frontend/assets/img/achievement_dummy.png" width="100">
                    <div>
                        <div class="text-lg fw-semibold">
                            Administrator<span class="text-xs fw-semibold text-muted mx-1">•</span><span class="text-xs fw-semibold text-muted">
                                2 users have made this achievement.
                            </span>
                        </div>
                        <div class="text-muted text-sm">
                            Lorem ipsum dolor sit amet, consectetur
                            adipiscing elit.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
