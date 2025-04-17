<script setup lang="ts">
import { ref, reactive, computed, watch } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";
import { route } from "momentum-trail"

import AppHead from "@/Components/AppHead.vue";
import Footer from "@/Components/LayoutParts/Footer.vue";
import axios from "axios";
import debounce from "lodash/debounce";
import VLazyImage from "v-lazy-image";
const form = useForm({
    username: "",
    displayname: "",
    birthdate: reactive({
        month: "",
        day: "",
        year: "",
    }),
    starter_item: "",
    email: "",
    password: "",
    password_confirmation: "",
});

defineProps<{
    themes?: Object;
    items?: Object;
    errors?: Object;
}>();

const currentTheme = localStorage.getItem("theme") || null;


let currentStep = ref(1);
const isStepOk = ref(false);

const totalSteps = 6;

const site = computed(() => usePage<any>().props.site);
const SetValue = () => {
    isStepOk.value = true;
};
const nextStep = () => {
    if (currentStep.value < totalSteps) {
        currentStep.value++;
        isStepOk.value = false;
    }
};
const checkStepOne = debounce(async (email) => {
    try {
        await axios.get(`/sanctum/csrf-cookie`);
        const response = await axios.get(route(`api.user.email-validate`, { email: email }));
        if (response.data.error === true && response.data.message) {
            // Set ForumError value here (assuming response.data.json.message contains the error message)
            formError.value = response.data.message;
            isStepOk.value = false;

        } else {
            // Handle successful response (e.g., set isStepOk.value to true)
            formError.value = "";
            isStepOk.value = true;
        }
    } catch (error) {
        // Handle errors during API call or CSRF token retrieval
        console.error("Error checking email :", error);
        // You can display an error message to the user here
    }
}, 1000);

const checkStepTwo = debounce(async (name) => {
    try {
        await axios.get(`/sanctum/csrf-cookie`);
        const response = await axios.get(route(`api.user.name-availability`, { name: name }));
        if (response.data.error === true && response.data.message) {
            // Set ForumError value here (assuming response.data.json.message contains the error message)
            formError.value = response.data.message;
            isStepOk.value = false;

        } else {
            // Handle successful response (e.g., set isStepOk.value to true)
            formError.value = "";
            isStepOk.value = true;
        }
    } catch (error) {
        // Handle errors during API call or CSRF token retrieval
        console.error("Error checking name availability:", error);
        // You can display an error message to the user here
    }
}, 1000);

const checkStepThree = debounce(async (password, confirm) => {
    try {
        await axios.get(`/sanctum/csrf-cookie`);
        const response = await axios.get(route(`api.user.password-validate`, { password: password, password_confirmation: confirm }));
        if (response.data.error === true && response.data.message) {
            // Set ForumError value here (assuming response.data.json.message contains the error message)
            formError.value = response.data.message ?? response.data.errors.password;
            isStepOk.value = false;

        } else {
            // Handle successful response (e.g., set isStepOk.value to true)
            formError.value = "";
            isStepOk.value = true;
        }
    } catch (error) {
        // Handle errors during API call or CSRF token retrieval
        console.error("Error checking password:", error);
        // You can display an error message to the user here
    }
}, 1000);

const isPreviousButtonDisabled = computed(() => {
    return currentStep.value === 1;
});

const previousStep = () => {
    currentStep.value--;
    isStepOk.value = true;
};

const getProgressWidth = () => {
    const progress = (currentStep.value / totalSteps) * 100;
    return `${progress}%`;
};

const getStepText = () => {
    if (currentStep.value === 1) {
        return `My Name is ${usePage<any>().props.tester.username} and I will be guiding you through the sign-up process!`;
    } else if (currentStep.value === 2) {
        return "Great! Now, what should we call you?";
    } else if (currentStep.value === 3) {
        return "Choose your password.";
    } else if (currentStep.value === 4) {
        return "When were you born?";
    } else if (currentStep.value === 5) {
        return "Choose a website theme.";
    } else if (currentStep.value === 6) {
        return 'By clicking the "Sign Me Up!" button below, you agree to our Terms of Service and Privacy Policy.';
    }
};

