<script setup lang="ts">
import { route } from 'ziggy-js';

import Navbar from "@/Components/LayoutParts/Admin/AdminNav.vue";
import AppHead from "@/Components/AppHead.vue";
</script>

<template>
    <AppHead pageTitle="Admin Dashboard" :description="usePage<any>().props.site.name + ' Administration'"
        :url="route('admin.page')" />
    <Navbar>
        <div style="background-color: #1b1b1b;">
            <div class="navbar-item has-text-white" style="background-color: #1b1b1b;">
                Admin Chat
            </div>
        </div>
        <br />


        <!-- Main Content columns: One for chat window another for profile -->
        <div class="columns">
            <div class="card column is-12">
                <div class="card">
                    <div class="card-header">
                        <p class="card-header-title">
                        <div class="tag is-medium is-info">Aeo</div>&nbsp; * 2m
                        </p>
                    </div>
                    <div class="card-content">
                        <div class="content">updates soon</div>
                    </div>
                </div>


                <div class="flex-item-2">
                    <form>
                        <div class="field has-addons">
                            <div class="control">
                                <input class="input" type="text" placeholder="Type your message...">
                            </div>
                            <div class="control">
                                <button type="submit" class="button">
                                    Send
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </Navbar>
</template>
