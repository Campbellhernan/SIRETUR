<template>
  <v-toolbar app clipped-left fixed dark class="light-blue darken-4">
    <v-toolbar-side-icon @click.stop="toggleDrawer" v-if="authenticated"></v-toolbar-side-icon>
    <v-toolbar-title :style="$vuetify.breakpoint.smAndUp ? 'width: 300px; min-width: 250px' : 'min-width: 72px'" class="ml-0 pl-3">
      <router-link v-if="authenticated" :to="{ name: 'home' }" class="white--text hidden-xs-only">
        {{ appName }}
      </router-link>
      
      <router-link v-else :to="{ name: 'welcome' }" class="white--text hidden-xs-only">
        {{ appName }}
      </router-link>
      <router-link v-if="authenticated" :to="{ name: 'home' }" class="white--text hidden-sm-and-up">
        <v-icon>fas fa-map-signs</v-icon>
      </router-link>
      
      <router-link v-else :to="{ name: 'welcome' }" class="hidden-sm-and-up">
        <v-icon>fas fa-map-signs</v-icon>
      </router-link>
    </v-toolbar-title>
    
    <div class="d-flex align-center" style="margin-left: auto">
      <!-- Authenticated -->
      <template v-if="authenticated">
        <progress-bar :show="busy"></progress-bar>
        <v-btn class="hidden-xs-only" flat :to="{ name: 'settings.profile' }">{{ user.name }}</v-btn>
        <v-btn class="hidden-xs-only" flat @click.prevent="logout">{{ $t('logout') }}</v-btn>
        <v-btn flat @click.prevent="goSetting" icon color="white" class="hidden-sm-and-up" >
          <v-icon>fa-user</v-icon>
        </v-btn>
        <v-btn flat @click.prevent="logout" icon color="white" class="hidden-sm-and-up" >
          <v-icon>fa-sign-in-alt</v-icon>
        </v-btn>
      </template>
      <!-- Guest -->
      <template v-else>
        <v-btn class="hidden-xs-only" flat :to="{ name: 'login' }">{{ $t('login') }}</v-btn>
        <v-btn class="hidden-xs-only" flat :to="{ name: 'register' }">{{ $t('register') }}</v-btn>
        <v-btn flat :to="{ name: 'login' }" icon color="white" class="hidden-sm-and-up" >
          <v-icon>fa-sign-in-alt</v-icon>
        </v-btn>
        <v-btn flat :to="{ name: 'register' }" icon color="white"class="hidden-sm-and-up" >
          <v-icon>fa-user-plus</v-icon>
        </v-btn>
      </template>
    </div>
  </v-toolbar>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  props: {
    drawer: {
      type: Boolean,
      required: true
    }
  },

  data: () => ({
    appName: window.config.appName,
    busy: false
  }),

  computed: mapGetters({
    user: 'authUser',
    authenticated: 'authCheck'
  }),

  methods: {
    toggleDrawer () {
      this.$emit('toggleDrawer')
    },
    async logout () {
      if(this.authenticated){
        this.busy = true
  
        if (this.drawer) {
          this.toggleDrawer()
        }
  
        // Log out the user.
        await this.$store.dispatch('logout')
        this.busy = false
  
        // Redirect to login.
        this.$router.push({ name: 'login' })
      }
    },
    goSetting(){
      this.$router.push({ name: 'settings.profile' })
    }
  }
}
</script>

<style lang="stylus" scoped>

.toolbar__title .router-link-active
  text-decoration: none

</style>