const getStepDesc = () => {
    if (currentStep.value === 1) {
        return "For starters, what is your email? We need this so we can contact you!";
    } else if (currentStep.value === 2) {
        return `Your username is how ${usePage<any>().props.site.name} players will be able to identify you.`;
    } else if (currentStep.value === 3) {
        return "Your password is the way you will be able to access your account and change your settings, please use a password you do not use anywhere else.";
    } else if (currentStep.value === 5) {
        return "Choose a theme that is comfortable for your viewing experience, this can be changed anytime later from your account settings.";
    } else if (currentStep.value === 6) {
        return "You can review our Terms of Service and/or Privacy Policy below.";
    }
};

const getStepSdesc = () => {
    if (currentStep.value === 3) {
        return "Do not share your password with anybody.";
    } else if (currentStep.value === 4) {
        return "We need this piece of information to ensure your privacy and safety on our platform.";
    }
};

const formError = ref("");

const submit = () => {
    axios.get(`/sanctum/csrf-cookie`).then(() => {
        form.post(route(`auth.register.validate`), {
            onFinish: () => form.reset("password"),
        });
    });
};

var days = reactive(
    [...Array(31).keys()].map((day) => ({
        value: day + 1,
        label: (day + 1).toString(),
    }))
);
const months = [
    { value: 1, label: "January" },
    { value: 2, label: "February" },
    { value: 3, label: "March" },
    { value: 4, label: "April" },
    { value: 5, label: "May" },
    { value: 6, label: "June" },
    { value: 7, label: "July" },
    { value: 8, label: "August" },
    { value: 9, label: "September" },
    { value: 10, label: "October" },
    { value: 11, label: "November" },
    { value: 12, label: "December" },
];
const currentYear = new Date().getFullYear();
const years = [...Array(currentYear - 1919 + 1).keys()]
    .map((year) => ({
        value: year + 1920,
        label: (year + 1920).toString(),
    }))
    .reverse();

function updateDays() {
    const selectedMonthNumber = Number(form.birthdate.month);
    const daysLimit = selectedMonthNumber === 2 ? 28 : 30;

    days = [...Array(daysLimit).keys()].map((day) => ({
        value: day + 1,
        label: (day + 1).toString(),
    }));
}

updateDays(); // Call the function initially to set the days based on the default month

// Watch for changes in the selected month
watch(() => form.birthdate.month, updateDays);
</script>

