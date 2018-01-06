<template>
      <v-navigation-drawer
      fixed
      clipped
      app
      hide-overlay
    >
    <v-list>
      <template  v-for="(item, i) in items">
        <v-layout
          row
          v-if="item.heading && item.permiso"
          align-center
          :key="i"
        >
          <v-subheader v-if="item.heading && item.permiso">
            {{ item.heading }}
          </v-subheader>
        </v-layout>
        <v-divider
          dark
          v-else-if="item.divider && item.permiso"
          class="my-4"
          :key="i"
        ></v-divider>
        <v-list-tile
        v-else-if="item.permiso"
          value="true"
          :key="i"
          :to="item.route">
          <v-list-tile-action >
            <v-icon light v-html="item.icon"></v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title v-text="item.title"></v-list-tile-title>
          </v-list-tile-content>
        </v-list-tile>
      </template>
    </v-list>
 </v-navigation-drawer>
</template>

<script>
import { mapGetters } from 'vuex'
export default {
  data () {
    return {
      name: this.$t('nav_menu_title'),
      items: []
    }
  },
  computed: mapGetters({
    user: 'authUser',
    authenticated: 'authCheck'
  }),
  created(){
    this.items = [
        { title: 'Inicio', icon: 'home', route: { name: 'home' }, permiso:true},
        { title: 'Recomendaciones para ti', icon: 'grade', route: { name: 'recommendation'}, permiso:true},
        { title: 'Cuenta', icon: 'account_box', route: { name: 'settings.profile' },permiso:true },
        { divider: this.obtenerPermiso() },
        { heading: 'Gestor de contenido', permiso:this.obtenerPermiso()},
        { title: 'Añadir contenido', icon: 'add_location', route: { name: 'append' }, permiso:this.obtenerPermiso() },
        { title: 'Clasificar contenido', icon: 'storage', route: { name: 'cluster' }, permiso:this.obtenerPermiso() },
      ]
  },
  methods:{
    obtenerPermiso: function(){
      return this.user.perfil =='Experto' || this.user.perfil == 'Administrador';
    }
  }
}
</script>
