<script setup lang="ts">
import { ref, reactive, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";
import { route } from 'ziggy-js';;

import AppHead from "@/Components/AppHead.vue";
import Footer from "@/Components/LayoutParts/Footer.vue";
import axios from "axios";
import VLazyImage from "v-lazy-image";
import * as z from 'zod';
import { toTypedSchema } from '@vee-validate/zod';
import { Field, ErrorMessage, useForm } from 'vee-validate';

import { router } from '@inertiajs/vue3'

const registerFormSchema = toTypedSchema(z.object({
    username: z
        .string()
        .min(3, {
            message: 'Username must be at least 3 characters.',
        })
        .max(20, {
            message: 'Username must not be longer than 20 characters.',
        }),
    displayName: z
        .string()
        .min(3, {
            message: 'Display name must be at least 3 characters.',
        })
        .max(30, {
            message: 'Display name must not be longer than 30 characters.',
        }),
    password: z
        .string()
        .min(8, {
            message: 'Your password must be at least 8 characters.',
        }),
    password_confirmation: z
        .string()
        .min(8, {
            message: 'Password confirmation must be at least 8 characters.',
        }),
    birthdate: z.object({
        month: z.number({
            required_error: 'Please select your birth month.',
        }),
        day: z.number({
            required_error: 'Please select your birth day.',
        }),
        year: z.number({
            required_error: 'Please select your birth year.',
        }),
    }),
    country: z
        .string({
            required_error: 'Please select a country.',
        }),
    email: z
        .string({
            required_error: 'Please enter your email address.',
        })
        .email({ message: 'Please enter a valid email address.' }),
    bio: z
        .string()
        .max(160, { message: 'Bio must not be longer than 160 characters.' })
        .min(2, { message: 'Bio must be at least 2 characters.' })
        .optional(),
}).refine((data) => data.password === data.password_confirmation, {
    message: "Passwords don't match",
    path: ['password_confirmation'],
}));

const generateRandomNumber = (min: number, max: number): number => {
    if (min > max) {
        throw new Error("Minimum value cannot be greater than maximum value.");
    }
    return Math.floor(Math.random() * (max - min + 1)) + min;
};

const placeholderUsername = usePage<any>().props.site.name + generateRandomNumber(1, 999);

const { handleSubmit, values, errors, setErrors } = useForm({
    validationSchema: registerFormSchema,
    initialValues: {
        username: placeholderUsername,
        displayName: placeholderUsername,
        country: 'jp', // fetch this dynamically later.
        bio: 'Greetings! im new to ' + usePage<any>().props.site.name,
        email: '',
        password: '',
        password_confirmation: '',
        birthdate: {
            month: generateRandomNumber(1, 12),
            day: generateRandomNumber(1, 25),
            year: generateRandomNumber(1925, 2025),
        },
        starter_item: '', // not used atm used in the form?
    },
});

const submit = handleSubmit(async (formValues) => {
    try {
        await axios.post(route('auth.register.validate'), formValues);
        router.visit(route('my.dashboard.page'));
    } catch (error: any) {
        if (error.response && error.response.status === 422) {
            setErrors(error.response.data.errors);
            return 'There were errors in your registration.';
        } else {
            return 'An unexpected error occurred during registration.';
            console.error('Registration error:', error);
        }
    }
});

defineProps<{
    themes?: Object;
    items?: Object;
    errors?: Record<string, string>;
}>();

watch(() => usePage<any>().props.errors, (newErrors) => {
    if (newErrors) {
        setErrors(newErrors);
    }
}, { immediate: true });

const currentTheme = localStorage.getItem("theme") || null;
const currentStep = ref(1);
const isStepOk = ref(false);
const totalSteps = 6;

const nextStep = () => {
    if (currentStep.value < totalSteps) {
        currentStep.value++;
    }
};

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
    const selectedMonthNumber = Number(values.birthdate.month);
    const daysLimit = selectedMonthNumber === 2 ? 28 : 30;

    days.length = 0; // Clear the existing array
    days.push(
        ...[...Array(daysLimit).keys()].map((day) => ({
            value: day + 1,
            label: (day + 1).toString(),
        }))
    );
}

// Watch for changes in the selected month
watch(() => values.birthdate.month, updateDays);

// Initialize days based on the initial month value (if any)
if (values.birthdate.month) {
    updateDays();
}
</script>

