import Vue from 'vue'
import VeeValidate,{ Validator } from 'vee-validate'
import es from 'vee-validate/dist/locale/es';

Validator.addLocale(es);

Vue.use(VeeValidate, { delay: 250, locale:'es'})

Vue.mixin({
  $_veeValidate: {
    validator: 'new'
  },
  methods: {
    async formHasErrors () {
      const valid = await this.$validator.validateAll()
      
      if (valid) {
        this.$validator.pause()
      }
      return !valid
    }
  }
})
