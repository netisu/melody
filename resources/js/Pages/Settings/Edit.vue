<script setup lang="ts">
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import LanguageModal from '@/Components/Modal/LanguageModal.vue';
// import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import Upload from '@/Components/Upload.vue';
import { usePage, router } from '@inertiajs/vue3';
import { route } from "ziggy-js";
import { computed, ref } from 'vue';
import type { Ref } from 'vue';
import axios from "axios";
import AppHead from '@/Components/AppHead.vue';
import VLazyImage from 'v-lazy-image';

defineProps<{
    hasVerifiedEmail?: Boolean,
    status?: String,
    categories?: Array<string>,
    themes?: Object,
}>();

function showModal(modalId: string): void {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.toggle("active");
    }
};

const locale_active = usePage().props?.['locale'];
const locales = usePage().props?.['locales'];

const bannerVisibilityEnabled = ref(usePage<any>().props.auth.user.settings.profile_banner_enabled);
const isBannerUploading = ref(false);
const isBannerUploading = ref(false);
const uploadProgress = ref(0);
const uploadError = ref('');
const bannerImageFile = ref(null);

const ActiveCategory: Ref<string> = ref("Profile");

function setActiveCategory(category): void {
    ActiveCategory.value = category;
};

const handleBannerImageChange = (event) => {
    bannerImageFile.value = event.target.files[0];
};

function changeCountry(country): void {
    axios.post(route(`api.settings.changeCountry`, { country: country }))
        .then(response => {
            // Handle the success response here
            console.error('Success:', response);

        })
        .catch(error => {
            // Handle the error response here
            console.error('Error setting your country:', error);
        });
    console.log(country);
};

function uploadProfilePicture(image): void {
    axios.post(route(`api.settings.uploadProfilePicture`, { image: image }))
        .then(response => {
            // Handle the success response here
            console.error('Success:', response);

        })
        .catch(error => {
            // Handle the error response here
            console.error('Error uploading your profile picture:', error);
        });
    console.log(image);
};

const uploadBannerImage = () => {
    if (!bannerImageFile.value) {
        alert('Please select an image to upload.');
        return;
    }

    isBannerUploading.value = true;
    uploadProgress.value = 0;
    uploadError.value = '';

    const formData = new FormData();
    formData.append('image', bannerImageFile.value);

    axios.post(route('api.settings.uploadBanner'), formData, { // Ensure this route is defined
        headers: {
            'Content-Type': 'multipart/form-data',
        },
        onUploadProgress: (progressEvent) => {
            uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
        },
    })
    .then(response => {
        isBannerUploading.value = false;
        uploadProgress.value = 100;
        console.log('Banner uploaded successfully:', response);
        bannerImageFile.value = null; // Clear the selected file
        showModal('banner-modal'); // Close the modal on success, or handle UI update
    })
    .catch(error => {
        isBannerUploading.value = false;
        uploadProgress.value = 0;
        console.error('Error uploading banner:', error);
        uploadError.value = 'Error uploading banner. Please try again.';
    });
};

const enableBannerVisibility = (visible) => {
    axios.post(route('api.settings.bannerVisibility'), { value: visible })
        .then(response => {
            bannerVisibilityEnabled.value = visible;
            console.log('Banner visibility updated:', response);
        })
        .catch(error => {
            console.error('Error changing banner visibility:', error);
            // Revert the toggle state on error
            bannerVisibilityEnabled.value = !visible;
        });
    console.log('Banner visibility:', visible);
};

const currentTheme = localStorage.getItem('theme');


const isVerifiedEmail = computed(() => {
    return usePage<any>().props.hasVerifiedEmail || false;
});
</script>

