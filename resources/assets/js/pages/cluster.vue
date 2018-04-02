<template>
    <div v-if="user.perfil =='Experto'">
      <v-card color="grey lighten-4" flat>
        <progress-bar :show="this.busy"></progress-bar>
        <v-card-title primary-title>
            <div class="headline">Clasificar contenido</div>
        </v-card-title>
        <v-container grid-list-md>
          <form @submit.prevent="cluster" @keydown="form.onKeydown($event)">
              <v-flex xs12>
                  <v-text-field v-model="form.k" box label="Cantidad Cluster" mask="###"></v-text-field>
              </v-flex>
              <v-btn type="submit"> Aceptar</v-btn>
          </form>
        </v-container>
      </v-card>
      <v-dialog v-model="dialog" max-width="600">
      <v-card>
        <v-card-title class="headline">Proceso completado</v-card-title>
        <v-card-text>
          ¡Se ha clasificado el contenido exitosamente!
        <v-data-table
          v-bind:headers="headers"
          :items="items"
          hide-actions
          class="elevation-1"
        >
           <template slot="items" slot-scope="props">
            <td>{{ props.item.cluster }}</td>
            <td class="text-xs-right">{{ props.item.similitud }}</td>
            <td class="text-xs-right">{{ props.item.cantidad }}</td>
           </template>
        </v-data-table>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="green darken-1" flat="flat" @click.native="dialog = false">Cerrar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    </div>
</template>


<script>
import Form from 'vform'
import store from '~/store'
import { mapGetters } from 'vuex'
export default {
  name: 'cluster-view',
  metaInfo () {
    return { title: this.$t('cluster') }
  },
  data: () => ({
    form: new Form({
      k: ''
    }),
    busy: false,
    dialog: false,
    items:[],
    headers: [
          {
            text: 'Cluster',
            align: 'left',
            sortable: false,
            value: 'cluster'
          },
          { text: 'Promedio Similitud', value: 'similitud' },
          { text: 'Cantidad de sitios', value: 'cantidad' },
        ],
  }),
  computed: mapGetters({
    user: 'authUser',
    authenticated: 'authCheck'
  }),
  methods: {
    async cluster () {
      if (await this.formHasErrors()) return
      this.busy = true

      await this.form.post('/api/cluster') 
            .then(response => { 
                console.log(response);
                this.form.k = '';
                if (response.data.status == 'OK') {
                      this.dialog = true;
                      console.log(response.data);
                      this.items = response.data.items;
                     /* store.dispatch('responseMessage', {
                        type: 'success',
                        text: '¡Se ha clasificado el contenido exitosamente!',
                        title: 'Proceso completado',
                        modal: true
                    })*/
                  }
              })
            .catch(error => {
                console.log(error.response);
            })
      this.busy = false
    }
  },
  beforeRouteEnter(to, from, next) {
    next(vm => {
        if(vm.user.perfil == 'Experto' ){
            return next();
        }else{
          return next({name:'home'});
        }
    })
  }
}
</script>
