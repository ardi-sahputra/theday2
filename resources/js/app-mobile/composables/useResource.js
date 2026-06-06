import { ref } from 'vue';
import { getItem, setItem } from '../lib/storage';

/**
 * Stale-while-revalidate resource with optimistic mutation.
 * @param {string} key  cache key (namespaced under "cache.")
 * @param {() => Promise<any>} fetcher  network read
 */
export function useResource(key, fetcher) {
  const cacheKey = `cache.${key}`;
  const data = ref(null);
  const loading = ref(false);
  const error = ref(null);

  async function load() {
    // 1) surface cached value instantly (stale) on first load
    if (data.value === null) {
      const cached = await getItem(cacheKey);
      if (cached !== null) data.value = cached;
    }
    // 2) always revalidate in the same call (stale-while-revalidate)
    loading.value = true;
    error.value = null;
    try {
      const fresh = await fetcher();
      data.value = fresh;
      await setItem(cacheKey, fresh);
    } catch (e) {
      error.value = e;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Apply an optimistic update, then commit to the server.
   * @param {(current:any)=>any} apply  pure transform of current data
   * @param {()=>Promise<any>} commit  network write; returns canonical state
   */
  async function mutate(apply, commit) {
    const previous = data.value;
    data.value = apply(previous);
    try {
      const canonical = await commit();
      if (canonical !== undefined) data.value = canonical;
      await setItem(cacheKey, data.value);
    } catch (e) {
      data.value = previous; // rollback
      throw e;
    }
  }

  return { data, loading, error, load, mutate };
}
