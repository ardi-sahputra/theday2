import { ref } from 'vue';

/**
 * Data composable for the Home summary module.
 * Surface-agnostic: the caller injects an http client
 * (Bearer client in the app, axios in the web wrapper).
 */
export function useHomeSummary({ http }) {
  const summary = ref(null);
  const loading = ref(false);

  async function load() {
    loading.value = true;
    try {
      summary.value = await http.get('/home/summary');
    } finally {
      loading.value = false;
    }
  }

  return { summary, loading, load };
}
