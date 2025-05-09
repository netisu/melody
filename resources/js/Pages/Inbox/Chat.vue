<script setup lang="ts">
import Navbar from '@/Components/LayoutParts/Navbar.vue'
import Sidebar from '@/Components/LayoutParts/Sidebar.vue'
import { usePage } from '@inertiajs/vue3';
import { route } from "ziggy-js";
import AppHead from "@/Components/AppHead.vue";
import Footer from '@/Components/LayoutParts/Footer.vue';
import { onMounted, ref, watch, nextTick } from 'vue';
import axios from 'axios';
import { PushMessage, sendTypingWhisper } from '@/echo';

const messages = ref([]);
const newMessage = ref("");
const messagesContainer = ref(null);
const isFriendTyping = ref(false);
const isFriendTypingTimer = ref(null);

watch(
    messages,
    () => {
        nextTick(() => {
            messagesContainer.value.scrollTo({
                top: messagesContainer.value.scrollHeight,
                behavior: "smooth",
            });
        });
    },
    { deep: true }
);


const getChats = async () => {
    try {
        const response = await axios.get(route(`api.messaging.fetchMessages`, { id: usePage<any>().props.toUser.id }));
        messages.value = response.data.chats;
    } catch (error) {
        console.error('Error fetching chats:', error);
    }
};


function createChatUpdate(message: string) {
    return {
        message: message,
        // thumbnail: usePage<any>().props.auth.user.headshot,
    };
}

const JSONALERT = ref<{ message: string; type: string } | null>(null);
const sendMessage = () => {
    const messageToSend = newMessage.value.trim();
    if (messageToSend !== "") {
            const newChat = createChatUpdate(messageToSend);
            messages.value.push(newChat);
            axios.post(route(`chat.validate`, { id: usePage<any>().props.toUser.id }), {
                message: messageToSend,
            }).then((response) => {
                const jsonData = response.data;

                JSONALERT.value = jsonData;
                newMessage.value = "";
            }).catch((error) => {
                console.error("Error:", error);
            });
    }
};

const sendTypingEvent = () => {
    sendTypingWhisper(usePage<any>().props.toUser.id, usePage<any>().props.auth.user.id);
};

onMounted(() => {
    getChats();
    PushMessage(usePage<any>().props.toUser.id, usePage<any>().props.auth.user.id, isFriendTyping, isFriendTypingTimer, (message) => {
        messages.value.push(message);
    });
});
</script>

<template>
    <AppHead :pageTitle="usePage<any>().props.toUser.username"
        :description="'Chat with ' + usePage<any>().props.toUser.username + '.'"
        :url="route('my.money.page')" />
    <Navbar />
    <Sidebar :JSONALERT="JSONALERT">
        <div class="cell medium-8">
            <div class="mb-2 text-xl fw-semibold">Chat with {{ usePage<any>().props.toUser.username }}</div>

            <div class="card card-body">
                <div ref="messagesContainer" class="chat-card-container" v-if="messages.length">
                    <template v-for="message in messages" :key="message.id">
                        <div v-if="message.sent_from === usePage<any>().props.auth.user.id" class="chat-sender">
                            <div class="badge badge-info text-wrap">
                                {{ message.message }}
                            </div>
                            <div class="text-muted text-xs">
                                15th Jan 4:37pm
                            </div>
                        </div>
                        <div v-else class="chat-receiver">
                            <div class="badge badge-success"> {{ message.message }}
                            </div>
                            <div class="text-muted text-xs">
                                15th Jan 4:39pm
                            </div>
                        </div>
                    </template>
                </div>
                <div v-else>
                    <div class="gap-3 text-center flex-container flex-dir-column">
                        <i class="text-5xl fas fa-face-sleeping text-muted"></i>
                        <div style="line-height: 16px">
                            <div class="text-xs fw-bold text-muted text-uppercase">
                                No messages
                            </div>
                            <div class="text-xs text-muted fw-semibold">
                                You have no messages with {{ usePage<any>().props.toUser.username }}.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="divider mx-1 my-2"></div>

                <div class="gap-2 align-middle flex-container-lg">
                    <input type="text" v-model="newMessage" @keydown="sendTypingEvent" @keyup.enter="sendMessage"
                        class="mb-2 form" placeholder="Message" />

                    <div class="mb-2 flex-container flex-child-grow">
                        <button @click="sendMessage()" class="btn  btn-info w-100">Send</button>
                    </div>
                </div>
                <small v-if="isFriendTyping" class="text-gray-700">
                    {{ usePage<any>().props.toUser.username }} is typing...
                </small>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
