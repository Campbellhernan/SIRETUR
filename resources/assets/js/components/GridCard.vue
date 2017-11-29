<template>
  <v-layout column>
    <v-flex xs12 sm6 offset-sm3>
      <v-container fluid grid-list-md class="grey lighten-4">
        <v-layout row wrap>
          <v-flex
            v-bind="{ [`xs${card.flex}`]: true }"
            v-for="card in cards"
            :key="card.title"
          >
            <v-card>
            <v-card-title primary-title>
              <div class="headline" >{{card.title}}</div>
              <div>{{card.text}}</div>
            </v-card-title>
            <v-card-actions>
              <v-btn flat color="primary">Explorar</v-btn>
            </v-card-actions>
            </v-card>
          </v-flex>
        </v-layout>
      </v-container>
    </v-flex>
  </v-layout>
</template>

<script>
  export default {
    data: () => ({
      cards: [
        { title: 'Playas Carabobo', text: '  El estado Carabobo destaca por ser uno de los de mayor importancia económica. En esta región se encuentra el principal puerto marítimo de Venezuela y el 70% del total de su industria manufacturera.', flex: 12 },
        { title: 'Isla Larga', text: 'es uno de los cayos que se encuentran dentro del Parque Nacional San Esteban en la ciudad de Puerto Cabello.', flex: 12 },
        { title: 'Cuevas del Carrao', text: 'Creo que este es uno de los sitios más sabrosos que he visitado en mi vida y queda a una hora de Barquisimeto en la población de Cubiro en el Estado Lara. ', flex: 12 }
      ]
    })
  }
</script>