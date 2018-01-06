<template>
      <v-container fluid grid-list-md class="grey lighten-4">
        <progress-bar :show="busy"></progress-bar>
        <form @submit.prevent="search" @keydown="form.onKeydown($event)">
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
              </v-card-title>
              <v-card-text>
                  <div class="body-1">{{card.description}}</div>
              </v-card-text>
              <v-card-actions>
                <v-flex>
                  <v-btn flat color="primary" @click.native="content(card)">Ver mas</v-btn>
                  <v-btn flat color="primary" v-bind:href="card.url" >Ver en Google Map</v-btn>
                </v-flex>
                <v-flex xs2>
                    <star-rating read-only v-bind:increment="0.001" v-bind:rating="parseFloat(card.rating)" :star-size="20"></star-rating>
                </v-flex>
              </v-card-actions>
              </v-card>
            </v-flex>
            <v-flex>
              <div class="text-xs-center">
                <v-pagination :length="pagination.last_page" v-model="pagination.current_page" @next="recommendation()" @previous="recommendation()" @input="recommendation()" circle></v-pagination>
              </div>
            </v-flex>
          </v-layout>
          <v-layout row wrap v-else>
            <v-flex xs12>
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
import StarRating from 'vue-star-rating'
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
      textarea: '',
      current_page: 1
    }),
    pagination:{
      total: 0,
      per_page: 0,
      last_page: 1,
      current_page: 1,
    },
  }),
  components:{
      'star-rating':StarRating
  },
  created() {
  		this.recommendation();
	},
  methods: {

     async recommendation () {
      if (await this.formHasErrors()) return
      this.busy = true
      this.form.current_page = this.pagination.current_page;
      await this.form.post('/api/recommendation') 
      .then(response => { 
          console.log(response.data);
          if(response.data.status == 'OK'){
            if(response.data.documentos.data.length >0){
              this.pagination.total = response.data.documentos.total;
              this.pagination.per_page = response.data.documentos.per_page;
              this.pagination.last_page = response.data.documentos.last_page;
              this.cards = response.data.documentos.data;
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
    },
    content: function($card){
       this.$router.push({ name: 'content',query: {  place_id: $card.place_id} })
    },
    async  search(){
     this.$router.push({ name: 'search',query: { query: this.form.textarea } })
    }
  }
}
</script>
