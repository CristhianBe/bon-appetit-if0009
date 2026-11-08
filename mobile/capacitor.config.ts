import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'cr.ac.ucr.bonappetit',
  appName: 'Bon Appetit',
  webDir: 'dist',
  server: {
    // SOLO DESARROLLO: el backend local corre en HTTP. Con el esquema por defecto
    // (https://localhost) la WebView bloquearía las llamadas http:// como "mixed content",
    // y Android bloquea el tráfico cleartext si no se habilita explícitamente.
    androidScheme: 'http',
    cleartext: true,
  },
  plugins: {
    LocalNotifications: {
      iconColor: '#b5442c',
    },
  },
};

export default config;
