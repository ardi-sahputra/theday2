<template>
  <ion-page>
    <ion-header><ion-toolbar><ion-title>Home</ion-title></ion-toolbar></ion-header>
    <ion-content class="ion-padding">
      <home-summary :http="http" />
    </ion-content>
  </ion-page>
</template>

<script setup>
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent } from '@ionic/vue';
import HomeSummary from '@/modules/home-summary/HomeSummary.vue';
import { createHttp } from '../lib/http';

const http = createHttp();
</script>
