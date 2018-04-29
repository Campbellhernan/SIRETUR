<template>
  <v-layout row>
    <v-flex xs10 offset-xs1>
       <v-card color="grey lighten-4"  flat>
        <progress-bar :show="this.busy"></progress-bar>
       <v-card-title>
           <div class="display-1 grey--text text-xs-center">Métricas del sistema</div>
       </v-card-title>
       <v-container>
            <v-card>
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
          </v-card>
      </v-container>
      </v-card>
    </v-flex>
  </v-layout>
</template>

<script>
import axios from 'axios'
export default {
  name: 'metrics-view',
  metaInfo () {
    return { title: this.$t('metrics') }
  },
  data: () => ({
    busy:false,
    title: window.config.appName,
    items:[],
    headers: [
          {text: 'Cluster',value: 'cluster'},
          { text: 'Promedio Similitud', value: 'similitud',align:'center' },
          { text: 'Cantidad de sitios', value: 'cantidad',align:'center' },
        ],
  }),
  created() {
    this.getDatos();
  },
  methods: {
 async getDatos(){
        this.busy = true
        await axios.get('/api/metrics')
				.then(response => {
				  if (response.data.status == 'OK') {
				    console.log(response.data);
				    this.items = response.data.items;
				  }
				})
				.catch(error => {
          console.log(error.response);
        });
        this.busy = false
    }
  }
}
</script>
