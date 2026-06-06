import { Preferences } from '@capacitor/preferences';

export async function getItem(key) {
  const { value } = await Preferences.get({ key });
  if (value == null) return null;
  try {
    return JSON.parse(value);
  } catch {
    return value;
  }
}

export async function setItem(key, value) {
  await Preferences.set({ key, value: JSON.stringify(value) });
}

export async function removeItem(key) {
  await Preferences.remove({ key });
}
