import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'id.theday.app',
  appName: 'TheDay',
  webDir: 'app-dist',
  plugins: {
    SplashScreen: {
      launchShowDuration: 0, // we hide manually once the app is ready
    },
  },
};

export default config;
