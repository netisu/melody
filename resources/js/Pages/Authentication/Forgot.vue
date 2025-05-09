<script setup lang="ts">
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AppHead from '@/Components/AppHead.vue';
import axios from 'axios';
import { route } from 'ziggy-js';


const form = useForm({
   email: '',
});

let currentStep = ref(1);
const totalSteps = 2;

const nextStep = () => {
   if (currentStep.value < totalSteps) {
      currentStep.value++;
   }
};

const isPreviousButtonDisabled = computed(() => {
   return currentStep.value === 1;
});

const previousStep = () => {
   currentStep.value--;
};

const getProgressWidth = () => {
   const progress = (currentStep.value / totalSteps) * 100;
   return `${progress}%`;
};

const getTitle = () => {
   if (currentStep.value === 1) {
      return 'Oh noes! Did you forget your password?';
   } else if (currentStep.value === 2) {
      return 'Sent!';
   }
};

const getStepText = () => {
   if (currentStep.value === 1) {
      return 'Fill in the e-mail asscoiated with your account in the field below, if said e-mail is linked to an account, we will send you a link to reset your password.';
   } else if (currentStep.value === 2) {
      return ' If you do not see the e-mail within 5 minutes, please check your spam folder. If you still do not see it, then please press the "Resend Confirmation Email" button below. (Sorry for the inconvenience!)';
   }
};

const formError = ref('');
const post_data = ref('');

const submit = () => {
   if (!form.email || currentStep.value != totalSteps) {
      formError.value = 'Please fill in all the fields.';
      return;
   }
   axios.get(`/sanctum/csrf-cookie`).then(() => {
      form.post(route(`auth.register.validate`), {
         onFinish: () => form.reset('email'),
      });
   });
};

</script>

<template>
   <AppHead pageTitle="Reset Password"
      :description="'Reset your ' + usePage<any>().props.site.name + ' account password'"
      :url="route('auth.register.page')" />
   <Navbar />
   <Sidebar>
      <div class="cell large-8 medium-10">
         <div class="card">
            <div class="card-body">
               <FlashMessages />
               <div class="progress-bar">
                  <span class="progress" :style="{ width: getProgressWidth() }"></span>
               </div>
               <div class="mx-1 my-3 divider"></div>
               <div class="grid-x grid-margin-x grid-padding-y">
                  <div class="text-center cell medium-3">
                     <img src="/assets/img/earl_placeholder.png" class="show-for-medium" alt="earl" />
                     <img src="/assets/img/earl_placeholder.png" alt="earl" style="max-width: 180px"
                        class="show-for-small-only" />
                  </div>
                  <div class="cell medium-9">
                     <div class="text-2xl fw-semibold">
                        {{ getTitle() }}
                     </div>
                     <div class="gap-1 mb-2 flex-container flex-dir-column">
                        <div class="text-sm text-muted fw-semibold">
                           {{ getStepText() }}
                        </div>
                     </div>
                     <div v-if="currentStep === 1">
                        <div>
                           <div class="text-xs fw-bold text-muted text-uppercase">
                              Email Address
                           </div>
                           <input type="text" class="form" v-model="form.email" placeholder="Email Address" />
                        </div>
                     </div>
                     <div v-if="currentStep === 2">
                        <div>
                           <button class="btn btn-info btn-disabled btn-block">
                              <i class="fas fa-envelopes me-2"></i>Resend Confirmation Email
                           </button>
                        </div>
                     </div>

                     <div class="mx-1 my-3 divider"></div>
                     <div class="flex-container align-justify">
                        <button v-if="currentStep > 1" @click="previousStep" class="px-4 btn btn-danger"
                           :class="{ disabled: isPreviousButtonDisabled }">
                           Previous
                        </button>
                        <button v-if="currentStep === 1" class="px-4 btn btn-success" @click="nextStep"
                           :disabled="form.processing">
                           Send Email
                        </button>
                     </div>
                     <Link class="text-xs fw-bold text-muted text-uppercase" :href="route('auth.login.page')">Already
                     have
                     an
                     account?</Link>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </Sidebar>
   <Footer />
</template>
