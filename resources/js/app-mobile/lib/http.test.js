import { describe, it, expect, vi, beforeEach } from 'vitest';

const store = new Map();
vi.mock('@capacitor/preferences', () => ({
  Preferences: {
    get: vi.fn(async ({ key }) => ({ value: store.get(key) ?? null })),
    set: vi.fn(async ({ key, value }) => { store.set(key, value); }),
    remove: vi.fn(async ({ key }) => { store.delete(key); }),
  },
}));

import { createHttp, TOKEN_KEY } from './http';

function fakeFetchSequence(responses) {
  let i = 0;
  return vi.fn(async () => {
    const r = responses[Math.min(i++, responses.length - 1)];
    return {
      ok: r.status >= 200 && r.status < 300,
      status: r.status,
      json: async () => r.body ?? {},
    };
  });
}

describe('createHttp', () => {
  beforeEach(() => store.clear());

  it('attaches the Bearer token from storage', async () => {
    store.set(TOKEN_KEY, JSON.stringify('abc123'));
    const fetchMock = fakeFetchSequence([{ status: 200, body: { ok: true } }]);
    const http = createHttp({ fetch: fetchMock, baseUrl: 'http://x' });

    await http.get('/me');

    const [, init] = fetchMock.mock.calls[0];
    expect(init.headers.Authorization).toBe('Bearer abc123');
  });

  it('retries once on a 500 then succeeds', async () => {
    const fetchMock = fakeFetchSequence([
      { status: 500 },
      { status: 200, body: { ok: true } },
    ]);
    const http = createHttp({ fetch: fetchMock, baseUrl: 'http://x', retries: 1, retryDelay: 0 });

    const res = await http.get('/thing');
    expect(res).toEqual({ ok: true });
    expect(fetchMock).toHaveBeenCalledTimes(2);
  });

  it('calls onUnauthorized on a 401', async () => {
    const fetchMock = fakeFetchSequence([{ status: 401 }]);
    const onUnauthorized = vi.fn();
    const http = createHttp({ fetch: fetchMock, baseUrl: 'http://x', onUnauthorized });

    await expect(http.get('/me')).rejects.toThrow();
    expect(onUnauthorized).toHaveBeenCalledOnce();
  });
});