<template>
    <AppHead pageTitle="Register" :description="'Register to ' + usePage<any>().props.site.name + ''"
        :url="route('auth.register.page')" />
    <Navbar />
    <Sidebar>
        <div class="cell large-8 medium-10">
            <div class="card">
                <div class="card-body">
                    <div class="progress-bar">
                        <span class="progress" :style="{ width: getProgressWidth() }"></span>
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
                                Welcome to {{ usePage<any>().props.site.name }}!
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
                                <div v-show="currentStep === 1">
                                    <!-- First section content -->
                                    <div class="text-xs fw-bold text-muted text-uppercase" :class="{
                                        'text-danger': errors.email,
                                    }">
                                        Email Address
                                    </div>
                                    <Field v-model="values.email" type="email" class="form"
                                        placeholder="Email Address..." name="email" />
                                    <ErrorMessage name="email" class="text-xs text-danger fw-semibold" />
                                </div>
                                <div v-show="currentStep === 2">
                                    <!-- Second section content -->
                                    <div class="mb-2">
                                        <div :class="{
                                            'text-danger':
                                                errors.username,
                                        }" class="text-xs fw-bold text-muted text-uppercase">
                                            Username
                                        </div>
                                        <Field type="text" v-model="values.username" name="username" class="form"
                                            placeholder="@Username..." />
                                        <ErrorMessage name="username" class="text-xs text-danger fw-semibold" />
                                    </div>
                                    <div class="mt-2">
                                        <div :class="{
                                            'text-danger':
                                                errors.displayName,
                                        }" class="text-xs fw-bold text-muted text-uppercase">
                                            Display Name
                                        </div>
                                        <Field type="text" v-model="values.displayName" name="displayName" class="form"
                                            placeholder="Display Name..." />
                                        <ErrorMessage name="displayName" class="text-xs text-danger fw-semibold" />
                                    </div>
                                </div>

                                <div v-show="currentStep === 3">
                                    <!-- Third section content -->
                                    <div class="mb-2">
                                        <div class="text-xs fw-bold text-muted text-uppercase">
                                            Password
                                        </div>
                                        <Field class="form" placeholder="Password..." v-model="values.password"
                                            type="password" name="password" />
                                        <ErrorMessage name="password" class="text-xs text-danger fw-semibold" />

                                    </div>
                                    <div class="mt-2">
                                        <div class="text-xs fw-bold text-muted text-uppercase">
                                            Confirm Password
                                        </div>
                                        <Field class="form" placeholder="Confirm Password..."
                                            v-model="values.password_confirmation" type="password"
                                            name="password_confirmation" />
                                        <ErrorMessage name="password_confirmation"
                                            class="text-xs text-danger fw-semibold" />
                                    </div>
                                </div>
                                <div v-show="currentStep === 4">
                                    <!-- Fourth section content -->
                                    <div class="grid-x grid-margin-x grid-padding-y">
                                        <div class="cell medium-4">
                                            <div class="mb-1 text-xs fw-bold text-muted text-uppercase">
                                                Month
                                            </div>
                                            <Field as="select" v-model="values.birthdate.month" class="form form-select"
                                                name="birthdate.month">
                                                <option value="">
                                                    Select Month
                                                </option>
                                                <option v-for="month in months" :key="month.value" :value="month.value">
                                                    {{ month.label }}
                                                </option>
                                            </Field>
                                            <ErrorMessage name="birthdate.month"
                                                class="text-xs text-danger fw-semibold" />

                                        </div>
                                        <div class="cell medium-4">
                                            <div class="mb-1 text-xs fw-bold text-muted text-uppercase">
                                                Day
                                            </div>
                                            <Field as="select" v-model="values.birthdate.day" class="form form-select"
                                                name="birthdate.day">
                                                <option value="">
                                                    Select Day
                                                </option>
                                                <option v-for="day in days" :key="day.value" :value="day.value">
                                                    {{ day.label }}
                                                </option>
                                            </Field>
                                            <ErrorMessage name="birthdate.day"
                                                class="text-xs text-danger fw-semibold" />

                                        </div>
                                        <div class="cell medium-4">
                                            <div class="mb-1 text-xs fw-bold text-muted text-uppercase">
                                                Year
                                            </div>
                                            <Field as="select" v-model="values.birthdate.year" class="form form-select"
                                                name="birthdate.year">
                                                <option value="">
                                                    Select Year
                                                </option>
                                                <option v-for="year in years" :key="year.value" :value="year.value">
                                                    {{ year.label }}
                                                </option>
                                            </Field>
                                            <ErrorMessage name="birthdate.year"
                                                class="text-xs text-danger fw-semibold" />
                                        </div>

                                    </div>
                                </div>
                                <div v-show="currentStep === 5">
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


                                    </div>
                                </div>

                                <div v-show="currentStep === 6">
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
                                <div v-show="currentStep === 1">
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
                                        class="px-4 btn btn-danger">
                                        Previous
                                    </button>
                                    <button type="button" v-if="currentStep !== totalSteps" class="px-4 btn btn-success"
                                        @click="nextStep">
                                        Next
                                    </button>

                                    <button v-if="currentStep === totalSteps" type="submit" class="px-4 btn btn-success"
                                        :disabled="Object.keys(errors).length > 0">
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
