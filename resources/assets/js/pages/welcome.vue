<template>
  <div>
    <v-toolbar dark color="light-blue darken-4">
      <v-spacer></v-spacer>
      <!-- <v-toolbar-side-icon class="hidden-md-and-up"></v-toolbar-side-icon> -->
      <v-toolbar-items>
        <v-btn flat v-if="authenticated" :to="{ name: 'home' }">{{ $t('home') }}</v-btn>
        <template v-else >
            <v-btn class="hidden-xs-only" flat :to="{ name: 'login' }">{{ $t('login') }}</v-btn>
            <v-btn class="hidden-xs-only" flat :to="{ name: 'register' }">{{ $t('register') }}</v-btn>
            <v-layout align-center class="hidden-sm-and-up">
              <v-btn flat :to="{ name: 'login' }" icon color="white" slot="activator">
                <v-icon>fa-sign-in-alt</v-icon>
              </v-btn>
              <v-btn flat :to="{ name: 'register' }" icon color="white">
                <v-icon>fa-user-plus</v-icon>
              </v-btn>
            </v-layout>
        </template>
      </v-toolbar-items>
    </v-toolbar>
    <main>
      <v-content>
        <v-container fluid>
          <v-layout column align-center>
            <v-flex>
              <v-layout align-center>
                <v-flex xs2>
                  <div class="display-5 mt-5">
                    <v-icon color="grey" size="42px">fa-map-signs</v-icon>
                  </div>
                </v-flex>
                <v-flex xs3>
                  <div class="display-3 grey--text mt-5">
                    {{ title }}
                  </div>
                </v-flex>
              </v-layout>
            </v-flex>
          </v-layout>
        </v-container>
      </v-content>
    </main>
    <v-footer fixed height="auto">
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
  </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  name: 'welcome-view',
  layout: 'default',

  metaInfo () {
    return { title: this.$t('home') }
  },

  computed: mapGetters({
    authenticated: 'authCheck'
  }),

  data: () => ({
    title: window.config.appName,
    icons: ['fab fa-facebook', 'fab fa-twitter', 'fab fa-google-plus', 'fab fa-instagram']
  })
}
</script>
