<template>
  <form @submit.prevent="search" @keydown="form.onKeydown($event)">
    <v-container fluid fill-height>
      <v-layout flex align-center justify-center column>
          <v-layout align-center>
            <v-flex xs2>
              <div class="display-5 mt-5">
                <v-icon color="grey" size="42px">fa-map-signs</v-icon>
              </div>
            </v-flex>
            <v-flex xs3 >
              <div class="display-3 grey--text mt-5">
                {{ title }}
              </div>
            </v-flex>
          </v-layout>
            <v-text-field column align-center
            v-model="form.textarea"
            light
            solo
            prepend-icon="search"
            placeholder="Search"
            class="mt-4"
            v-on:mouseover="change"
            v-on:mouseleave="change"
            v-bind:class="{'elevation-5' : apply}"
            style="max-width: 600px;"
            ></v-text-field>
      </v-layout>
    </v-container>
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
    apply: false,
    form: new Form({
        textarea: ''
    }),
  }),
  methods: {
    async  search(){
         this.$router.push({ name: 'search',query: { query: this.form.textarea } })
    },
    change() {
      this.apply = !this.apply;
    }
  }
}
</script>
