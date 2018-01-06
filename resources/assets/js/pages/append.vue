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
                <v-flex xs12>
                    <v-text-field v-model="form.place_id" box disabled v-validate="'required'" label="Place ID"></v-text-field>
                    <has-error :form="form" :field="form.place_id"></has-error> 
                </v-flex>
                <v-flex xs12>
                    <v-text-field v-model="form.descripcion" box multi-line label="Descripcion"></v-text-field>
                </v-flex>
                <v-btn type="submit"  color="primary"> Aceptar</v-btn>
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
      descripcion: ''
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
              
              var input = document.getElementById('pac-input');
              var searchBox = new google.maps.places.SearchBox(input);
              map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
               map.addListener('bounds_changed', function() {
          searchBox.setBounds(map.getBounds());
        });

        var markers = [];
        // Listen for the event fired when the user selects a prediction and retrieve
        // more details for that place.
        searchBox.addListener('places_changed', function() {
          var places = searchBox.getPlaces();

          if (places.length == 0) {
            return;
          }

          // Clear out the old markers.
          markers.forEach(function(marker) {
            marker.setMap(null);
          });
          markers = [];

          // For each place, get the icon, name and location.
          var bounds = new google.maps.LatLngBounds();
          places.forEach(function(place) {
            if (!place.geometry) {
              console.log("Returned place contains no geometry");
              return;
            }
            var icon = {
              url: place.icon,
              size: new google.maps.Size(71, 71),
              origin: new google.maps.Point(0, 0),
              anchor: new google.maps.Point(17, 34),
              scaledSize: new google.maps.Size(25, 25)
            };

            // Create a marker for each place.
            markers.push(new google.maps.Marker({
              map: map,
              icon: icon,
              title: place.name,
              position: place.geometry.location
            }));

            if (place.geometry.viewport) {
              // Only geocodes have viewport.
              bounds.union(place.geometry.viewport);
            } else {
              bounds.extend(place.geometry.location);
            }
          });
          map.fitBounds(bounds);
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

      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }

      .controls {
        margin-top: 10px;
        border: 1px solid transparent;
        border-radius: 2px 0 0 2px;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        height: 32px;
        outline: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
      }

      #pac-input {
        background-color: #fff;
        font-family: Roboto;
        font-size: 15px;
        font-weight: 300;
        margin-left: 12px;
        padding: 0 11px 0 13px;
        text-overflow: ellipsis;
        width: 400px;
      }

      #pac-input:focus {
        border-color: #4d90fe;
      }

      .pac-container {
        font-family: Roboto;
      }

      #type-selector {
        color: #fff;
        background-color: #4d90fe;
        padding: 5px 11px 0px 11px;
      }

      #type-selector label {
        font-family: Roboto;
        font-size: 13px;
        font-weight: 300;
      }

      #target {
        width: 345px;
      }
    </style>
