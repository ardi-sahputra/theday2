<template>
  <ion-page>
    <ion-content class="ion-padding">
      <h1>Masuk</h1>
      <ion-input v-model="email" label="Email" label-placement="floating" type="email" />
      <ion-input v-model="password" label="Password" label-placement="floating" type="password" />
      <ion-button expand="block" :disabled="busy" @click="submit">Masuk</ion-button>
      <p v-if="err" class="err">{{ err }}</p>
    </ion-content>
  </ion-page>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { IonPage, IonContent, IonInput, IonButton } from '@ionic/vue';
import { useAuth } from '../composables/useAuth';

const email = ref('');
const password = ref('');
const err = ref('');
const busy = ref(false);
const router = useRouter();
const auth = useAuth();

async function submit() {
  busy.value = true;
  err.value = '';
  try {
    await auth.login(email.value, password.value, 'android-device');
    router.replace('/tabs/home');
  } catch (e) {
    err.value = 'Email atau password salah.';
  } finally {
    busy.value = false;
  }
}
</script>

<style scoped>.err { color: var(--ion-color-danger); }</style>
