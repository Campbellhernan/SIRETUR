<template>
   <v-container
      fluid
      style="min-height: 0;"
      grid-list-lg
    >
    <progress-bar :show="this.busy"></progress-bar>
     <v-layout  row wrap>
       <v-flex xs12>
          <v-card color="grey lighten-4" flat v-show="this.cargado">
          <v-card-title primary-title>
            <v-container grid-list-md>
            <v-layout align-center row spacer>
              <v-flex xs1>
                <v-btn flat icon large color="blue" @click="back()">
                  <v-icon>arrow_back</v-icon>
                </v-btn>
              </v-flex>
              <v-flex>
                <div class="display-2 grey--text">{{this.documento.nombre}}</div>
              </v-flex>
            </v-layout>
            <v-flex>
              <div class="title grey--text">{{this.documento.direccion}}</div>
            </v-flex>
            </v-container>
          </v-card-title>
          <v-container grid-list-md>
            <blockquote>
            {{this.documento.description}}
            </blockquote>
            <star-rating read-only v-bind:increment="0.001" v-bind:rating="obtenerRating()" ></star-rating>
          </v-container>
        </v-card>
      </v-flex>
     <v-flex xs12>
          <v-card height="300px" width="100%" >
            <v-container>
                <v-layout>
                  <google-map
                     name="example"
                  ></google-map>
                </v-layout>
                <v-layout>
                  <input id="pac-input" class="controls" type="text" placeholder="Search Box">
                </v-layout>
              </v-container>
          </v-card>
      </v-flex>
      <v-flex xs12>
          <v-card color="grey lighten-4" flat v-show="this.cargado">
          <v-card-title primary-title>
              <div class="headline">Califica este lugar y opina sobre él</div>
          </v-card-title>
          <v-container grid-list-md>
            <form @submit.prevent="public" @keydown="form.onKeydown($event)">
                <v-flex xs12>
                     <star-rating v-model="form.rating" v-bind:increment="0.5" :star-size="30" ></star-rating>
                    <v-text-field v-model="form.comentario" box multi-line label="Comentario"></v-text-field>
                </v-flex>
                <v-btn type="submit" color="primary">Publicar</v-btn>
            </form>
            <v-divider></v-divider>
            <v-flex xs10 offset-xs1 mt-3 v-if="cards.length > 0">
              <div class="headline">Reseñas</div>
              <v-layout row wrap>
                 <v-flex xs12
              v-for="card in cards"
              :key="card.nombre"
            >
              <v-card>
                <v-card-title column>
                  <v-layout align-center row spacer>
                    <v-flex xs1>
                      <v-avatar size="36px" v-bind:class="card.avatarColor">
                        <span class="white--text headline">{{card.nombre_usuario.charAt(0).toUpperCase()}}</span>
                      </v-avatar>
                    </v-flex>
                    <v-flex>
                      <div class="title" >{{card.nombre_usuario}}</div>
                    </v-flex>
                    <v-flex xs2>
                      <star-rating read-only v-bind:increment="0.001" v-bind:rating="parseFloat(card.rating)" :star-size="20"></star-rating>
                    </v-flex>
                  </v-layout>
              </v-card-title>
              <v-card-text>
                <div class="subheading">{{card.comentario}}</div>
                <v-divider></v-divider>
                  <v-layout align-center row spacer>
                    <v-flex>
                      <div class="caption" >{{card.fecha_publicacion}}</div>
                    </v-flex>
                    <v-flex xs1>
                      <div class="caption" >{{card.origen}}</div>
                    </v-flex>
                  </v-layout>
              </v-card-text>
              </v-card>
            </v-flex>
              </v-layout>
            </v-flex>
          </v-container>
        </v-card>
      </v-flex>
      </v-layout>
    </v-container>
</template>
<script>
import Form from 'vform'
import axios from 'axios'
import googleMap from '~/components/GoogleMap'
import StarRating from 'vue-star-rating'
export default {
  components: {
    'google-map': googleMap,
    'star-rating':StarRating
  },
    data: function () { 
    return {title: window.config.appName,
    form: new Form({
      comentario: '',
      place_id: '',
      rating: 0,
    }),
    documento: {},
    cards: {},
    busy: false,
    map: null, 
    marker: null,
    cargado:false,
    latLng:{}
    }
  },
  name: 'content-view',
  metaInfo () {
    if(this.$route.query.place_id !== undefined){
      return { title: this.documento.nombre }
    }
  },
  created() {
    if(this.$route.query.place_id == undefined){
      this.$router.push({ name: 'home'});
    }else{
      this.form.place_id = this.$route.query.place_id;
    }
  },
  mounted () {
    this.obtenerDatos();
  },

  methods:{
    back: function(){
      this.$router.go(-1);
    },
    obtenerRating: function(){
      return parseFloat(this.documento.rating);
    },
    async obtenerDatos () {
      if (await this.formHasErrors()) return
      this.busy = true
      await this.form.post('/api/content') 
      .then(response => { 
          if(response.data.status == 'OK'){
            this.documento = response.data.documento;
            this.cards = response.data.comentario;
            this.latLng = new google.maps.LatLng(parseFloat(response.data.documento.latitud),parseFloat(response.data.documento.longitud))
            const element = document.getElementById("example-map")
            const options = {
              zoom: 14,
              center: this.latLng,
            }
            this.map  = new google.maps.Map(element, options);
            this.marker = new google.maps.Marker ({ 
              position:this.latLng, 
              map: this.map,
              visible:true
            }); 
          }
        })
      .catch(error => {
          console.log(error.response);
      })
      this.busy = false
      this.cargado = true
    },
    async public () {
      if (await this.formHasErrors()) return
      this.busy = true
      await this.form.post('/api/public') .then(response => { 
        	console.log(response);
        	this.form.rating = 0;
        	this.form.comentario = '';
        	if (response.data.status == 'OK') {
        	    this.cards.push(response.data.comentario);
              store.dispatch('responseMessage', {
                type: 'success',
                text: '¡Se ha agregado tu comentario!',
                title: 'Proceso completado',
                modal: true
            })
          }
        })
        .catch(error => {
            console.log(error.response)
        });
      this.busy = false
    },
  }
}
  
</script>

