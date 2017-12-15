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
          v-if="item.heading"
          align-center
          :key="i"
        >
          <v-subheader v-if="item.heading">
            {{ item.heading }}
          </v-subheader>
        </v-layout>
        <v-divider
          dark
          v-else-if="item.divider"
          class="my-4"
          :key="i"
        ></v-divider>
        <v-list-tile
        v-else
          value="true"
          :key="i"
          :to="item.route"
        >
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
export default {
  data () {
    return {
      name: this.$t('nav_menu_title'),
      items: [
        { title: 'Inicio', icon: 'dashboard', route: { name: 'home' } },
        { title: 'Recomendaciones para ti', icon: 'grade', route: { name: 'recommendation' } },
        { title: 'Cuenta', icon: 'account_box', route: { name: 'settings.profile' } },
        { divider: true },
        { heading: 'Gestor de contenido' },
        { title: 'Añadir contenido', icon: 'add_location', route: { name: 'append' } },
        { title: 'Clasificar contenido', icon: 'storage', route: { name: 'cluster' } },
      ]
    }
  }
}
</script>
