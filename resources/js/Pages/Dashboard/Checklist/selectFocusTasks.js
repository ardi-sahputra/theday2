// Pure focus-selection for the checklist "Fokus Sekarang" view. No Vue deps so
// it is trivially unit-testable. Mirrors the deadline bucketing used by
// Index.vue's deadlineGroups (overdue / today / week / month / later).

const PRIORITY_ORDER = { high: 0, medium: 1, low: 2 };

function byDueThenPriority(a, b) {
  if (a.due_date && b.due_date) {
    const diff = new Date(a.due_date) - new Date(b.due_date);
    if (diff !== 0) return diff;
  } else if (a.due_date && !b.due_date) {
    return -1;
  } else if (!a.due_date && b.due_date) {
    return 1;
  }
  return (PRIORITY_ORDER[a.priority] ?? 1) - (PRIORITY_ORDER[b.priority] ?? 1);
}

function byPriority(a, b) {
  return (PRIORITY_ORDER[a.priority] ?? 1) - (PRIORITY_ORDER[b.priority] ?? 1);
}

/**
 * @param {Array} tasks  active task objects ({ id, due_date, status, priority })
 * @param {object} opts  { now, minCount, displayCap, overdueOnlyCap }
 * @returns {{ tasks: Array, mode: 'normal'|'overdueHeavy'|'relaxed' }}
 */
export function selectFocusTasks(tasks, opts = {}) {
  const {
    now = new Date(),
    minCount = 5,
    displayCap = 8,
    overdueOnlyCap = 6,
  } = opts;

  const today = new Date(now);
  today.setHours(0, 0, 0, 0);

  const buckets = { overdue: [], today: [], week: [], month: [], later: [] };

  for (const t of tasks) {
    if (t.status === 'done' || t.status === 'archived') continue;
    if (!t.due_date) { buckets.later.push(t); continue; }
    const due = new Date(t.due_date + 'T00:00:00');
    const diff = Math.round((due - today) / 86400000);
    if (diff < 0) buckets.overdue.push(t);
    else if (diff === 0) buckets.today.push(t);
    else if (diff <= 7) buckets.week.push(t);
    else if (diff <= 30) buckets.month.push(t);
    else buckets.later.push(t);
  }

  // Too many overdue tasks is itself overwhelming — show a calm capped slice.
  if (buckets.overdue.length > displayCap) {
    const sorted = [...buckets.overdue].sort(byDueThenPriority);
    return { tasks: sorted.slice(0, overdueOnlyCap), mode: 'overdueHeavy' };
  }

  const dueSoon = [...buckets.overdue, ...buckets.today, ...buckets.week].sort(byDueThenPriority);

  if (dueSoon.length >= minCount) {
    return { tasks: dueSoon.slice(0, displayCap), mode: 'normal' };
  }

  if (dueSoon.length > 0) {
    // Top up from the next window so the list never looks sparse/anxious.
    const topUp = [...buckets.month].sort(byDueThenPriority);
    const combined = [...dueSoon, ...topUp].slice(0, Math.max(minCount, dueSoon.length));
    return { tasks: combined.slice(0, displayCap), mode: 'normal' };
  }

  // Nothing due soon — relaxed. Surface upcoming month tasks, or if nothing is
  // scheduled at all, the highest-priority undated tasks.
  const upcoming = [...buckets.month].sort(byDueThenPriority);
  if (upcoming.length > 0) {
    return { tasks: upcoming.slice(0, displayCap), mode: 'relaxed' };
  }
  const fallback = [...buckets.later].sort(byPriority);
  return { tasks: fallback.slice(0, displayCap), mode: 'relaxed' };
}
