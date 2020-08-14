<template>
  <v-data-table
    item-key="name"
    class="elevation-1"
    color="error"
    :loading="loading"
    loading-text="Loading... Please wait"
    :headers="headers"
    @pagination="paginate"
    :items="roles.data"
    :server-items-length="roles.total"
    :items-per-page="5"
    show-select
    @input="selectAll"
    sort-by="calories"
    :footer-props="{
      itemsPerPageOptions: [5,10,15],
      itemsPerPageText: 'Roles Per Page',
      showCurrentPage: true,
      showFirstLastPage: true
    }"
  >
    <template v-slot:top>
      <v-text-field @input="searchIt" label="Search..."></v-text-field>
      <v-toolbar flat color="dark">
        <v-toolbar-title>ROLE MANAGEMENT SYSTEM</v-toolbar-title>
        <v-divider class="mx-4" inset vertical></v-divider>
        <v-spacer></v-spacer>
        <v-dialog v-model="dialog" max-width="500px">
          <template v-slot:activator="{ on, attrs }">
            <v-btn color="primary" dark class="mb-2" v-bind="attrs" v-on="on">Add New Role</v-btn>
            <v-btn
              color="primary"
              dark
              class="mb-2 mr-2"
              v-bind="attrs"
              @click="deleteAll"
            >Delete All</v-btn>
          </template>
          <v-card>
            <v-card-title>
              <span class="headline">{{ formTitle }}</span>
            </v-card-title>

            <v-card-text>
              <v-container>
                <v-row>
                  <v-col cols="12" sm="12" md="12">
                    <v-text-field v-model="editedItem.name" label="Role name"></v-text-field>
                  </v-col>
                  <!-- <v-col cols="12" sm="6" md="4">
                    <v-text-field v-model="editedItem.calories" label="Calories"></v-text-field>
                  </v-col>
                  <v-col cols="12" sm="6" md="4">
                    <v-text-field v-model="editedItem.fat" label="Fat (g)"></v-text-field>
                  </v-col>
                  <v-col cols="12" sm="6" md="4">
                    <v-text-field v-model="editedItem.carbs" label="Carbs (g)"></v-text-field>
                  </v-col>
                  <v-col cols="12" sm="6" md="4">
                    <v-text-field v-model="editedItem.protein" label="Protein (g)"></v-text-field>
                  </v-col>-->
                </v-row>
              </v-container>
            </v-card-text>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" text @click="close">Cancel</v-btn>
              <v-btn color="blue darken-1" text @click="save">Save</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-toolbar>
    </template>
    <template v-slot:item.actions="{ item }">
      <!-- <template> -->
      <v-icon small class="mr-2" @click="editItem(item)">mdi-pencil</v-icon>
      <v-icon small @click="deleteItem(item)">mdi-delete</v-icon>
    </template>
    <template v-slot:no-data>
      <v-btn color="primary" @click="initialize">Reset</v-btn>
    </template>
    <v-snackbar v-model="snackbar">
      Record deleted successfully.
      <template v-slot:action="{ attrs }">
        <v-btn color="red" text v-bind="attrs" @click="snackbar = false">Close</v-btn>
      </template>
    </v-snackbar>
  </v-data-table>
</template>
<script>
export default {
  data: () => ({
    dialog: false,
    loading: false,
    snackbar: false,
    selected: [],
    headers: [
      {
        text: "SN",
        align: "start",
        sortable: false,
        value: "id",
      },
      { text: "Name", value: "name" },
      { text: "Created At", value: "created_at" },
      { text: "Updated At", value: "updated_at" },
      { text: "Actions", value: "actions", sortable: false },
    ],
    roles: [],
    editedIndex: -1,
    editedItem: {
      id: "",
      name: "",
      created_at: "",
      updated_at: "",
    },
    defaultItem: {
      id: "",
      name: "",
      created_at: "",
      updated_at: "",
    },
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Role" : "Edit Role";
    },
  },

  watch: {
    dialog(val) {
      val || this.close();
    },
  },

  created() {
    this.initialize();
  },

  methods: {
    selectAll(e) {
      this.selected = [];
      if (e.length > 0) {
        this.selected = e.map((val) => val.id);
      }
    },
    deleteAll() {
      let decide = confirm("Are you sure you want to delete all these items?");
      if (decide) {
        axios
          .post("/api/roles/delete-multiple-roles", { roles: this.selected })
          .then((res) => {
            this.text = "Records deleted successfully";
            this.selected.map((val) => {
              const index = this.roles.data.indexOf(val);
              this.roles.data.splice(index, 1);
            });
            this.snackbar = true;
          })
          .catch((err) => console.log(err.response));
      }
    },
    paginate(e) {
      axios
        .get(`/api/roles?page=${e.page}`, {
          params: { per_page: e.itemsPerPage },
        })
        .then((res) => (this.roles = res.data.roles))
        .catch((err) => {
          if (err.response.status == 401) localStorage.removeItem("token");
          this.$router.push("/login");
        });
    },
    searchIt(e) {
      if (e.length > 3) {
        axios
          .get(`/api/roles/${e}`)
          .then((res) => (this.roles = res.data.roles))
          .catch((err) => console.dir(err.response));
      }
      if (e.length <= 0) {
        axios
          .get(`/api/roles`)
          .then((res) => (this.roles = res.data.roles))
          .catch((err) => console.dir(err.response));
      }
    },
    initialize() {
      // Add a request interceptor
      axios.interceptors.request.use(
        (config) => {
          this.loading = true;
          return config;
        },
        (error) => {
          this.loading = false;
          return Promise.reject(error);
        }
      );

      // Add a response interceptor
      axios.interceptors.response.use(
        (response) => {
          this.loading = false;
          return response;
        },
        (error) => {
          this.loading = false;
          return Promise.reject(error);
        }
      );
    },

    editItem(item) {
      this.editedIndex = this.roles.data.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteItem(item) {
      const index = this.roles.data.indexOf(item);
      let decide = confirm("Are you sure you want to delete this item?");
      if (decide) {
        axios
          .delete("/api/roles/" + item.id)
          .then((res) => {
            this.text = "Record deleted successfully";
            this.snackbar = true;
            this.roles.data.splice(index, 1);
          })
          .catch((err) => console.log(err.response));
      }
    },

    close() {
      this.dialog = false;
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    save() {
      if (this.editedIndex > -1) {
        let currentIndex = this.editedIndex;
        axios
          .put("api/roles/" + this.editedItem.id, {
            name: this.editedItem.name,
          })
          .then((res) =>
            Object.assign(this.roles.data[currentIndex], res.data.role)
          )
          .catch((err) => console.log(err.response));
      } else {
        axios
          .post("/api/roles", { name: this.editedItem.name })
          .then((res) => this.roles.data.push(res.data.role))
          .catch((err) => console.dir(err.response));
      }
      this.close();
    },
  },
};
</script>
<style scoped>
</style>