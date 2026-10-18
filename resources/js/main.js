import { store } from './state/store.js';

export function iniciarApp() {
  console.log('App iniciada', store.getState());
}