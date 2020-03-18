<template>
  <div class="mx-5">
    <div class="mt-10 container-fluid">
      <div class="m-5">
        <button type="button" class="btn btn-outline-primary" @click="cleanLog()">Clean LOG</button>
        <button
          type="button"
          class="btn btn-outline-primary"
          @click="cleanAccessToken()"
        >Clean Access Token</button>
      </div>
      <div class="mt-5">
        <div v-if="loadingObject">Caricamento in corso...</div>
        <div v-else-if="!loadingObject && file.length == 0">Non ci sono log</div>
        <div v-else>
          <ul class="list-group">
            <li
              :key="index"
              v-for="(item, index) in file"
              class="list-group-item list-group-item-action"
            >{{item}}</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { EventBus } from "../../event-bus.js";

export default {
  props: {
    url: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      file: [],
      loadingObject: true
    };
  },
  created() {
    let vm = this;
    this.fetchData();
    // EventBus.$on("newNotification", notification => {
    //   if (
    //     notification.hasOwnProperty("message") &&
    //     notification.hasOwnProperty("type")
    //   ) {
    //     notification.message = this.parseMessage(notification.message);
    //     vm.notifications.push(notification);
    //   }
    // });
  },
  methods: {
    fetchData() {
      let vm = this;
      vm.loadingObject = true;
      axios
        .get(vm.url)
        .then(res => {
          if (res.status != 204) {
            vm.file = res.data.contentFile;
          }
          vm.loadingObject = false;
        })
        .catch(function(error) {
          vm.loadingObject = false;
        });
    },
    cleanLog() {
      const vm = this;
      axios
        .get(vm.url + "/clean/log")
        .then(res => {
          vm.fetchData();
          EventBus.$emit("newNotification", {
            message: "File di log cancellato correttamente",
            type: "SUCCESS"
          });
        })
        .catch(function(error) {
          EventBus.$emit("newNotification", {
            message: "Errore nella cancellazione del file di log",
            type: "ERROR"
          });
        });
    },
    cleanAccessToken() {
      axios
        .get(this.url + "/clean/access-token")
        .then(res => {
          const cancelled = res.data.cancelled;
          EventBus.$emit("newNotification", {
            message: "Access token cancellati: " + cancelled,
            type: "SUCCESS"
          });
        })
        .catch(function(error) {
          EventBus.$emit("newNotification", {
            message: "Errore nella cancellazione degli access token",
            type: "ERROR"
          });
        });
    }
  }
};
</script>
<style src="./HandleJobs.css" scoped></style>