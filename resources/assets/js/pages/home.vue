<template>
  <v-layout row>
    <v-flex xs10 offset-xs1>
       <v-card>
        <progress-bar :show="busy"></progress-bar>
        <v-card-text >  
        <v-layout column align-center>
          <div class="display-3 grey--text mt-5">
            {{ title }}
          </div>
          <v-text-field column align-center
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
            <v-btn color="default">Buscar</v-btn>
            </v-flex>
            <v-flex xs2>
            <v-btn color="default">Voy a tener suerte</v-btn>
            </v-flex>
          </v-layout>
        </v-card-actions>
      </v-card>
    </v-flex>
  </v-layout>
</template>

<script>
export default {
  name: 'home-view',
  metaInfo () {
    return { title: this.$t('home') }
  },
  data: () => ({
    title: window.config.appName
  })
}
</script>
