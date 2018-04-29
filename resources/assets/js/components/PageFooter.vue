<template>
  <v-footer app absolute height="auto">
    <v-layout row wrap align-center>
      <v-flex>
        <v-card
          flat
          tile
          class="dark-1 grey--text text-xs-center"
        >
          <v-card-text>
            <v-btn
              v-for="icon in icons"
              :key="icon"
              icon
              class="mx-3 grey--text"
            >
              <v-icon size="24px">{{ icon }}</v-icon>
            </v-btn>
          </v-card-text>
          <v-card-text class="grey--text">
            &copy;{{ new Date().getFullYear() }} — <strong>Universidad de Carabobo</strong>
          </v-card-text>
        </v-card>
      </v-flex>
    </v-layout>
  </v-footer>
</template>

<script>
export default {
  data () {
    return {
      fixed: false,
      icons: ['fab fa-facebook', 'fab fa-twitter', 'fab fa-google-plus', 'fab fa-instagram']
    }
  }
}
</script>
