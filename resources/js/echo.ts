import Echo from "laravel-echo";
import Pusher from "pusher-js";
import type { Ref } from 'vue';
import axios from 'axios';

interface TypingResponse {
    userID: number;
}

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env?.['VITE_REVERB_APP_KEY'],
    wsHost: import.meta.env?.['VITE_REVERB_HOST'],
    wsPort: import.meta.env?.['VITE_REVERB_PORT'] ?? 80,
    wssPort: import.meta.env?.['VITE_REVERB_PORT'] ?? 443,
    authorizer: (channel) => {
        return {
            authorize: (socketId) => {
                axios.post('/api/broadcasting/auth', {
                    socket_id: socketId,
                    channel_name: channel.name
                })
                .then(response => {
                    console.log(response.data);
                })
                .catch(error => {
                    console.log(error);
                });
            }
        };
    },
    forceTLS: (import.meta.env?.['VITE_REVERB_SCHEME'] ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
export function PushMessage(
    toUserId: number,
    authUserId: number,
    isFriendTyping: Ref<boolean>,
    isFriendTypingTimer: Ref<null>,
    messageHandler: (message: string) => void // Add a callback for message handling
  ) {
      window.Echo.private(`chat.${authUserId}`)
        .listen('MessageSent', (response: { message: string }) => {
          messageHandler(response.message); // Call the provided handler
        });

      window.Echo.private(`chat.${toUserId}`)
        .listenForWhisper('typing', (response: TypingResponse) => {
          isFriendTyping.value = response.userID === toUserId;

          if (isFriendTypingTimer.value) {
            clearTimeout(isFriendTypingTimer.value);
          }

          isFriendTypingTimer.value = setTimeout(() => {
            isFriendTyping.value = false;
          }, 1000);
        });
  }

  // Function to send typing whisper (unchanged)
  export function sendTypingWhisper(toUserId: number, authUserId: number) {
      window.Echo.private(`chat.${toUserId}`).whisper('typing', {
        userID: authUserId,
      });
  }
