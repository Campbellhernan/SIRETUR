<template>
   <v-container
      fluid
      style="min-height: 0;"
      grid-list-lg
    >
     <v-layout row wrap>
        <v-flex xs12>
          <v-card height="300px">
            <v-container>
                <v-layout>
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
                <v-btn type="submit"> Aceptar</v-btn>
            </form>
          </v-container>
        </v-card>
      </v-flex>
      </v-layout>
    </v-container>
</template>

<script>
import Form from 'vform'
import store from '~/store'
import googleMap from '~/components/GoogleMap'
import axios from 'axios'
export default {
  components: {
    'google-map': googleMap,
  },
  name: 'append-view',
  metaInfo () {
    return { title: this.$t('append') }
  },
  data: () => ({
    form: new Form({
      place_id: '',
      descripcion: ''
    }),
    busy: false,
    documentos: {}
  }),
  created() {
  		this.getDocumento();
	},
  mounted: function () {
    const element = document.getElementById("example-map")
    var latLng = new google.maps.LatLng(10.238969,-68.001141)
    const options = {
      zoom: 12,
      center: latLng
    }
    const map = new google.maps.Map(element, options);
    var clickHandler = new ClickEventHandler(map,this.form);
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
				});
				this.busy = false
    }
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
