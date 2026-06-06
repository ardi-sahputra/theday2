import { Capacitor } from '@capacitor/core';
import { PushNotifications } from '@capacitor/push-notifications';
import { createHttp } from '../lib/http';

/**
 * Registers for push, sends the FCM token to the backend, and routes
 * notification taps to the screen carried in the payload's `route`.
 */
export async function initPush(router, http = createHttp()) {
  if (!Capacitor.isNativePlatform()) return;

  let permission = await PushNotifications.checkPermissions();
  if (permission.receive === 'prompt') {
    permission = await PushNotifications.requestPermissions();
  }
  if (permission.receive !== 'granted') return; // app stays usable without push

  await PushNotifications.register();

  PushNotifications.addListener('registration', async (token) => {
    try {
      await http.post('/devices', { token: token.value, platform: 'android' });
    } catch { /* will re-register on next resume */ }
  });

  PushNotifications.addListener('pushNotificationActionPerformed', (action) => {
    const route = action?.notification?.data?.route;
    if (route) router.push(route);
  });
}
