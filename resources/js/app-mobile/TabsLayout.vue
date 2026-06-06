<template>
  <ion-tabs>
    <ion-router-outlet />
    <ion-tab-bar slot="bottom">
      <ion-tab-button tab="home" href="/tabs/home">
        <ion-icon :icon="homeOutline" /><ion-label>Home</ion-label>
      </ion-tab-button>
      <ion-tab-button tab="undangan" href="/tabs/undangan">
        <ion-icon :icon="mailOutline" /><ion-label>Undangan</ion-label>
      </ion-tab-button>
      <ion-tab-button tab="budget" href="/tabs/budget">
        <ion-icon :icon="walletOutline" /><ion-label>Budget</ion-label>
      </ion-tab-button>
      <ion-tab-button tab="planner" href="/tabs/planner">
        <ion-icon :icon="checkboxOutline" /><ion-label>Planner</ion-label>
      </ion-tab-button>
      <ion-tab-button tab="more" href="/tabs/more">
        <ion-icon :icon="ellipsisHorizontal" /><ion-label>More</ion-label>
      </ion-tab-button>
    </ion-tab-bar>
  </ion-tabs>
</template>

<script setup>
import { IonTabs, IonRouterOutlet, IonTabBar, IonTabButton, IonIcon, IonLabel } from '@ionic/vue';
import { homeOutline, mailOutline, walletOutline, checkboxOutline, ellipsisHorizontal } from 'ionicons/icons';
</script>
