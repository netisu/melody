<template>
  <div class="input-group">
    <input
      id="btn-input"
      type="text"
      name="message"
      class="form input-sm"
      placeholder="Type your message here..."
      v-model="newMessage"
      @keyup.enter="sendMessage"
    />
    //Button
    <span class="input-group-btn">
      <button class="btn btn-primary btn-sm" id="btn-chat" @click="sendMessage">
        Send
      </button>
    </span>
  </div>
</template>
<script>
export default {
  props: ["user"],
  data() {
    return {
      newMessage: "",
    };
  },
  methods: {
    sendMessage() {
      this.$emit("messagesent", {
        user: this.user,
        message: this.newMessage,
      });
      this.newMessage = "";
    },
  },
};
</script>
