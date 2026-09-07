import { describe, it, expect } from 'vitest';
import { selectFocusTasks } from './selectFocusTasks';

// Fixed "today" so due-date math is deterministic.
const NOW = new Date('2026-06-14T09:00:00');

// Helper: build a task with a due date N days from NOW (null = no date).
function task(id, offsetDays, extra = {}) {
  let due = null;
  if (offsetDays !== null) {
    const d = new Date('2026-06-14T00:00:00');
    d.setDate(d.getDate() + offsetDays);
    due = d.toISOString().slice(0, 10);
  }
  return { id, due_date: due, status: 'todo', priority: 'medium', ...extra };
}

describe('selectFocusTasks', () => {
  it('returns due-soon tasks (overdue+today+week) in normal mode', () => {
    const tasks = [
      task(1, -3),  // overdue
      task(2, 0),   // today
      task(3, 5),   // this week
      task(4, 20),  // month — excluded when enough due-soon
      task(5, 60),  // later — excluded
    ];
    const { tasks: focus, mode } = selectFocusTasks(tasks, { now: NOW, minCount: 3 });
    expect(mode).toBe('normal');
    expect(focus.map(t => t.id)).toEqual([1, 2, 3]);
  });

  it('sorts overdue before today before week, by due date', () => {
    const tasks = [task(3, 6), task(1, -5), task(2, -1)];
    const { tasks: focus } = selectFocusTasks(tasks, { now: NOW, minCount: 2 });
    expect(focus.map(t => t.id)).toEqual([1, 2, 3]);
  });

  it('tops up from the month bucket when due-soon is below minCount', () => {
    const tasks = [task(1, 0), task(2, 15), task(3, 25), task(4, 60)];
    const { tasks: focus, mode } = selectFocusTasks(tasks, { now: NOW, minCount: 3 });
    expect(mode).toBe('normal');
    expect(focus.map(t => t.id)).toEqual([1, 2, 3]); // today + two from month, later excluded
  });

  it('caps and switches to overdueHeavy when overdue exceeds displayCap', () => {
    const tasks = Array.from({ length: 10 }, (_, i) => task(i + 1, -(i + 1)));
    const { tasks: focus, mode } = selectFocusTasks(tasks, { now: NOW, displayCap: 8, overdueOnlyCap: 6 });
    expect(mode).toBe('overdueHeavy');
    expect(focus).toHaveLength(6);
  });

  it('is relaxed and shows upcoming month tasks when nothing is due soon', () => {
    const tasks = [task(1, 20), task(2, 25), task(3, 90)];
    const { tasks: focus, mode } = selectFocusTasks(tasks, { now: NOW });
    expect(mode).toBe('relaxed');
    expect(focus.map(t => t.id)).toEqual([1, 2]); // month bucket, later excluded
  });

  it('falls back to highest-priority undated tasks when nothing is scheduled', () => {
    const tasks = [
      task(1, null, { priority: 'low' }),
      task(2, null, { priority: 'high' }),
      task(3, null, { priority: 'medium' }),
    ];
    const { tasks: focus, mode } = selectFocusTasks(tasks, { now: NOW });
    expect(mode).toBe('relaxed');
    expect(focus[0].id).toBe(2); // high priority first
  });

  it('ignores done and archived tasks', () => {
    const tasks = [
      task(1, -1, { status: 'done' }),
      task(2, 0, { status: 'archived' }),
      task(3, 2),
    ];
    const { tasks: focus } = selectFocusTasks(tasks, { now: NOW });
    expect(focus.map(t => t.id)).toEqual([3]);
  });

  it('returns an empty set with relaxed mode when there are no active tasks', () => {
    const { tasks: focus, mode } = selectFocusTasks([], { now: NOW });
    expect(focus).toEqual([]);
    expect(mode).toBe('relaxed');
  });
});
