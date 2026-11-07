import { SecureStoragePlugin } from 'capacitor-secure-storage-plugin';

/**
 * Envoltorio sobre capacitor-secure-storage-plugin: en Android guarda los valores cifrados
 * con Android Keystore y en iOS en el Keychain. Acá va SOLO lo sensible (el token);
 * nunca en @capacitor/preferences ni localStorage, que guardan en texto plano.
 */
export async function leerSeguro(clave: string): Promise<string | null> {
  try {
    const { value } = await SecureStoragePlugin.get({ key: clave });
    return value;
  } catch {
    // El plugin rechaza la promesa cuando la clave no existe.
    return null;
  }
}

export async function guardarSeguro(clave: string, valor: string): Promise<void> {
  await SecureStoragePlugin.set({ key: clave, value: valor });
}

export async function borrarSeguro(clave: string): Promise<void> {
  try {
    await SecureStoragePlugin.remove({ key: clave });
  } catch {
    // Si no existía, no hay nada que borrar.
  }
}
