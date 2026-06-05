import { describe, it, expect, vi } from 'vitest';
import { useHomeSummary } from './useHomeSummary';

describe('useHomeSummary', () => {
  it('loads summary from the injected http client', async () => {
    const http = { get: vi.fn(async () => ({ greeting_name: 'Ardi', wedding_date: '2026-12-12' })) };
    const m = useHomeSummary({ http });

    await m.load();

    expect(http.get).toHaveBeenCalledWith('/home/summary');
    expect(m.summary.value.greeting_name).toBe('Ardi');
  });
});
