import { Capacitor } from '@capacitor/core';
import { StatusBar, Style } from '@capacitor/status-bar';
import { SplashScreen } from '@capacitor/splash-screen';
import { Keyboard, KeyboardResize } from '@capacitor/keyboard';
import { App as CapApp } from '@capacitor/app';
import { Haptics, ImpactStyle } from '@capacitor/haptics';

export async function initNative(router) {
  if (!Capacitor.isNativePlatform()) return;

  try {
    await StatusBar.setStyle({ style: Style.Dark });
    await Keyboard.setResizeMode({ mode: KeyboardResize.Native });
  } catch { /* plugin unavailable in some contexts */ }

  // Android hardware back: navigate history, exit only at a tab root.
  CapApp.addListener('backButton', ({ canGoBack }) => {
    const atTabRoot = /^\/tabs\/(home|undangan|budget|planner|more)$/.test(router.currentRoute.value.path);
    if (canGoBack && !atTabRoot) {
      router.back();
    } else {
      CapApp.exitApp();
    }
  });
}

export function hideSplash() {
  SplashScreen.hide().catch(() => {});
}

export async function tapFeedback() {
  try { await Haptics.impact({ style: ImpactStyle.Light }); } catch { /* no-op */ }
}