<template>
    <AppHead pageTitle="Register" :description="'Register to ' + usePage<any>().props.site.name + ''"
        :url="route('auth.register.page')" />
    <Navbar />
    <Sidebar>
        <div class="cell large-8 medium-10">
            <div v-if="formError" class="alert alert-danger mb-2">
                {{ formError }}
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="progress-bar">
                        <span class="progress" :style="{ width: getProgressWidth() }"></span>
                    </div>
                    <div v-if="form.errors.length" class="alert alert-danger mt-2">
                        {{ form.errors }}
                    </div>

                    <div class="mx-1 my-3 divider"></div>
                    <div class="grid-x grid-margin-x grid-padding-y">
                        <div class="text-center cell medium-3">
                            <v-lazy-image :src="usePage<any>().props.tester.avatar" class="show-for-medium"
                                alt="earl" />
                            <v-lazy-image :src="usePage<any>().props.tester.avatar" alt="earl" style="max-width: 180px"
                                class="show-for-small-only" />
                        </div>
                        <div class="cell medium-9">
                            <div class="text-2xl fw-semibold">
                                Welcome to {{ site.name }}!
                            </div>
                            <div class="gap-1 mb-2 flex-container flex-dir-column">
                                <div class="text-sm text-muted fw-semibold">
                                    {{ getStepText() }}
                                </div>
                                <div class="text-sm text-muted fw-semibold">
                                    {{ getStepDesc() }}
                                </div>
                                <div class="text-sm text-muted fw-semibold">
                                    {{ getStepSdesc() }}
                                </div>
                            </div>
                            <form @submit.prevent="submit">
                                <div v-if="currentStep === 1">
                                    <!-- First section content -->
                                    <div class="text-xs fw-bold text-muted text-uppercase">
                                        Email Address
                                    </div>
                                    <input v-model="form.email" @input="checkStepOne(form.email)" type="text"
                                        class="form" placeholder="Email Address..." />
                                </div>
                                <div v-else-if="currentStep === 2">
                                    <!-- Second section content -->
                                    <div class="mb-2">
                                        <div :class="{
                                            'text-danger':
                                                form.errors.username,
                                        }" class="text-xs fw-bold text-danger text-uppercase">
                                            Username
                                        </div>
                                        <input type="text" v-model="form.username" name="username" class="form"
                                            placeholder="@Username..." @input="checkStepTwo(form.username)" />
                                        <div v-if="form.errors.username" class="text-xs text-danger fw-semibold">
                                            {{ form.errors.username }}
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <div :class="{
                                            'text-danger':
                                                form.errors.displayname,
                                        }" class="text-xs fw-bold text-muted text-uppercase">
                                            Display Name
                                        </div>
                                        <input type="text" v-model="form.displayname" name="displayname" class="form"
                                            placeholder="Display Name..." />
                                        <div v-if="form.errors.displayname" class="text-xs text-danger fw-semibold">
                                            {{ form.errors.displayname }}
                                        </div>
                                    </div>
                                </div>

                                <div v-else-if="currentStep === 3">
                                    <!-- Third section content -->
                                    <div class="mb-2">
                                        <div class="text-xs fw-bold text-muted text-uppercase">
                                            Password
                                        </div>
                                        <input class="form" placeholder="Password..." v-model="form.password"
                                            type="password" name="password" />
                                    </div>
                                    <div class="mt-2">
                                        <div class="text-xs fw-bold text-muted text-uppercase">
                                            Confirm Password
                                        </div>
                                        <input class="form" placeholder="Confirm Password..."
                                            v-model="form.password_confirmation"
                                            @input="checkStepThree(form.password, form.password_confirmation)"
                                            type="password" name="password_confirmation" />
                                    </div>
                                </div>
                                <div v-else-if="currentStep === 4">
                                    <!-- Fourth section content -->
                                    <div class="grid-x grid-margin-x grid-padding-y">
                                        <div class="cell medium-4">
                                            <div class="mb-1 text-xs fw-bold text-muted text-uppercase">
                                                Month
                                            </div>
                                            <select v-model="form.birthdate.month" class="form form-select"
                                                name="birth_month" @change="updateDays">
                                                <option value="">
                                                    Select Month
                                                </option>
                                                <option v-for="month in months" :key="month.value" :value="month.value">
                                                    {{ month.label }}
                                                </option>
                                            </select>
                                        </div>
                                        <div class="cell medium-4">
                                            <div class="mb-1 text-xs fw-bold text-muted text-uppercase">
                                                Day
                                            </div>
                                            <select v-model="form.birthdate.day" class="form form-select"
                                                name="birth_day">
                                                <option value="">
                                                    Select Day
                                                </option>
                                                <option v-for="day in days" :key="day.value" :value="day.value">
                                                    {{ day.label }}
                                                </option>
                                            </select>
                                        </div>
                                        <div class="cell medium-4">
                                            <div class="mb-1 text-xs fw-bold text-muted text-uppercase">
                                                Year
                                            </div>
                                            <select v-model="form.birthdate.year" class="form form-select"
                                                name="birth_year" @change="updateDays()">
                                                <option value="">
                                                    Select Year
                                                </option>
                                                <option v-for="year in years" :key="year.value" :value="year.value">
                                                    {{ year.label }}
                                                </option>
                                            </select>
                                        </div>
                                        <button type="button" @click="SetValue" class="btn btn-xs w-100 btn-success">
                                            Set </button>
                                    </div>
                                </div>
                                <div v-else-if="currentStep === 5">
                                    <!-- Fifth section content -->
                                    <div class="grid-x grid-margin-x grid-padding-y">
                                        <div class="cell large-6" v-for="(
                                                theme, index
                                            ) in themes" :key="index">
                                            <div :class="{
                                                active:
                                                    currentTheme ===
                                                    theme.lowercase,
                                            }" class="mb-2 theme-selection squish card card-body card-inner mb-lg-0"
                                                :id="theme.lowercase +
                                                    '-theme-btn'
                                                    " @click="
                                                        setTheme(theme.lowercase)
                                                        ">
                                                <div class="gap-4 align-middle flex-container">
                                                    <div class="selection-circle flex-child-grow show-for-large"></div>
                                                    <div class="gap-1 align-middle flex-container flex-dir-column"
                                                        style="min-width: 0">
                                                        <div :class="'theme-circle ' +
                                                            theme.lowercase
                                                            "></div>
                                                        <div class="text-lg fw-semibold text-truncate">
                                                            {{
                                                                theme.name
                                                            }}
                                                        </div>
                                                        <div
                                                            class="selection-circle flex-child-grow show-for-small hide-for-large">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" @click="SetValue" class="btn btn-xs w-100 btn-success">
                                            Set </button>

                                    </div>
                                </div>

                                <div v-else-if="currentStep === 6">
                                    <!-- Seventh section content -->
                                    <div class="gap-2 flex-container-lg">
                                        <button class="mb-2 btn btn-gray btn-block mb-lg-0">
                                            <i class="fas fa-scroll text-muted me-1"></i>
                                            Terms of Service
                                        </button>
                                        <button class="mb-2 btn btn-gray btn-block mb-lg-0">
                                            <i class="fas fa-user-secret text-muted me-1"></i>
                                            Privacy Policy
                                        </button>
                                    </div>
                                </div>

                                <!-- Add more sections as needed -->
                                <div v-if="currentStep === 1">
                                    <div class="gap-1 mt-2 mb-2 flex-container flex-dir-column">
                                        <div class="text-xs text-muted fw-semibold">
                                            You can skip this step by signing up
                                            with Google or Discord!
                                        </div>
                                    </div>
                                    <div class="gap-2 mb-3 flex-container-lg">
                                        <button class="mb-2 btn btn-gray btn-block mb-lg-0">
                                            <img src="/assets/img/google.png" width="20" alt="google_logo" class="me-1"
                                                style="
                                                    margin-top: -3px;
                                                    filter: drop-shadow(
                                                        0 1px 1px
                                                            rgb(0, 0, 0, 0.2)
                                                    );
                                                " />
                                             Google Sign Up
                                        </button>
                                        <button class="btn btn-discord btn-block">
                                            <i class="fab fa-discord me-1"></i>
                                             Discord Sign Up
                                        </button>
                                    </div>
                                </div>
                                <div class="mx-1 my-3 divider"></div>
                                <div class="flex-container align-justify">
                                    <button type="button" v-if="currentStep > 1" @click="previousStep"
                                        class="px-4 btn btn-danger" :class="{
                                            disabled: isPreviousButtonDisabled,
                                        }">
                                        Previous
                                    </button>
                                    <button type="button" v-if="currentStep !== totalSteps" class="px-4 btn btn-success"
                                        @click="nextStep" :disabled="!isStepOk">
                                        Next
                                    </button>

                                    <button v-if="currentStep === totalSteps" type="submit" class="px-4 btn btn-success"
                                        :disabled="form.processing">
                                        Sign Me Up!
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
<script lang="ts">
export default {
    data() {
        return {
            activeTheme: "",
        };
    },
    mounted() {
        const theme = localStorage.getItem("theme") || "light";
        this.activeTheme = theme;
        this.applyTheme(theme);
        const storedTheme = localStorage.getItem("theme") || null;

        const themeBtn = document.getElementById(theme + "-theme-btn");

        if (themeBtn) {
            if (themeBtn.classList.contains("active")) {
                themeBtn.classList.remove("active");
            } else if (storedTheme === theme || this.activeTheme === theme) {
                themeBtn.classList.add("active");
            }
        }
    },
    methods: {
        capitalized(name: string) {
            const capitalizedFirst = name[0].toUpperCase();
            const rest = name.slice(1);

            return capitalizedFirst + rest;
        },
        applyTheme(theme) {
            var lowercaseTheme = theme.toLowerCase();

            let style = document.getElementById("theme-style");
            const themeBtn = document.getElementById(
                lowercaseTheme + "-theme-btn"
            );

            if (!style) {
                style = document.createElement("link");
                style.id = "theme-style";
                style.rel = "stylesheet";
                document.head.appendChild(style);
            }

            style.href = `/assets/css/themes/variables-${lowercaseTheme}.css`;

            // Save the selected theme in localStorage
            localStorage.setItem("theme", theme);
            const storedTheme = localStorage.getItem("theme") || null;

            if (themeBtn) {
                if (themeBtn.classList.contains("active")) {
                    themeBtn.classList.remove("active");
                } else if (
                    storedTheme === theme ||
                    this.activeTheme === theme
                ) {
                    themeBtn.classList.add("active");
                }
            }
        },
        setTheme(theme) {
            const storedTheme = localStorage.getItem("theme") || null;
            const newtheme = theme;
            if (storedTheme != newtheme) {
                document
                    .getElementById(storedTheme + "-theme-btn")
                    .classList.remove("active");
            }

            this.applyTheme(theme);

            if (storedTheme === newtheme) {
                document
                    .getElementById(theme + "-theme-btn")
                    .classList.add("active");
            }
        },
    },
};
</script>
