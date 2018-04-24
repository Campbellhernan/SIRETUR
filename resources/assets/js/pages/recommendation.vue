<template>
      <v-container  grid-list-md class="grey lighten-4">
        <progress-bar :show="busy"></progress-bar>
        <form @submit.prevent="search" @keydown="form.onKeydown($event)">
        <v-layout row pa-3 >
          <div class="title grey--text pt-3 mr-3 hidden-xs-only">
            {{ title }}
          </div>
          <v-text-field column align-center
            light
            solo
            v-on:mouseover="change"
            v-on:mouseleave="change"
            v-bind:class="{'elevation-5' : apply}"
            v-model="form.textarea"
            append-icon="search"
            :append-icon-cb="search"
            placeholder="Buscar"
          ></v-text-field>
        </v-layout>
        </form>
        <v-divider></v-divider>
          <v-layout row wrap v-if="found">
          <v-flex
            v-for="card in cards"
            :key="card.nombre"
          >
            <v-card>
            <v-card-title column primary-title>
                <div class="headline" >{{card.nombre}}</div>
            </v-card-title>
            <v-card-text>
                <div class="body-1">{{ card.description.substr(0,300) + "..."}}</div>
            </v-card-text>
            <v-card-actions>
              <v-btn flat color="primary" @click.native="content(card)">Detalles</v-btn>
              <v-spacer class="hidden-xs-only" ></v-spacer>
              <star-rating read-only class="" v-bind:increment="0.001" v-bind:rating="parseFloat(card.rating)" :star-size="20"></star-rating>
            </v-card-actions>
            </v-card>
          </v-flex>
          <v-flex>
            <div class="text-xs-center">
              <v-pagination :length="pagination.last_page" v-model="pagination.current_page" @next="recommendation()" @previous="recommendation()" @input="recommendation()" circle></v-pagination>
            </div>
          </v-flex>
        </v-layout>
        <v-layout row wrap v-else-if="!this.busy">
          <v-flex xs12>
            <v-card color="error">
            <v-card-title row primary-title>
              <v-icon>warning</v-icon>
                <div class="headline" >Sorry, nothing to display here :(</div>
            </v-card-title>
            </v-card>
          </v-flex>
        </v-layout>
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
    apply: false,
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
    change() {
      this.apply = !this.apply;
    },
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
      this.$vuetify.goTo(0, {duration: 500,offset:0,easing:'linear'});
    },
    content: function($card){
       this.$router.push({ name: 'content',query: {  place_id: $card.place_id} })
    },
    search: function(){
     this.$router.push({ name: 'search',query: { query: this.form.textarea } })
    }
  }
}
</script>
