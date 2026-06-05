import { createApp } from 'vue';
import { IonicVue } from '@ionic/vue';
import App from './App.vue';
import { router } from './router';
import { useAuth } from './composables/useAuth';
import { initNative, hideSplash } from './native';
import { initPush } from './native/push';

import '@ionic/vue/css/core.css';
import '@ionic/vue/css/normalize.css';
import '@ionic/vue/css/structure.css';
import '@ionic/vue/css/typography.css';

const app = createApp(App).use(IonicVue).use(router);

router.isReady().then(async () => {
  const auth = useAuth();
  await initNative(router);
  await auth.bootstrap();
  if (!auth.isAuthenticated.value) {
    router.replace('/login');
  } else {
    await initPush(router);
  }
  app.mount('#app');
  hideSplash();
});
