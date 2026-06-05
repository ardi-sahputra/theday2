import { describe, it, expect, vi, beforeEach } from 'vitest';

const store = new Map();
vi.mock('@capacitor/preferences', () => ({
  Preferences: {
    get: vi.fn(async ({ key }) => ({ value: store.has(key) ? store.get(key) : null })),
    set: vi.fn(async ({ key, value }) => { store.set(key, value); }),
    remove: vi.fn(async ({ key }) => { store.delete(key); }),
  },
}));

import { getItem, setItem, removeItem } from './storage';

describe('storage', () => {
  beforeEach(() => store.clear());

  it('round-trips JSON values', async () => {
    await setItem('k', { a: 1 });
    expect(await getItem('k')).toEqual({ a: 1 });
  });

  it('returns null for missing keys', async () => {
    expect(await getItem('missing')).toBeNull();
  });

  it('removes values', async () => {
    await setItem('k', 1);
    await removeItem('k');
    expect(await getItem('k')).toBeNull();
  });
});
