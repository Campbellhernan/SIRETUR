<template>
        <grid-card></grid-card>
</template>

<script>
import { mapGetters } from 'vuex'
import GridCard from '~/components/GridCard'

export default {
      components: {
    'grid-card': GridCard,
  },
  name: 'search-view',
  metaInfo () {
    return { title: this.$t('search') }
  },
  data: () => ({
    title: window.config.appName
  })
}
</script>
