<template>
  <div class="row">
    <div class="col-12 pt-3 pb-5">
      <div
        v-if="this.buttons"
        class="d-flex justify-content-between align-items-center mb-2"
        style="z-index: 1"
      >
        <h2 class="d-inline-flex align-items-center m-0">Failed Jobs</h2>
        <div>
          <button
            id="retry-button"
            @click="retryAllJobs()"
            class="ml-2 btn btn-success"
            :disabled="objects.length == 0"
          >
            Riprova tutti
            <i class="fa fa-recycle"></i>
          </button>
          <button
            id="delete-button"
            @click="showDeleteAll()"
            class="ml-1 btn btn-danger"
            :disabled="objects.length == 0"
          >
            Cancella tutti
            <i class="fa fa-trash-o"></i>
          </button>
        </div>
        <div class="d-flex justify-content-end">
          <div>
            <form class="form-inline mr-2">
              <input
                id="inputQuery"
                type="text"
                class="form-control"
                v-model="query.q"
                placeholder="Cerca"
                @input="search"
              />
            </form>
          </div>
          <div>
            <select
              class="form-control"
              id="inputLimitPage"
              @change="changeElementsPerPage()"
              v-model="query.limit"
            >
              <option>10</option>
              <option>15</option>
              <option>20</option>
            </select>
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead class="table-dark">
            <tr>
              <th
                v-for="(column, index) in columns"
                :class="{
                  pointer: column.orderby,
                  small_column: column.dimension == 'small',
                  medium_column: column.dimension == 'medium',
                  large_column: column.dimension == 'large',
                }"
                :key="index"
                data-sortable="true"
                scope="col"
                @click="column.orderby && orderBy(column.orderby)"
              >
                {{ column.label }}
                <i
                  :class="{
                    'fa fa-fw fa-sort-desc':
                      column.orderby &&
                      column.orderby === query.orderBy &&
                      query.order === 'DESC',
                    'fa fa-fw fa-sort-asc':
                      column.orderby &&
                      column.orderby === query.orderBy &&
                      query.order === 'ASC',
                    'fa fa-fw fa-sort':
                      column.orderby && column.orderby !== query.orderBy,
                  }"
                ></i>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loadingObject">
              <td :colspan="columns.length">Caricamento in corso</td>
            </tr>
            <tr v-if="!loadingObject && objects.length === 0">
              <td :colspan="columns.length">Nessun elemento trovato</td>
            </tr>
            <tr
              v-if="!loadingObject"
              :class="{ cell: detail }"
              v-for="object in objects"
              :key="object.id"
              @click="showObject(object.id)"
            >
              <td
                v-for="(column, index) in columns"
                :class="{
                  'text-center':
                    column.type === 'alert' || column.type === 'status-badge',
                  'text-truncate': column.type === 'text',
                }"
                scope="row"
                :key="index"
              >
                <span
                  v-if="column.type == 'text'"
                  :title="object[column.field]"
                  >{{ object[column.field] }}</span
                >
                <status-badge
                  v-if="column.type === 'status-badge'"
                  :status="object[column.field]"
                ></status-badge>
                <span
                  class="col-md-1"
                  v-if="column.type == 'alert' && object[column.field]"
                >
                  <i
                    class="fa fa-exclamation-triangle fa-2x"
                    aria-hidden="true"
                  ></i>
                </span>
                <button
                  v-if="column.type === 'buttonRetry'"
                  id="retry-button"
                  @click="retryJob(object.id)"
                  class="btn btn-success"
                >
                  <i class="fa fa-recycle"></i>
                </button>
                <button
                  v-if="column.type === 'buttonDelete'"
                  id="delete-button"
                  @click="showDelete(object.id)"
                  class="btn btn-danger"
                >
                  <i class="fa fa-trash-o"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="objects.length > 0" class="d-flex justify-content-between">
        <div>
          {{ this.title }} dal
          {{ (pagination.currentPage - 1) * query.limit + 1 }} al
          {{ (pagination.currentPage - 1) * query.limit + objects.length }}
        </div>
        <nav aria-label="Page navigation example">
          <ul class="pagination">
            <li
              :class="[{ disabled: !pagination.prevPageUrl }]"
              class="page-item"
            >
              <a
                class="page-link"
                href="#"
                @click.stop.prevent="changePage(--pagination.currentPage)"
                aria-label="Previous"
              >
                <span aria-hidden="true">&laquo;</span>
                <span class="sr-only">Precedente</span>
              </a>
            </li>
            <li
              v-for="ele in pagination.pagesToRender"
              :key="ele"
              class="page-item"
              :class="{
                'font-weight-bold disabled': ele === pagination.currentPage,
              }"
            >
              <a
                class="page-link"
                href="#"
                @click.stop.prevent="changePage(ele)"
                >{{ ele }}</a
              >
            </li>
            <li
              :class="[{ disabled: !pagination.nextPageUrl }]"
              class="page-item"
            >
              <a
                class="page-link"
                href="#"
                @click.stop.prevent="changePage(++pagination.currentPage)"
                aria-label="Next"
              >
                <span aria-hidden="true">&raquo;</span>
                <span class="sr-only">Successiva</span>
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
    <!-- Modal Cancella job -->
    <base-modal
      :type="'confirmModal'"
      :isOpen="showDeleteModal"
      :hideModal="hideDeleteModal"
      :confirmFn="deleteJob"
      :title="'Elimina job'"
      :confirmBtnLabel="'SI'"
      :cancelBtnLabel="'NO'"
      :message="'Vuoi davvero eliminare il job?'"
    ></base-modal>
    <!-- Modal Cancella tutti i job -->
    <base-modal
      :type="'confirmModal'"
      :isOpen="showDeleteAllModal"
      :hideModal="hideDeleteAllModal"
      :confirmFn="deleteAllJob"
      :title="'Elimina job'"
      :confirmBtnLabel="'SI'"
      :cancelBtnLabel="'NO'"
      :message="'Vuoi davvero eliminare tutti i job?'"
    ></base-modal>
    <!-- Modal Alert -->
    <base-modal
      :type="'alert'"
      :isOpen="showAlert"
      :hideModal="hideAlertModal"
      :confirmBtnLabel="'OK'"
      :message="outcome"
      :confirmFn="errorCallback"
      :isAnError="errorFound"
    ></base-modal>
  </div>
