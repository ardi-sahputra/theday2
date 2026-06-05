import { getItem } from './storage';

export const TOKEN_KEY = 'auth.token';

export function createHttp({
  fetch = globalThis.fetch,
  baseUrl = '/api',
  retries = 1,
  retryDelay = 300,
  onUnauthorized = () => {},
} = {}) {
  async function request(method, path, body) {
    const token = await getItem(TOKEN_KEY);
    const headers = { 'Content-Type': 'application/json', Accept: 'application/json' };
    if (token) headers.Authorization = `Bearer ${token}`;

    let attempt = 0;
    // eslint-disable-next-line no-constant-condition
    while (true) {
      const res = await fetch(`${baseUrl}${path}`, {
        method,
        headers,
        body: body !== undefined ? JSON.stringify(body) : undefined,
      });

      if (res.status === 401) {
        onUnauthorized();
        throw new Error('Unauthorized');
      }

      if (res.status >= 500 && attempt < retries) {
        attempt += 1;
        if (retryDelay) await new Promise((r) => setTimeout(r, retryDelay));
        continue;
      }

      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res.json();
    }
  }

  return {
    get: (path) => request('GET', path),
    post: (path, body) => request('POST', path, body),
    patch: (path, body) => request('PATCH', path, body),
    put: (path, body) => request('PUT', path, body),
    delete: (path, body) => request('DELETE', path, body),
  };
}
