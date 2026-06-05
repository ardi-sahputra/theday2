import { describe, it, expect, vi, beforeEach } from 'vitest';

const store = new Map();
vi.mock('@capacitor/preferences', () => ({
  Preferences: {
    get: vi.fn(async ({ key }) => ({ value: store.get(key) ?? null })),
    set: vi.fn(async ({ key, value }) => { store.set(key, value); }),
    remove: vi.fn(async ({ key }) => { store.delete(key); }),
  },
}));

import { useAuth } from './useAuth';

function httpStub(overrides = {}) {
  return {
    post: vi.fn(async () => ({ token: 'tok', user: { id: 'u1', email: 'a@b.c' } })),
    get: vi.fn(async () => ({ user: { id: 'u1', email: 'a@b.c' } })),
    ...overrides,
  };
}

describe('useAuth', () => {
  beforeEach(() => store.clear());

  it('login stores token and sets user', async () => {
    const http = httpStub();
    const auth = useAuth({ http });

    await auth.login('a@b.c', 'password', 'pixel');

    expect(auth.isAuthenticated.value).toBe(true);
    expect(auth.user.value.email).toBe('a@b.c');
    expect(store.get('auth.token')).toBe(JSON.stringify('tok'));
  });

  it('bootstrap with no token leaves user null', async () => {
    const http = httpStub();
    const auth = useAuth({ http });

    await auth.bootstrap();

    expect(auth.isAuthenticated.value).toBe(false);
    expect(http.get).not.toHaveBeenCalled();
  });

  it('bootstrap with a stored token fetches the user', async () => {
    store.set('auth.token', JSON.stringify('tok'));
    const http = httpStub();
    const auth = useAuth({ http });

    await auth.bootstrap();

    expect(http.get).toHaveBeenCalledWith('/me');
    expect(auth.isAuthenticated.value).toBe(true);
  });

  it('logout clears token and user', async () => {
    store.set('auth.token', JSON.stringify('tok'));
    const http = httpStub();
    const auth = useAuth({ http });
    await auth.bootstrap();

    await auth.logout();

    expect(auth.isAuthenticated.value).toBe(false);
    expect(store.has('auth.token')).toBe(false);
  });
});
