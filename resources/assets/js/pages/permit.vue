<template>
  <div>
    <v-dialog v-model="dialog" max-width="500px">
      <form @submit.prevent="save" @keydown="form.onKeydown($event)">
        <v-card>
          <progress-bar :show="this.busy2"></progress-bar>
          <v-card-title>
            <span class="headline"> Editar permisos y roles</span>
          </v-card-title>
          <v-card-text>
            <v-container grid-list-md>
              <v-layout wrap>
                <v-flex xs12 sm6 md4>
                  <v-text-field disabled label="Nombre de Usuario" v-model="editedItem.name"></v-text-field>
                </v-flex>
                <v-flex xs12 sm6 md4>
                  <v-text-field disabled label="Email" v-model="editedItem.email"></v-text-field>
                </v-flex>
                <v-flex xs12 sm6 md4>
                  <v-select :items="perfiles" v-model="editedItem.perfil" label="Perfil" single-line ></v-select>
                </v-flex>
              </v-layout>
            </v-container>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="blue darken-1" flat @click.native="close">Cancelar</v-btn>
            <v-btn color="blue darken-1" type="submit" flat>Guardar</v-btn>
          </v-card-actions>
        </v-card>
      </form>
    </v-dialog>
     <v-container fluid
      style="min-height: 0;"
      grid-list-lg>
        <v-card color="grey lighten-4" flat>
            <progress-bar :show="this.busy"></progress-bar>
            <v-card-title primary-title>
                <div class="display-1 dark-1 grey--text text-xs-center">Permisos de usuario</div>
            </v-card-title>
            <v-container grid-list-md>
              <v-data-table
                :headers="headers"
                :items="items"
                class="elevation-1"
                rows-per-page-text="Filas por pagina"
              >
                <template slot="items" slot-scope="props">
                  <td>{{ props.item.name }}</td>
                  <td>{{ props.item.email }}</td>
                  <td>{{ props.item.perfil }}</td>
                  <td class="justify-center layout px-0">
                    <v-btn icon class="mx-0" @click="editItem(props.item)">
                      <v-icon color="teal">edit</v-icon>
                    </v-btn>
                  </td>
                </template>
              </v-data-table>
            </v-container>
        </v-card>
    </v-container>
  </div>
</template>

<script>
import axios from 'axios'
import Form from 'vform'
export default {
  name: 'permit-view',
  metaInfo () {
    return { title: this.$t('permit') }
  },
  data: () => ({
    title: window.config.appName,
     busy: false,
     busy2: false,
    dialog: false,
    
    items:[],
    perfiles:['Usuario','Administrador','Experto'],
   headers: [
    { text: 'Nombre de usuario', value: 'name'},
    { text: 'Email', value: 'email' },
    { text: 'Perfil', value: 'perfil', width:'100px' },
    { text: 'Editar', value: 'name', sortable: false , width:'100px'}
    ],
    editedIndex: -1,
    editedItem: {
      name: '',
      email: '',
      perfil: '',
    },
    form: new Form({
      name: '',
      email: '',
      perfil: '',
    }),
    defaultItem: {
      name: '',
      email: '',
      perfil: '',
    }
  }),
  created() {
    this.getUsers();
  },
  watch: {
    dialog (val) {
      val || this.close()
    }
  },
  methods: {

   async getUsers(){
      this.busy = true
      await axios.get('/api/permit')
			.then(response => {
			  if (response.data.status == 'OK') {
			    this.items = response.data.items;
			    console.log(this.items)
			  }
			})
			.catch(error => {
        console.log(error.response);
      });
      this.busy = false
    },
    editItem (item) {
      this.editedIndex = this.items.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.dialog = true
    },
    close () {
      this.dialog = false
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      }, 300)
    },

   async save () {
      if (this.editedIndex > -1) {
        Object.assign(this.items[this.editedIndex], this.editedItem);
        if (await this.formHasErrors()) return;
        this.busy2 = true;
        console.log();
        this.form.name = this.editedItem.name;
        this.form.email = this.editedItem.email;
        this.form.perfil= this.editedItem.perfil;
        console.log(this.form);
        await this.form.post('/api/updateUser') 
        .then(response => { 
              store.dispatch('responseMessage', {
                  type: 'success',
                  text: '¡Se ha actualizado el perfil correctamente!',
                  title: 'Proceso completado',
                  modal: true
              })
            console.log(response);
          })
        .catch(error => {
            console.log(error.response);
        })
        this.busy2 = false
      } else {
        this.items.push(this.editedItem)
      }
      this.close()
    }
  }
}
</script>
