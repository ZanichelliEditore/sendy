<template>
  <b-modal
    v-model="isOpen"
    hide-footer
    :title="title"
    :hide-header-close="true"
    :no-close-on-esc="true"
    :no-close-on-backdrop="true"
  >
    <div class="d-block text-center">
      <h3>{{this.computedMessage}}</h3>
      <footer class="col-12 m-0" v-if="type !== 'alert'">
        <b-button
          data-cy="modal-alert-btn-success"
          class="col-5 mt-2 p-1"
          variant="outline-success"
          @click="confirmFn"
        >{{confirmBtnLabel}}</b-button>
        <b-button
          data-cy="modal-alert-btn-error"
          class="col-5 mt-2 p-1"
          variant="outline-danger"
          @click="hideModal"
        >{{cancelBtnLabel}}</b-button>
      </footer>
      <footer class="col-12" v-if="type === 'alert'">
        <b-button
          data-cy="modal-btn-success"
          v-if="!isAnError"
          class="col-4 offset-4 mt-5 p-1"
          variant="outline-success"
          @click="hideModal(confirmFn)"
        >{{confirmBtnLabel}}</b-button>
        <b-button
          data-cy="modal-btn-error"
          v-if="!!isAnError"
          class="col-4 offset-4 mt-5 p-1"
          variant="outline-danger"
          @click="hideModal(confirmFn)"
        >{{confirmBtnLabel}}</b-button>
      </footer>
    </div>
  </b-modal>
</template>

<script>
import { ErrorHandler } from "../../ErrorHandler.js";

export default {
  props: {
    type: {
      type: String,
      required: true
    },
    isOpen: {
      type: Boolean,
      required: true
    },
    hideModal: {
      type: Function,
      required: true
    },
    confirmFn: Function,
    confirmBtnLabel: {
      type: String,
      default: "OK"
    },
    cancelBtnLabel: {
      type: String,
      default: "Annulla"
    },
    title: String,
    message: [String, Object, Error],
    isAnError: Boolean
  },
  data() {
    return {
      errorHandler: new ErrorHandler()
    };
  },
  computed: {
    computedMessage: function() {
      this.errorHandler.setResponse(this.message);
      return this.errorHandler.getMessage();
    }
  },

  methods: {}
};
</script>

<style src="./BaseModal.css" />
