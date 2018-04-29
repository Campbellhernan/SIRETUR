<template>
  <v-layout row>
    <v-flex >
      <v-card>
        <progress-bar :show="busy"></progress-bar>
        <v-card-title primary-title class="grey lighten-4">
          <div class="display-1 dark-1 grey--text text-xs-center">{{ $t('settings') }}</div>
        </v-card-title>
        <v-tabs icons 
                centered 
                fixed 
                color="grey lighten-4" 
                slider-color="primary"  
                v-model="model">
          <v-tab href="#tab-person">
            <v-icon>person</v-icon>
            {{ $t('profile') }}
          </v-tab>
          <v-tab href="#tab-password">
            <v-icon>lock</v-icon>
            {{ $t('password') }}
          </v-tab>
        </v-tabs>
        <v-divider></v-divider>
        <div xs12 sm8 offset-sm2 lg4 offset-lg4>
          <v-tabs-items v-model="model">
            <v-tab-item id="tab-person">
              <profile-view v-on:busy="busy = $event"></profile-view>
            </v-tab-item>
            <v-tab-item id="tab-password">
              <password-view v-on:busy="busy = $event"></password-view>
            </v-tab-item>
          </v-tabs-items>
        </div>
      </v-card>
    </v-flex>
  </v-layout>
</template>

<script>
import Profile from '~/pages/settings/profile'
import Password from '~/pages/settings/password'

export default {
  name: 'settings-view',
  metaInfo () {
    return { title: this.$t('settings') }
  },
  components: {
    'profile-view': Profile,
    'password-view': Password
  },
  data () {
    return {
      busy: false,
      model: 'tab-person',
    }
  }
}
</script>
