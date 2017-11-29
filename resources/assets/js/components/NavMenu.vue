<template>
      <v-navigation-drawer
      fixed
      clipped
      app

    >
    <v-list>
      <v-list-tile
        value="true"
        v-for="(item, i) in items"
        :key="i"
        :to="item.route"
      >
        <v-list-tile-action>
          <v-icon light v-html="item.icon"></v-icon>
        </v-list-tile-action>
        <v-list-tile-content>
          <v-list-tile-title v-text="item.title"></v-list-tile-title>
        </v-list-tile-content>
      </v-list-tile>
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
        { title: 'Recomendaciones para ti', icon: 'grade', route: { name: 'search' } },
        { title: 'Cuenta', icon: 'account_box', route: { name: 'settings.profile' } },
      ]
    }
  }
}
</script>
