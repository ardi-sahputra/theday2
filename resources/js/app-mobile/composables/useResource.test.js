import { describe, it, expect, vi, beforeEach } from 'vitest';
import { flushPromises } from '@vue/test-utils';

const store = new Map();
vi.mock('@capacitor/preferences', () => ({
  Preferences: {
    get: vi.fn(async ({ key }) => ({ value: store.get(key) ?? null })),
    set: vi.fn(async ({ key, value }) => { store.set(key, value); }),
    remove: vi.fn(async ({ key }) => { store.delete(key); }),
  },
}));

import { useResource } from './useResource';

describe('useResource', () => {
  beforeEach(() => store.clear());

  it('surfaces cached data instantly then revalidates within one load()', async () => {
    store.set('cache.items', JSON.stringify([{ id: 1 }]));
    // Deferred fetcher so we can observe the stale value before fresh resolves.
    let resolveFetch;
    const fetcher = vi.fn(() => new Promise((res) => { resolveFetch = res; }));

    const r = useResource('items', fetcher);
    const pending = r.load(); // do not await yet

    await flushPromises(); // cache read resolves, fetcher invoked but still pending
    expect(r.data.value).toEqual([{ id: 1 }]); // stale surfaced
    expect(fetcher).toHaveBeenCalledOnce();

    resolveFetch([{ id: 1 }, { id: 2 }]);
    await pending;
    expect(r.data.value).toEqual([{ id: 1 }, { id: 2 }]); // revalidated
  });

  it('optimistic mutate applies locally then reconciles', async () => {
    const fetcher = vi.fn(async () => [{ id: 1, done: false }]);
    const r = useResource('items', fetcher);
    await r.load();

    const commit = vi.fn(async () => [{ id: 1, done: true }]);
    await r.mutate((cur) => cur.map((x) => ({ ...x, done: true })), commit);

    expect(commit).toHaveBeenCalledOnce();
    expect(r.data.value).toEqual([{ id: 1, done: true }]);
  });

  it('rolls back when commit fails', async () => {
    const fetcher = vi.fn(async () => [{ id: 1, done: false }]);
    const r = useResource('items', fetcher);
    await r.load();

    const commit = vi.fn(async () => { throw new Error('boom'); });
    await expect(
      r.mutate((cur) => cur.map((x) => ({ ...x, done: true })), commit),
    ).rejects.toThrow('boom');

    expect(r.data.value).toEqual([{ id: 1, done: false }]);
  });
});
