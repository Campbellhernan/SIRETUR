<template>
  <div class="google-map" :id="mapName"></div>
</template>

<script>
export default {
  name: 'google-map',
  props: ['name'],
  data: function () {
    return {
      mapName: this.name + "-map",
    }
  }
};
</script>

<style scoped>
.google-map {
    height: 100%;
    width: 100%;
    position: absolute;
    top: 0;
    left: 0;
  background: gray;
}
</style>