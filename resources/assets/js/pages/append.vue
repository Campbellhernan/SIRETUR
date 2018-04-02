<template>
  <div v-if="user.perfil =='Experto' || user.perfil == 'Administrador'">
   <v-container
      fluid
      style="min-height: 0;"
      grid-list-lg
    >
     <v-layout row wrap>
     <v-flex xs12>
        <v-card height="300px" width="100%" >
          <v-container>                
            <v-layout>
              <input id="pac-input" class="controls" type="text" placeholder="Buscar">
              <div id="infowindow-content">
                <span id="place-name"  class="title"></span><br>
                <span id="place-address"></span>
              </div>
              <google-map
                 name="example"
              ></google-map>
            </v-layout>
          </v-container>  
        </v-card>
      </v-flex>
        <v-flex xs12>
          <v-card color="grey lighten-4" flat>
          <progress-bar :show="form.busy"></progress-bar>
          <v-card-title primary-title>
              <div class="headline">Añadir contenido</div>
          </v-card-title>
          <v-container grid-list-md>
            <form @submit.prevent="append" @keydown="form.onKeydown($event)">
              <v-text-field v-model="form.place_id" box disabled v-validate="'required'" label="Place ID"></v-text-field>
              <has-error :form="form" :field="form.place_id"></has-error> 
              <v-text-field v-model="form.descripcion" box multi-line label="Descripcion"></v-text-field>
              <v-text-field v-model="form.fuente" box label="URL Fuente"></v-text-field>
              <v-layout>
                <v-spacer></v-spacer>
                <v-btn xs2 type="submit"  color="primary"> Aceptar</v-btn>
              </v-layout>
            </form>
          </v-container>
        </v-card>
      </v-flex>
      </v-layout>
    </v-container>
    </div>
</template>

<script>
import Form from 'vform'
import store from '~/store'
import googleMap from '~/components/GoogleMap'
import axios from 'axios'
import { mapGetters } from 'vuex'
export default {
  components: {
    'google-map': googleMap,
  },
  name: 'append-view',
  metaInfo () {
    return { title: this.$t('append') }
  },
  computed: mapGetters({
    user: 'authUser',
    authenticated: 'authCheck'
  }),
  data: () => ({
    form: new Form({
      place_id: '',
      descripcion: '',
      fuente:''
    }),
    busy: false,
    documentos: {}
  }),

  mounted: function () {
    this.getDocumento();
  },
  methods: {
    async append () {
      if (await this.formHasErrors()) return
      this.busy = true
      
      var id = this.form.place_id;
      
      var existeDocumento = this.documentos.findIndex(function (doc) {
          doc.id == id;
      }) != -1;
      if(!existeDocumento){  
        // Submit the form.
        await this.form.post('/api/append') .then(response => { 
          	console.log(response);
          	this.form.place_id = '';
          	this.form.descripcion = '';
          	this.form.fuente = '';
          	if (response.data.status == 'OK') {
                store.dispatch('responseMessage', {
                  type: 'success',
                  text: '¡Se ha agregado un nuevo sitio al sistema!',
                  title: 'Proceso completado',
                  modal: true
              })
            }else if(response.data.status == 'Existe'){
              store.dispatch('responseMessage', {
                  type: 'warning',
                  text: 'Esta ubicación ya ha sido registrada en el sistema.',
                  title: 'Proceso no completado',
                  modal: true
              })
            }
        })
        .catch(error => {
            console.log(error.response)
        });
      }else{
        store.dispatch('responseMessage', {
                  type: 'warning',
                  text: 'Esta ubicación ya ha sido registrada en el sistema.',
                  title: 'Proceso no completado',
                  modal: true
        })
      }
      this.busy = false
    },
    async getDocumento(){
        this.busy = true
        await axios.get('/api/documents')
				.then(response => {
				  this.documentos = response.data;
				   if(this.user.perfil =='Experto' || this.user.perfil == 'Administrador'){
              const element = document.getElementById("example-map")
              var latLng = new google.maps.LatLng(10.238969,-68.001141)
              const options = {
                zoom: 12,
                center: latLng
              }
              const map = new google.maps.Map(element, options);
              this.documentos.forEach((coord) => {
                const position = new google.maps.LatLng(coord.latitud, coord.longitud);
                const marker = new google.maps.Marker({ 
                  position,
                  map
                });
              });
              var _this = this;
              var input = document.getElementById('pac-input');
              var autocomplete = new google.maps.places.Autocomplete(input);
              autocomplete.bindTo('bounds', map);
      
              map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
      
              var infowindow = new google.maps.InfoWindow();
              var marker = new google.maps.Marker({
                map: map
              });
              marker.addListener('click', function() {
                infowindow.open(map, marker);
              });
      
              autocomplete.addListener('place_changed', function() {
                infowindow.close();
                var place = autocomplete.getPlace();
                if (!place.geometry) {
                  return;
                }
      
                if (place.geometry.viewport) {
                  map.fitBounds(place.geometry.viewport);
                } else {
                  map.setCenter(place.geometry.location);
                  map.setZoom(17);
                }
      
                // Set the position of the marker using the place ID and location.
                marker.setPlace({
                  placeId: place.place_id,
                  location: place.geometry.location
                });
                marker.setVisible(true);
                _this.form.place_id = place.place_id;
      
                document.getElementById('place-name').textContent = place.name;
                document.getElementById('place-address').textContent =
                    place.formatted_address;
                infowindow.setContent(document.getElementById('infowindow-content'));
                infowindow.open(map, marker);
              });
              
              var clickHandler = new ClickEventHandler(map,this.form);
            }
				});
				this.busy = false
    }
  }, 
  beforeRouteEnter(to, from, next) {
    next(vm => {
        if(vm.user.perfil =='Experto' || vm.user.perfil == 'Administrador'){
            return next();
        }else{
          return next({name:'home'});
        }
    })
  }
}
  var ClickEventHandler = function(map,form) {
    this.map = map;
    this.directionsService = new google.maps.DirectionsService;
    this.directionsDisplay = new google.maps.DirectionsRenderer;
    this.directionsDisplay.setMap(map);
    this.placesService = new google.maps.places.PlacesService(map);

    // Listen for clicks on the map.
    this.map.addListener('click', this.handleClick.bind(this,form));
  };
    
  ClickEventHandler.prototype.handleClick = function(form,event) {
    if (event.placeId) {
      form.place_id = event.placeId;
      //event.stop();
    }else{
      form.place_id = '';
      form.descripcion = '';
    }
  };
</script>
   <style>
      #map {
        height: 100%;
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      .controls {
        background-color: #fff;
        border-radius: 2px;
        border: 1px solid transparent;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        box-sizing: border-box;
        font-family: Roboto;
        font-size: 15px;
        font-weight: 300;
        height: 29px;
        margin-left: 17px;
        margin-top: 10px;
        outline: none;
        padding: 0 11px 0 13px;
        text-overflow: ellipsis;
        width: 400px;
      }

      .controls:focus {
        border-color: #4d90fe;
      }
      .title {
        font-weight: bold;
      }

      #map #infowindow-content {
        display: inline;
      }
    </style>
