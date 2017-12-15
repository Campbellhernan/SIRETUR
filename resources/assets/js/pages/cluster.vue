<template>
      <v-card color="grey lighten-4" flat>
        <progress-bar :show="form.busy"></progress-bar>
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
</template>

<script>
import Form from 'vform'
import store from '~/store'
export default {
  name: 'cluster-view',
  metaInfo () {
    return { title: this.$t('cluster') }
  },
  data: () => ({
    form: new Form({
      k: ''
    }),
    busy: false
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
                      store.dispatch('responseMessage', {
                        type: 'success',
                        text: '¡Se ha clasificado el contenido exitosamente!',
                        title: 'Proceso completado',
                        modal: true
                    })
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
