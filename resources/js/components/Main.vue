<template>
  <div>
    <heading class="mb-6">Import</heading>

    <card class="flex flex-col items-center justify-center" style="min-height: 300px">
      <button
        type="button"
        style="margin-bottom: 1rem"
        class="btn btn-default"
        @click="selectFile"
      >Upload File</button>
      <input type="file" hidden name="file" id="import_file" ref="file" @change="handleFile" />
      <div style="margin-bottom: 1rem; font-weight: bold;" id="filename_preview"></div>
      <button type="submit" class="btn btn-default btn-primary" @click="upload">Import</button>
    </card>

    <heading class="mb-6 mt-6">Previous Files</heading>

    <card class="flex items-center justify-center" style="min-height: 300px; flex-wrap: wrap;">
      <div
        style="flex: 0 0 100%; text-align: right; padding-right: 2rem"
        v-if="prevfiles != null && prevfiles.length > 0"
      >
        <button type="button" class="btn btn-default btn-danger" @click="deleteAll">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            style="vertical-align: text-bottom"
            viewBox="0 0 24 24"
            width="24"
            height="24"
            fill="white"
          >
            <path
              class="heroicon-ui"
              d="M8 6V4c0-1.1.9-2 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8H3a1 1 0 1 1 0-2h5zM6 8v12h12V8H6zm8-2V4h-4v2h4zm-4 4a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0v-6a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0v-6a1 1 0 0 1 1-1z"
            />
          </svg>
          Delete All
        </button>
      </div>
      <h4 v-if="prevfiles == null || prevfiles.length == 0">Tidak ada data</h4>
      <div v-for="file in prevfiles" v-bind:key="file">
        <button
          type="button"
          style="margin: 1rem"
          class="btn btn-default"
          @click="selectPrevFile"
        >{{file}}</button>
      </div>
    </card>
    <portal to="modals">
      <transition name="fade">
        <general-modal v-if="modalOpen" @confirm="confirmModal" @close="closeModal" />
      </transition>
    </portal>
  </div>
</template>

<script>
import GeneralModal from "./GeneralModal.vue";

export default {
  mounted() {
    const self = this;

    Nova.request()
      .get("/nova-vendor/laravel-nova-csv-import/prevfiles/")
      .then(function (response) {
        self.prevfiles = response.data.prevfiles;
      });
  },
  data() {
    return {
      file: "",
      prevfiles: null,
      modalOpen: false,
      selectedPrevFiles: "",
    };
  },
  components: {
    GeneralModal,
  },
  methods: {
    confirmModal: function (event) {
      const self = this;

      Nova.request()
        .post("/nova-vendor/laravel-nova-csv-import/deleteallfiles")
        .then(function (response) {
          self.$toasted.show("All files deleted!", { type: "success" });
          self.prevfiles = null;
        })
        .catch(function (e) {
          self.$toasted.show(e.response.e.message, { type: "error" });
        });
      this.modalOpen = false;
    },
    closeModal: function (event) {
      this.modalOpen = false;
    },
    deleteAll: function (event) {
      this.modalOpen = true;
    },
    selectPrevFile: function (event) {
      this.$router.push({
        name: "csv-import-preview",
        params: { file: event.target.innerHTML.split("|")[1].trim() },
      });
      // window.location.href = "/admin/csv-import/preview/" + event.target.innerHTML.split("|")[1].trim();
    },
    handleFile: function (event) {
      this.file = this.$refs.file.files[0];
      document.getElementById("filename_preview").innerHTML = this.file.name;
    },
    selectFile: function (event) {
      document.getElementById("import_file").click();
    },
    upload: function (event) {
      let formData = new FormData();
      // send it to the server
      formData.append("file", this.file);

      const self = this;

      // if it's valid, move to the next screen
      Nova.request()
        .post("/nova-vendor/laravel-nova-csv-import/upload", formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })
        .then(function (response) {
          self.$router.push({
            name: "csv-import-preview",
            params: { file: response.data.file },
          });
        })
        .catch(function (e) {
          self.$toasted.show(e.response.data.message, { type: "error" });
        });
    },
  },
};
</script>

<style>
/* Scoped Styles */
</style>
