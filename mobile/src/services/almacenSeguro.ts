import { SecureStoragePlugin } from 'capacitor-secure-storage-plugin';

export async function leerSeguro(clave: string): Promise<string | null> {
  try {
    const { value } = await SecureStoragePlugin.get({ key: clave });
    return value;
  } catch {
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
  }
}