</template>

<script>
import { EventBus } from "../../event-bus.js";

export default {
  props: {
    title: {
      type: String,
      required: true,
    },
    url: {
      type: String,
      required: true,
    },
    detail: {
      type: String,
      required: false,
    },
    columns: {
      type: Array,
      required: true,
    },
    buttons: {
      type: String,
      required: false,
    },
  },
  data() {
    return {
      query: {
        q: null,
        orderBy: "failed_at",
        order: null,
        page: 1,
        limit: 10,
      },
      baseRedirectUrl: "/failedJobs",
      baseUrl: null,
      timeout: null,
      objects: [],
      pagination: {
        currentPage: 1,
      },
      showDeleteModal: false,
      showDeleteAllModal: false,
      loadingObject: false,
      jobId: null,
      outcome: { data: { message: "Si è verificato un errore imprevisto" } },
      errorFound: false,
      showAlert: false,
      errorCallback: null,
    };
  },

  created() {
    this.fetchObjects();
  },
  mounted() {
    EventBus.$on("refreshTable", () => {
      this.fetchObjects();
    });
  },
  methods: {
    fetchObjects() {
      let vm = this;
      vm.loadingObject = true;
      axios
        .get(vm.url, {
          params: vm.query,
        })
        .then((res) => {
          const { data, links, meta } = res.data;
          vm.objects = data;
          vm.makePagination(meta, links);
          vm.loadingObject = false;
        })
        .catch(function (error) {
          vm.loadingObject = false;
        });
    },

    makePagination(meta, links) {
      let startPage = meta.current_page - 2 < 1 ? 1 : meta.current_page - 2;
      let endPage =
        meta.current_page + 2 > meta.last_page
          ? meta.last_page
          : meta.current_page + 2;
      let pageToRender = [];

      for (let i = startPage, j = 0; i <= meta.last_page && j < 5; i++, j++) {
        pageToRender.push(i);
      }

      if (meta.last_page > 5 && pageToRender.length < 5) {
        for (
          let i = pageToRender[0] - 1;
          pageToRender.length < 5 && i > 1;
          i--
        ) {
          pageToRender.unshift(i);
        }
      }

      let pagination = {
        currentPage: meta.current_page,
        lastPage: meta.last_page,
        nextPageUrl: links.next,
        prevPageUrl: links.prev,
        pagesToRender: pageToRender,
      };

      this.pagination = pagination;
    },

    search(event) {
      if (this.timeout) clearTimeout(this.timeout);
      this.timeout = setTimeout(() => {
        this.updateQuery(
          this.query.q,
          1,
          this.query.orderBy,
          this.query.order,
          this.query.limit
        );
      }, 200);
    },

    orderBy(column) {
      let order = "DESC";

      if (this.query.orderBy === column) {
        order = this.query.order === "ASC" ? "DESC" : "ASC";
      }

      this.updateQuery(
        this.query.q,
        this.query.page,
        column,
        order,
        this.query.limit
      );
    },

    changePage(page) {
      this.updateQuery(
        this.query.q,
        page,
        this.query.orderBy,
        this.query.order,
        this.query.limit
      );
    },

    changeElementsPerPage() {
      this.updateQuery(
        this.query.q,
        1,
        this.query.orderBy,
        this.query.order,
        this.query.limit
      );
    },

    updateQuery(query, page, orderBy, order, limit) {
      this.query = {
        q: query || null,
        page: parseInt(page),
        orderBy: orderBy,
        order: order,
        limit: parseInt(limit),
      };

      this.fetchObjects();
    },

    showObject(id) {
      if (this.detail) {
        window.location.href = "/" + this.detail + "/" + id;
      }
    },
    showDelete(id = null) {
      this.jobId = id;
      this.showDeleteModal = true;
    },
    showDeleteAll() {
      this.showDeleteAllModal = true;
    },
    hideDeleteModal() {
      this.showDeleteModal = false;
    },
    hideDeleteAllModal() {
      this.showDeleteAllModal = false;
    },
    deleteJob() {
      this.showDeleteModal = false;
      axios
        .delete(this.url + this.jobId)
        .then((res) => {
          EventBus.$emit("newNotification", {
            message: "Job cancellato correttamente",
            type: "SUCCESS",
          });
          this.fetchObjects();
        })
        .catch((err) => {
          this.openAlertModal(err.response, true);
        });
    },
    deleteAllJob() {
      this.showDeleteAllModal = false;
      axios
        .delete(this.url + "all/")
        .then((res) => {
          EventBus.$emit("newNotification", {
            message: "Job cancellati correttamente",
            type: "SUCCESS",
          });
          this.fetchObjects();
        })
        .catch((err) => {
          this.openAlertModal(err.response, true);
        });
    },
    openAlertModal(outcome, isError = false, callback = null) {
      this.outcome = outcome;
      this.errorFound = isError;
      this.showAlert = true;
      this.errorCallback = callback;
    },
    hideAlertModal() {
      this.showAlert = false;
      if (!!this.errorCallback) {
        this.errorCallback();
      }
    },
    retryJob(id) {
      axios
        .get(this.url + "retry/" + id)
        .then((res) => {
          EventBus.$emit("newNotification", {
            message: "Job reinviato correttamente",
            type: "SUCCESS",
          });
          this.fetchObjects();
        })
        .catch((err) => {
          this.openAlertModal(err.response, true);
        });
    },
    retryAllJobs() {
      axios
        .get(this.url + "retry/all")
        .then((res) => {
          EventBus.$emit("newNotification", {
            message: "I job falliti saranno gradualmente reinseriti in coda",
            type: "SUCCESS",
          });
          this.fetchObjects();
        })
        .catch((err) => {
          this.openAlertModal(err.response, true);
        });
    },
  },
};
</script>
<style src="./Table.css" />
