<template>
  <form @submit.prevent="search" @keydown="form.onKeydown($event)">
  <v-layout row>
    <v-flex xs10 offset-xs1>
       <v-card color="grey lighten-4" flat>
        <v-card-text >  
        <v-layout column align-center>
          <div class="display-3 grey--text mt-5">
            {{ title }}
          </div>
          <v-text-field column align-center
          v-model="form.textarea"
          light
          solo
          prepend-icon="search"
          placeholder="Search"
          style="max-width: 600px; min-width: 128px"
        ></v-text-field>
        </v-layout>
        </v-card-text>
        <v-card-actions>
          <v-layout row justify-center >
            <v-flex xs2>
            <v-btn color="primary" type="submit">Buscar</v-btn>
            </v-flex>
          </v-layout>
        </v-card-actions>
      </v-card>
    </v-flex>
  </v-layout>
  </form>
</template>

<script>
import Form from 'vform'
export default {
  name: 'home-view',
  metaInfo () {
    return { title: this.$t('home') }
  },
  data: () => ({
    title: window.config.appName,
    form: new Form({
        textarea: ''
    }),
  }),
  methods: {
    async  search(){
         this.$router.push({ name: 'search',params: { query: this.form.textarea } })
      }
    }
}
</script>
