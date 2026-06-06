import { ref, computed } from 'vue';
import { getItem, setItem, removeItem } from '../lib/storage';
import { TOKEN_KEY, createHttp } from '../lib/http';

const user = ref(null);

export function useAuth({ http = createHttp() } = {}) {
  const isAuthenticated = computed(() => user.value !== null);

  async function login(email, password, deviceName) {
    const res = await http.post('/auth/login', { email, password, device_name: deviceName });
    await setItem(TOKEN_KEY, res.token);
    user.value = res.user;
    return res.user;
  }

  async function register(payload) {
    const res = await http.post('/auth/register', payload);
    await setItem(TOKEN_KEY, res.token);
    user.value = res.user;
    return res.user;
  }

  async function bootstrap() {
    const token = await getItem(TOKEN_KEY);
    if (!token) {
      user.value = null;
      return;
    }
    const res = await http.get('/me');
    user.value = res.user;
  }

  async function logout() {
    try {
      await http.post('/auth/logout');
    } catch {
      // even if the call fails, clear local session
    }
    await removeItem(TOKEN_KEY);
    user.value = null;
  }

  return { user, isAuthenticated, login, register, bootstrap, logout };
}
