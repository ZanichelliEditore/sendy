<template>
  <div
    v-if="currentNotification"
    id="notification-container"
    :class="getBackgroundByType(currentNotification.type) + ' d-flex flex-column position-fixed ml-auto mr-3 col-10 col-md-4 col-lg-3 px-3 py-3 text-white align-items-center ' + ((currentNotification.type === 'ERROR') ? 'animation-error' : 'animation-success')"
  >
    <div class="d-flex w-100 justify-content-between align-items-start">
      <div>
        <h3 v-if="currentNotification.type === 'SUCCESS'" class="text-white">Successo</h3>
        <h3 v-if="currentNotification.type === 'ERROR'" class="text-white">Errore</h3>
        <h3 v-if="currentNotification.type === 'INFO'" class="text-white">Info</h3>
      </div>
      <button
        v-show="currentNotification.type === 'ERROR'"
        data-cy="notify-btn-error"
        type="button"
        class="close text-white"
        aria-label="Close"
        @click="closeNotification"
      >
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <p class="w-100">{{currentNotification.message}}</p>
  </div>
</template>

<script>
import { EventBus } from "../../event-bus";

export default {
  data() {
    return {
      processing: false,
      notifications: [],
      currentNotification: null,
      types: ["SUCCESS", "ERROR", "INFO"]
    };
  },

  created() {
    let vm = this;
    EventBus.$on("newNotification", notification => {
      if (
        notification.hasOwnProperty("message") &&
        notification.hasOwnProperty("type")
      ) {
        notification.message = this.parseMessage(notification.message);
        vm.notifications.push(notification);
      }
    });
  },

  methods: {
    processNotification() {
      let vm = this;

      if (vm.notifications.length && !vm.processing) {
        vm.processing = true;
        vm.currentNotification = vm.notifications[0];

        if (vm.currentNotification.type === "ERROR") {
          return;
        }

        return setTimeout(function() {
          vm.processing = false;
          vm.currentNotification = null;

          setTimeout(function() {
            vm.notifications.shift();
          }, 200);
        }, 3000);
      }
    },

    closeNotification() {
      let vm = this;
      this.processing = false;
      this.currentNotification = null;

      setTimeout(function() {
        vm.notifications.shift();
      }, 200);
    },

    getBackgroundByType(type) {
      switch (type) {
        case "SUCCESS":
          return "bg-success";
        case "ERROR":
          return "bg-danger";
        case "INFO":
          return "bg-info";
        default:
          return "bg-success";
      }
    },

    parseMessage(message) {
      if (message == null) {
        return null;
      }

      if (_.isString(message)) {
        return message;
      }

      if (message.data && message.data.errors) {
        let msg = "";

        for (let k in message.data.errors) {
          msg += message.data.errors[k] + "\n";
        }
        return msg;
      }

      return null;
    }
  },

  watch: {
    notifications() {
      this.processNotification();
    }
  }
};
</script>

<style src="./Notification.css" scoped></style>
