<template>
    <div v-if="user.perfil =='Experto'">
      <v-card color="grey lighten-4" flat>
        <progress-bar :show="this.busy"></progress-bar>
        <v-card-title primary-title>
            <div class="display-1 dark-1 grey--text text-xs-center">Clasificar Contenido</div>
        </v-card-title>
        <form @submit.prevent="cluster" @keydown="form.onKeydown($event)">
          <v-container>
            <div class="blockquote mb-5">
              El proceso de actualización y clasificación se encargara de buscar y agregar nuevos comentarios a todos los sitios turisticos registrados en el sistema para luego clasificarlos en grupos predefinidos, el proceso puede tardar unos minutos.
            </div>
            <v-btn block class="primary" type="submit">Iniciar clasificacíon</v-btn>
            <v-carousel v-show="this.busy" interval="8000">
              <v-carousel-item
                v-for="(item,i) in srcs"
                :key="i"
                :src="item.src"
                transition="fade"
                reverse-transition="fade"
              ></v-carousel-item>
            </v-carousel>
          </v-container>
        </form>
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
                <td class="text-xs-center">{{ props.item.similitud }}</td>
                <td class="text-xs-center">{{ props.item.cantidad }}</td>
               </template>
            </v-data-table>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="green darken-1" :disabled="this.busy" flat="flat" @click.native="dialog = false">Cerrar</v-btn>
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
    form: new Form({}),
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
    srcs: [
          {src: '/img/waiting_1.gif'},
          {src: '/img/waiting_2.gif'},
          {src: '/img/waiting_3.gif'},
          {src: '/img/waiting_4.gif'}
        ]
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
            if (response.data.status == 'OK') {
                this.dialog = true;
                console.log(response.data);
                this.items = response.data.items;
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