<template>
    <AppHead pageTitle="Account Settings" description="Change your account Settings."
        :url="route(`user.settings.page`)" />
    <Navbar />
    <Sidebar :alertsEnabled="false"
        :image="usePage<any>().props.auth.user.settings.profile_banner_enabled ? usePage<any>().props.auth.user.settings.profile_banner_url : null"
        :superBanActive="true" :OfficialImageBackground="false">
        <div class="modal" id="email-modal">
            <div class="modal-card modal-card-body modal-card-sm">
                <div class="section-borderless">
                    <div class="gap-2 align-middle flex-container align-justify">
                        <div class="text-lg fw-semibold">Change Email</div>
                        <button @click="showModal('email-modal')" class="btn-circle" data-toggle-modal="#email-modal"
                            style="margin-right: -10px">
                            <i class="fad fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="section-borderless">
                    <div class="mb-2">
                        <div class="text-xs fw-bold text-muted text-uppercase">
                            New Email
                        </div>
                        <input type="text" class="form" placeholder="New Email..." />
                    </div>
                    <div class="mb-2">
                        <div class="text-xs fw-bold text-muted text-uppercase">
                            Password
                        </div>
                        <input type="password" class="form" placeholder="Password..." />
                    </div>
                    <div class="text-xs text-muted fw-semibold">
                        After changing your email address, a confirmation email
                        will be sent to your inbox to confirm your identity.
                    </div>
                </div>
                <div class="flex-wrap gap-2 flex-container justify-content-end section-borderless">
                    <button class="btn btn-secondary" @click="showModal('email-modal')">
                        Cancel
                    </button>
                    <input type="submit" class="btn btn-success" value="Change Email" />
                </div>
            </div>
        </div>
        <div class="modal" id="picture-modal">
            <div class="modal-card modal-card-body modal-card-sm">
                <div class="section-borderless">
                    <div class="gap-2 align-middle flex-container align-justify">
                        <div class="text-lg fw-semibold">Change Banner</div>
                        <button @click="showModal('picture-modal')" class="btn-circle" data-toggle-modal="#email-modal"
                            style="margin-right: -10px">
                            <i class="fad fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="section-borderless">
                    <div class="mb-2">
                        <div class="text-xs fw-bold text-muted text-uppercase">
                            Visibility
                        </div>
                        <button @change="changeCountry($event.target.value)" class="btn btn-success btn-sm w-100">
                            Enable Profile Picture</button>
                    </div>
                    <div class="mb-2">
                        <div class="gap-2 align-middle flex-container-lg">
                            <div class="text-xs fw-bold text-muted text-uppercase">
                                Image
                            </div>
                        </div>
                        <form>

                            <input class="form text-body" type="file">

                        </form>
                    </div>
                    <div class="text-xs text-muted fw-semibold">
                        After changing your email address, a confirmation email
                        will be sent to your inbox to confirm your identity.
                    </div>
                </div>
                <div class="flex-wrap gap-2 flex-container justify-content-end section-borderless">
                    <button class="btn btn-secondary" @click="showModal('picture-modal')">
                        Cancel
                    </button>
                    <input type="submit" class="btn btn-success" value="Change Email" />
                </div>
            </div>
        </div>
        <div class="modal" id="banner-modal">
            <div class="modal-card modal-card-body modal-card-sm">
                <div class="section-borderless">
                    <div class="gap-2 align-middle flex-container align-justify">
                        <div class="text-lg fw-semibold">Change Banner</div>
                        <button @click="showModal('email-modal')" class="btn-circle" data-toggle-modal="#email-modal"
                            style="margin-right: -10px">
                            <i class="fad fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="section-borderless">
                    <div class="mb-1">
                        <div class="text-xs fw-bold text-muted text-uppercase">
                            Visibility
                        </div>
                        <button @change="enableBannerVisibility($event.target.checked)" class="btn btn-success btn-sm w-100"
                        :disabled="isBannerUploading">
                        {{ bannerVisibilityEnabled ? 'Disable Banner' : 'Enable Banner' }}
                    </button>
                    </div>
                    <div class="mb-2">
                        <div class="gap-2 align-middle flex-container-lg">
                        <div class="text-xs fw-bold text-muted text-uppercase">
                            Image
                        </div>
                    </div>
                    <form @submit.prevent="uploadBannerImage">
                        <input class="form text-body" type="file" ref="bannerImageInput"
                            @change="handleBannerImageChange">
                        <button type="submit" class="btn btn-primary btn-sm w-100 mt-2"
                            :disabled="isBannerUploading">
                            <span v-if="isBannerUploading">Uploading...</span>
                            <span v-else>Upload Banner</span>
                        </button>
                        <div v-if="uploadProgress > 0 && uploadProgress < 100" class="mt-2">
                            Uploading: {{ uploadProgress }}%
                        </div>
                        <div v-if="uploadError" class="text-xs text-danger mt-2">
                            {{ uploadError }}
                        </div>
                    </form>
                    </div>
                     <div class="text-xs text-muted fw-semibold">
                    Only PNG and JPG files are allowed. Maximum size: 2MB.
                </div>
                </div>
                <div class="flex-wrap gap-2 flex-container justify-content-end section-borderless">
                <button class="btn btn-secondary" @click="showModal('banner-modal')" :disabled="isBannerUploading">
                    Cancel
                </button>
                <button type="button" class="btn btn-success" @click="submitBanner" :disabled="isBannerUploading || !bannerImageFile">
                    Change Banner
                </button>
                </div>
            </div>
        </div>
        <div class="modal" id="username-modal">
            <div class="modal-card modal-card-body modal-card-sm">
                <div class="section-borderless">
                    <div class="gap-2 align-middle flex-container align-justify">
                        <div class="text-lg fw-semibold">Change Username</div>
                        <button @click="showModal('username-modal')" class="btn-circle"
                            data-toggle-modal="#username-modal" style="margin-right: -10px">
                            <i class="fad fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="section-borderless">
                    <div class="mb-2">
                        <div class="text-xs fw-bold text-danger text-uppercase">
                            Username
                        </div>
                        <input type="text" class="form" placeholder="New Username..." />
                        <div class="text-xs text-danger fw-semibold">
                            This username is already taken. Try @Nabrious123
                            instead.
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="text-xs fw-bold text-muted text-uppercase">
                            Password
                        </div>
                        <input type="password" class="form" placeholder="Password..." />
                    </div>
                    <div class="text-xs text-muted fw-semibold">
                        Changing your username costs
                        <span class="mx-1 text-success">
                            <i class="fad fa-money-bill-1-wave"></i>
                            {{ usePage<any>().props.site.price.username }} Bucks
                        </span>
                    </div>
                </div>
                <div class="flex-wrap gap-2 flex-container justify-content-end section-borderless">
                    <button class="btn btn-secondary" @click="showModal('username-modal')">
                        Cancel
                    </button>
                    <input type="submit" class="btn btn-success" value="Change Username" />
                </div>
            </div>
        </div>
        <div class="modal" id="displayname-modal">
            <div class="modal-card modal-card-body modal-card-sm">
                <div class="section-borderless">
                    <div class="gap-2 align-middle flex-container align-justify">
                        <div class="text-lg fw-semibold">
                            Change Display Name
                        </div>
                        <button class="btn-circle" @click="showModal('displayname-modal')" style="margin-right: -10px">
                            <i class="fad fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="section-borderless">
                    <div class="text-xs fw-bold text-muted text-uppercase">
                        New Display Name
                    </div>
                    <input type="text" class="mb-2 form" placeholder="New Display Name..." />
                    <div class="text-xs fw-bold text-muted text-uppercase">
                        Password
                    </div>
                    <input type="password" class="mb-2 form" placeholder="Password..." />
                    <div class="text-xs text-muted fw-semibold">
                        Changing your your display name is free but can only be
                        done once every two weeks.
                    </div>
                </div>
                <div class="flex-wrap gap-2 flex-container justify-content-end section-borderless">
                    <button class="btn btn-secondary" @click="showModal('displayname-modal')">
                        Cancel
                    </button>
                    <input type="submit" class="btn btn-success" value="Change Display Name" />
                </div>
            </div>
        </div>
        <div class="cell medium-12">

            <div class="pb-4">
                <h3 class="text-lg font-medium">
                    <div class=" text-xl fw-semibold">Settings</div>
                </h3>
                <p class="text-sm text-muted">
                    Manage your account settings and set e-mail preferences.
                </p>
            </div>
            <div class="divider mx-1 my-1 mb-2" />
        </div>

        <div class="cell medium-3">
            <ul class="tabs flex-dir-column">
                <li class="tab-item" v-for="(categoryData, categoryName) in categories">
                    <a href="#" @click="setActiveCategory(categoryName)"
                        :class="{ active: categoryName === ActiveCategory }" class="tab-link squish">
                        <i :class="categoryData.icon"></i>
                        {{ capitalized(categoryName) }}
                    </a>
                </li>
            </ul>
        </div>
        <div class="cell medium-9">
            <template v-for="(categoryData, categoryName) in categories" :key="categoryName">
                <div v-if="categoryName === ActiveCategory">
                    <div class="pb-4">
                        <h3 class="text-lg fw-semibold">
                            <i :class="categoryData.icon"></i>

                            {{ capitalized(categoryName) }}
                        </h3>
                        <p class="text-sm text-muted">
                            {{ categoryData.description }} </p>
                    </div>
                    <div class="divider mx-1 my-1 mb-2" />

                </div>
            </template>
            <div v-show="ActiveCategory === 'Profile'">
                <div class="gap-4 align-middle flex-container" style="z-index: 1; position: relative">
                    <v-lazy-image :style="usePage<any>().props.auth.user.settings.calling_card_enabled
                        ? {
                            margin: '0',
                            'background-image':
                                'url(' +
                                usePage<any>().props.auth.user.settings.calling_card_url +
                                ')',
                            'background-repeat': 'no-repeat',
                            'background-size': 'cover',
                            'box-shadow':
                                'inset 0 0 0 100vw rgba(var(--section-bg-rgb), 0.5)!important',
                        }
                        : null
                        " :src="usePage<any>().props.auth.user.headshot" width="100" class="space-image" alt="Headshot"
                        src-placeholder="assets/img/space-error.png" />

                    <div class="text-start">
                        <div class="text-xl" style="line-height: 16px">
                            <div class="mb-1 flex-container align-middle gap-1 fw-semibold">
                                {{ usePage<any>().props.auth.user.display_name }}
                                    <i v-if="usePage<any>().props.auth.user.staff"
                                        :content="usePage<any>().props.auth.user.staff.Position"
                                        v-tippy="{ placement: 'bottom' }" class="fad fa-gavel text-danger"></i>
                                    <i v-else-if="usePage<any>().props.auth.user.settings.beta_tester"
                                        class="fad fa-hard-hat text-success"></i>
                                    <v-lazy-image
                                        :src="'/assets/img/flags/' + usePage<any>().props.auth.user.settings.country_code + '.svg'"
                                        alt="Country Flag" style="width: auto;height: 20px;"
                                        src-placeholder="/assets/img/flags/other/pirate.svg" />
                            </div>
                            <div class="text-sm text-muted fw-semibold">
                                Profile Preview
                            </div>
                        </div>
                        <button @click="showModal('picture-modal')" class="text-lg text-success squish"
                            content="Change Profile Picture" v-tippy="{ placement: 'bottom' }">
                            <i class="fad fa-user"></i>
                        </button>
                        <button class="text-lg text-warning squish" content="Change Calling Card"
                            v-tippy="{ placement: 'bottom' }">
                            <i class="fad fa-cards"></i>
                        </button>
                        <button @click="showModal('banner-modal')" class="text-lg text-info squish"
                            content="Change Banner" v-tippy="{ placement: 'bottom' }">
                            <i class="fad fa-pen"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-2 gap-4 align-middle flex-container align-justify">
                    <div class="mb-2 text-xl fw-semibold">Profile</div>
                    <div class="gap-2 align-middle flex-container-lg">
                        <select @change="changeCountry($event.target.value)"
                            class="mb-2 form form-xs form-select form-has-section-color">
                            <option v-for="country in usePage<any>().props.countries" :key="country.code"
                                :value="country.code">
                                {{ country.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="section-borderless">

                    <div class="card card-body">
                        <div class="mb-2 text-xl fw-semibold">
                            Account Information
                        </div>
                        <div class="section-borderless">
                            <div class="grid-x grid-margin-x">
                                <div class="mb-3 cell medium-6">
                                    <div
                                        class="gap-2 align-middle card card-body card-inner flex-container align-justify h-100">
                                        <div class="min-w-0">
                                            <div class="text-xs  fw-bold text-muted text-uppercase">
                                                User ID
                                            </div>
                                            <div class="text-truncate fw-semibold">
                                                {{ usePage<any>().props.auth.user.id }}
                                            </div>
                                        </div>
                                        <DeleteUserForm />
                                    </div>
                                </div>
                                <div class="mb-3 cell medium-6">
                                    <div
                                        class="gap-2 align-middle card card-body card-inner flex-container align-justify">
                                        <div class="min-w-0">
                                            <div class="text-xs  fw-bold text-muted text-uppercase">
                                                Username
                                            </div>
                                            <div class="text-truncate fw-semibold">
                                                {{ '@' + usePage<any>().props.auth.user.username }}
                                            </div>
                                        </div>
                                        <button class="btn btn-info btn-circle" @click="showModal('username-modal')">
                                            <i class="fad fa-pencil"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-3 cell medium-6">
                                    <div
                                        class="gap-2 align-middle card card-body card-inner flex-container align-justify">
                                        <div class="min-w-0">
                                            <div class="text-xs  fw-bold text-muted text-uppercase">
                                                Display Name
                                            </div>
                                            <div class="text-truncate fw-semibold">
                                                {{ usePage<any>().props.auth.user.display_name }}
                                            </div>
                                        </div>
                                        <button class="btn btn-info btn-circle" @click="showModal('displayname-modal')">
                                            <i class="fad fa-pencil"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-3 cell medium-6">
                                    <div
                                        class="gap-2 align-middle card card-body card-inner flex-container align-justify">
                                        <div class="min-w-0">
                                            <div class="text-xs fw-bold text-muted text-uppercase">
                                                Email Address
                                                <span style="font-size: 10px"
                                                    :class="{ 'text-success': isVerifiedEmail, 'text-danger': !isVerifiedEmail }"
                                                    class="ms-2">
                                                    <i class="fad me-1"
                                                        :class="{ 'fa-check': isVerifiedEmail, 'fa-times': !isVerifiedEmail }"></i>
                                                    {{ isVerifiedEmail ? 'Verified' : 'Unverified' }}
                                                </span>
                                            </div>
                                            <div class="text-truncate fw-semibold">
                                                {{ usePage<any>().props.auth.user.email }}
                                            </div>
                                        </div>
                                        <button class="btn btn-info btn-circle" @click="showModal('email-modal')">
                                            <i class="fad fa-pen"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-3 cell medium-6">
                                    <div
                                        class="gap-2 align-middle card card-body card-inner flex-container align-justify">
                                        <div class="min-w-0">
                                            <div class="text-xs text-truncate fw-bold text-muted text-uppercase">
                                                Date of Birth
                                            </div>
                                            <div class="text-truncate fw-semibold">
                                                {{ usePage<any>().props.auth.user.birthdate }}
                                            </div>
                                            <div class="text-xs fw-semibold text-muted">
                                                If you want to change your
                                                date of birth,
                                                <a href="#">contact support</a>.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-xs fw-bold text-muted text-uppercase">
                                About You
                            </div>
                            <div class="mb-3 position-relative">
                                <form>
                                    <textarea class="form form-has-button pe-5" rows="5">{{ usePage<any>
                                        ().props.auth.user.about_me
                                    }}</textarea>
                                    <input type="submit" class="btn btn-success btn-sm" value="Update" style="
                                            position: absolute;
                                            bottom: 10px;
                                            right: 10px;
                                        " />
                                </form>
                            </div>
                            <div class="text-xs fw-bold text-muted text-uppercase">
                                Forum Signature
                            </div>
                            <div class="gap-2 align-middle flex-container">
                                <input type="text" class="form form-sm btn-sm"
                                    :value="usePage<any>().props.auth.user.Signature" />
                                <input type="submit" class="btn btn-success btn-sm" value="Update" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-show="ActiveCategory === 'Account'">
                <div class="section-borderless">
                    <div class="card card-body">
                        <div class="mb-2 text-xl fw-semibold">Security</div>
                        <div class="cell medium-12">
                            <UpdatePasswordForm />
                        </div>
                    </div>
                </div>
                <div class="section-borderless">
                    <div class="card card-body">
                        <div class="mb-2 text-xl fw-semibold">Other</div>
                        <div class="mb-3 card card-inner card-body">
                            <div class="mb-2 text-xl fw-semibold">
                                Privacy
                            </div>
                            <div class="mb-2">
                                <div class="text-xs fw-bold text-muted text-uppercase">
                                    Who Can view My Profile
                                </div>
                                <select class="form form-select form-has-section-color">
                                    <option value="1">Everyone</option>
                                    <option value="2">No One</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <div class="text-xs fw-bold text-muted text-uppercase">
                                    Who Can See My Posts?
                                </div>
                                <select class="form form-select form-has-section-color">
                                    <option value="1">Everyone</option>
                                    <option value="2">
                                        Followers Only
                                    </option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <div class="text-xs fw-bold text-muted text-uppercase">
                                    Who Can Send Me Messages
                                </div>
                                <select class="form form-select form-has-section-color">
                                    <option value="1">Everyone</option>
                                    <option value="2">
                                        Followers Only
                                    </option>
                                    <option value="3">No One</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <div class="text-xs fw-bold text-muted text-uppercase">
                                    Who Can Send Me Trade Requests
                                </div>
                                <select class="form form-select form-has-section-color">
                                    <option value="1">Everyone</option>
                                    <option value="2">
                                        Followers Only
                                    </option>
                                    <option value="3">No One</option>
                                </select>
                            </div>
                            <button class="btn btn-success">
                                Save Settings
                            </button>
                        </div>
                        <div class="card card-inner card-body">
                            <div class="mb-2 text-xl fw-semibold">
                                Blocked Players
                            </div>
                            <div class="text-xs fw-bold text-muted text-uppercase">
                                Player Username
                            </div>
                            <div class="gap-2 mb-2 flex-container">
                                <input type="text" class="form form-has-section-color"
                                    placeholder="Player Username..." />
                                <button class="btn btn-danger">
                                    Block
                                </button>
                            </div>
                            <div class="card card-body">
                                <div class="gap-3 text-center flex-container flex-dir-column">
                                    <i class="text-5xl fad fa-user-slash text-muted"></i>
                                    <div style="line-height: 16px">
                                        <div class="text-xs fw-bold text-muted text-uppercase">
                                            No Blocked Players
                                        </div>
                                        <div class="text-xs text-muted fw-semibold">
                                            Yahoo! You have not blocked any
                                            players.
                                        </div>
                                    </div>
                                </div>
                                <!--
                    <div class="section">
                    <div class="align-middle flex-container align-justify">
                      <a href="#" class="gap-2 align-middle flex-container">
                        <img
                          src="/assets/img/dummy_headshot.png"
                          class="headshot"
                          width="50"
                        />
                        <div style="line-height: 18px">
                          <div class="text-body fw-semibold">Nabrious</div>
                          <div class="text-xs fw-semibold text-muted">
                            @Nabrious
                          </div>
                        </div>
                      </a>
                      <button class="btn btn-info btn-sm">Unblock</button>
                    </div>
                  </div>
                  <div class="section">
                    <div class="align-middle flex-container align-justify">
                      <a href="#" class="gap-2 align-middle flex-container">
                        <img
                          src="/assets/img/dummy_headshot.png"
                          class="headshot"
                          width="50"
                        />
                        <div style="line-height: 18px">
                          <div class="text-body fw-semibold">Nabrious</div>
                          <div class="text-xs fw-semibold text-muted">
                            @Nabrious
                          </div>
                        </div>
                      </a>
                      <button class="btn btn-info btn-sm">Unblock</button>
                    </div>
                  </div>
                  <div class="section">
                    <div class="align-middle flex-container align-justify">
                      <a href="#" class="gap-2 align-middle flex-container">
                        <img
                          src="/assets/img/dummy_headshot.png"
                          class="headshot"
                          width="50"
                        />
                        <div style="line-height: 18px">
                          <div class="text-body fw-semibold">Nabrious</div>
                          <div class="text-xs fw-semibold text-muted">
                            @Nabrious
                          </div>
                        </div>
                      </a>
                      <button class="btn btn-info btn-sm">Unblock</button>
                    </div>
                  </div>
                  -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-show="ActiveCategory === 'Appearance'">
                <div class="section-borderless">
                    <div class="card card-body mb-3">
                        <div class="mb-2 text-xl fw-semibold">
                            Website Theme
                        </div>

                        <div id="theme-switcher-container" class="grid-x grid-margin-x grid-padding-y">
                            <div v-for="(theme, index) in themes" class="cell large-6" :key="index">
                                <!-- Move v-if inside the loop -->
                                <div :class="{ active: currentTheme === theme.lowercase }"
                                    class="mb-2 theme-selection squish card card-body card-inner mb-lg-0"
                                    :id="theme.lowercase + '-theme-btn'" @click="setTheme(theme.lowercase)">
                                    <div class="gap-4 align-middle flex-container">
                                        <div class="selection-circle flex-child-grow show-for-large"></div>
                                        <div class="gap-1 align-middle flex-container flex-dir-column"
                                            style="min-width: 0">
                                            <div :class="'theme-circle ' + theme.lowercase"></div>
                                            <div class="text-lg fw-semibold text-truncate">
                                                {{ theme.name }} Theme
                                            </div>
                                            <div class="selection-circle flex-child-grow show-for-small hide-for-large">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card card-body">
                        <div class="mb-2 text-xl fw-semibold">
                           Language
                        </div>

                        <div id="theme-switcher-container" class="grid-x grid-margin-x grid-padding-y">
                            <div v-for="(name, locale) in locales" :key="locale" class="cell large-6">
                            <div @click="setLocale(locale)" :class="{ 'language-active': locale_active === locale }"
                                class="squish card card-body card-inner mb-lg-0" style="cursor: pointer;">
                                <div class="align-middle">
                                    <div class="gap-2 align-start flex-container" style="min-width: 0">
                                        <div class="selection-circle language"><i
                                                :class="{ 'fa-duotone fa-check': locale_active === locale }"></i></div>
                                        <div class="fw-semibold text-language text-truncate">
                                            {{ name }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-show="ActiveCategory === 'Billing'">
                <div class="mb-2 align-middle flex-container align-justify">
                    <a href="#" class="btn btn-upgrade btn-sm"><i class="fad fa-rocket-launch me-2"></i>Upgrade</a>
                </div>
                <div class="mb-3 card card-body">
                    <div class="mb-2 text-xl fw-semibold">
                        Active Membership
                    </div>
                    <div class="card card-inner card-body">
                        <div class="gap-3 text-center flex-container flex-dir-column">
                            <i class="text-5xl fad fa-rocket-launch text-muted"></i>
                            <div style="line-height: 16px">
                                <div class="text-xs fw-bold text-muted text-uppercase">
                                    No Active Membership
                                </div>
                                <div class="text-xs text-muted fw-semibold">
                                    You currently do not have any active
                                    membership to manage.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card card-body">
                    <div class="mb-2 text-xl fw-semibold">
                        Previous Purchases
                    </div>
                    <!--
              <div class="table">
              <div class="table-header">
                <div class="align-middle grid-x flex-nowrap">
                  <div class="cell medium-4">Product</div>
                  <div class="cell medium-4">Price</div>
                  <div class="cell medium-4">Payment Method</div>
                </div>
              </div>
              <div class="table-body">
                <div class="align-middle grid-x flex-nowrap">
                  <div class="cell medium-4">
                    <div class="gap-2 align-middle flex-container text-warning">
                      <i
                        class="text-xl text-center fad fa-coins"
                        style="width: 30px"
                      ></i>
                      <div class="fw-semibold">5000 Coins</div>
                    </div>
                  </div>
                  <div class="cell medium-4">
                    <div class="fw-semibold">
                      <span class="text-muted">$</span> 4.99
                    </div>
                  </div>
                  <div class="cell medium-4">
                    <div class="fw-semibold">
                      <i class="fab fa-paypal text-muted me-2"></i>PayPal
                    </div>
                  </div>
                </div>
              </div>
              <div class="table-body table-body-darker">
                <div class="align-middle grid-x flex-nowrap">
                  <div class="cell medium-4">
                    <div class="gap-2 align-middle flex-container text-success">
                      <i
                        class="text-xl text-center fad fa-money-bill-1-wave"
                        style="width: 30px"
                      ></i>
                      <div class="fw-semibold">1000 Bucks</div>
                    </div>
                  </div>
                  <div class="cell medium-4">
                    <div class="fw-semibold">
                      <span class="text-muted">$</span> 9.99
                    </div>
                  </div>
                  <div class="cell medium-4">
                    <div class="fw-semibold">
                      <i class="fad fa-credit-card text-muted me-2"></i>Credit
                      Card
                    </div>
                  </div>
                </div>
              </div>
              <div class="table-body">
                <div class="align-middle grid-x flex-nowrap">
                  <div class="cell medium-4">
                    <div
                      class="gap-2 align-middle flex-container text-membership"
                    >
                      <img src="/assets/img/membership_dummy.png" width="30" />
                      <div class="fw-semibold">Membership</div>
                    </div>
                  </div>
                  <div class="cell medium-4">
                    <div class="gap-2 align-middle flex-container text-success">
                      <i class="text-center fad fa-money-bill-1-wave"></i>
                      <div class="fw-semibold">500 Bucks</div>
                    </div>
                  </div>
                  <div class="cell medium-4">
                    <div class="fw-semibold">
                      <i class="fad fa-money-bill-1-wave text-muted me-2"></i
                      >Bucks
                    </div>
                  </div>
                </div>
              </div>
              <div class="table-body table-body-darker">
                <div class="align-middle grid-x flex-nowrap">
                  <div class="cell medium-4">
                    <div
                      class="gap-2 align-middle flex-container text-membership"
                    >
                      <img src="/assets/img/membership_dummy.png" width="30" />
                      <div class="fw-semibold">Membership</div>
                    </div>
                  </div>
                  <div class="cell medium-4">
                    <div class="fw-semibold">
                      <span class="text-muted">$</span> 9.99
                    </div>
                  </div>
                  <div class="cell medium-4">
                    <div class="fw-semibold">
                      <i class="fab fa-bitcoin text-muted me-2"></i>Crypto
                    </div>
                  </div>
                </div>
              </div>
            </div>
            -->

                    <div class="card card-inner card-body">
                        <div class="gap-3 text-center flex-container flex-dir-column">
                            <i class="text-5xl fad fa-envelope-open-text text-muted"></i>
                            <div style="line-height: 16px">
                                <div class="text-xs fw-bold text-muted text-uppercase">
                                    No Previous Purchases
                                </div>
                                <div class="text-xs text-muted fw-semibold">
                                    You have not made any purchases.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>

<script lang="ts">
let theme;

export default {
    data() {
        return {
            activeTheme: '',
        };
    },
    mounted() {
        if (typeof localStorage !== 'undefined') {
            theme = localStorage.getItem('theme') || 'light';
        } else if (typeof sessionStorage !== 'undefined') {
            // Fallback to sessionStorage
            theme = sessionStorage.getItem('theme') || 'light';
        } else {
            // If neither localStorage nor sessionStorage is supported
            console.log('Web Storage is not supported in this environment.');
            theme = 'light';
        }

        this.activeTheme = theme;
        this.applyTheme(theme);
        const storedTheme = localStorage.getItem('theme');

        const themeBtn = document.getElementById(theme + "-theme-btn");

        if (themeBtn) {
            if (themeBtn.classList.contains('active')) {
                themeBtn.classList.remove('active');
            } else if (storedTheme === theme || this.activeTheme === theme) {
                themeBtn.classList.add('active');
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

            let style = document.getElementById('theme-style');
            const themeBtn = document.getElementById(lowercaseTheme + "-theme-btn");

            if (!style) {
                style = document.createElement('link');
                style.id = 'theme-style';
                style.rel = 'stylesheet';
                document.head.appendChild(style);
            }

            style.href = `/assets/css/themes/variables-${lowercaseTheme}.css`;

            // Save the selected theme in Storage
            if (typeof localStorage !== 'undefined') {
                localStorage.setItem('theme', theme);
            } else if (typeof sessionStorage !== 'undefined') {
                // Fallback to sessionStorage if localStorage is not supported
                sessionStorage.setItem('theme', theme);
            } else {
                // If neither localStorage nor sessionStorage is supported
                console.log('Web Storage is not supported in this environment.');
            }

            const storedTheme = typeof localStorage !== 'undefined'
                ? localStorage.getItem('theme')
                : typeof sessionStorage !== 'undefined'
                    ? sessionStorage.getItem('theme')
                    : null;

            if (storedTheme === null) {
                console.log('Web Storage is not supported in this environment.');
            }

            if (themeBtn) {
                if (themeBtn.classList.contains('active')) {
                    themeBtn.classList.remove('active');
                } else if (storedTheme === theme || this.activeTheme === theme) {
                    themeBtn.classList.add('active');
                }
            }
        },
        setLocale(locale) {
            router.get(route(`auth.language`, { language: locale }))
        },
        setTheme(theme) {

            const storedTheme = localStorage.getItem('theme') || 'light';
            const newtheme = theme || 'light';

            if (storedTheme != newtheme) {
                document.getElementById(storedTheme + "-theme-btn").classList.remove('active');
            }

            this.applyTheme(theme);

            if (storedTheme === newtheme) {
                document.getElementById(theme + "-theme-btn").classList.add('active');
            }

        },
    },
};
</script>
