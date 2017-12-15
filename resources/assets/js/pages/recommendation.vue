<template>
      <v-container fluid grid-list-md class="grey lighten-4">
        <progress-bar :show="busy"></progress-bar>
        <form @submit.prevent="recommendation" @keydown="form.onKeydown($event)">
        <v-layout row pa-4>
          <div class="headline grey--tex t ma-2">
            {{ title }}
          </div>
              <v-text-field column align-center ma-4
              light
              solo
              v-model="form.textarea"
              prepend-icon="search"
              placeholder="Buscar"
            ></v-text-field>
            <v-btn color="primary" type="submit">Buscar</v-btn>
        </v-layout>
        </form>
        <v-divider></v-divider>
        <v-flex xs10 offset-xs1 mt-3>
          <v-layout row wrap v-if="found">
            <v-flex
              v-bind="{ [`xs${flexs}`]: true }"
              v-for="card in cards"
              :key="card.nombre"
            >
              <v-card>
              <v-card-title column primary-title>
                  <div class="headline" >{{card.nombre}}</div>
                  <div>{{card.description}}</div>
              </v-card-title>
              <v-card-actions>
                <v-btn flat color="primary">Explorar</v-btn>
              </v-card-actions>
              </v-card>
            </v-flex>
          </v-layout>
          <v-layout row wrap v-else>
            <v-flex v-bind="{ [`xs${flexs}`]: true }">
              <v-card color="error">
              <v-card-title row primary-title>
                <v-icon>warning</v-icon>
                  <div class="headline" >Sorry, nothing to display here :(</div>
              </v-card-title>
              </v-card>
            </v-flex>
          </v-layout>
        </v-flex>
      </v-container>
</template>

<script>
import axios from 'axios'
import Form from 'vform'
export default {
  name: 'recommendation-view',
  metaInfo () {
    return { title: this.$t('recommendation') }
  },
  data: () => ({
    title: window.config.appName,
    cards: [],
    flexs: 12,
    busy: false,
    found:false,
    form: new Form({
      textarea: ''
    }),
  }),
  created() {
  		this.recommendation();
	},
  methods: {

     async recommendation () {
      if (await this.formHasErrors()) return
      this.busy = true

      await this.form.get('/api/recommendation') 
      .then(response => { 
          console.log(response.data);
          if(response.data.status == 'OK'){
            if(response.data.documentos.length >0){
              this.cards = response.data.documentos;
              this.found=true;
            }else{
              this.found = false;
            }
          }
        })
      .catch(error => {
          console.log(error.response);
      })
      this.busy = false
    }
  }
}
</script>
