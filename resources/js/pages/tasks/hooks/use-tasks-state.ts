import type { GanttFeature, GanttStatus } from '@/components/gantt-view';
import type { DragEndEvent } from '@/components/list-view';
import tasksRoutes from '@/routes/tasks';
import { router } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import { toast } from 'sonner';
import type { CalendarTask, KanbanTask, ListTask, Status, Task } from '../types';

const toISODate = (date: Date): string => {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
};

const parseDate = (iso: string | null | undefined): Date => {
    const d = iso ? new Date(iso) : new Date();
    return Number.isNaN(d.getTime()) ? new Date() : d;
};

type UseTasksStateProps = {
    tasks: Task[];
    statuses: Status[];
};

export function useTasksState({ tasks, statuses }: UseTasksStateProps) {
    // List view state
    const [listTasks, setListTasks] = useState<ListTask[]>([]);

    // Kanban view state
    const [kanbanTasks, setKanbanTasks] = useState<KanbanTask[]>([]);

    // Calendar view state
    const [calendarMonth, setCalendarMonth] = useState<Date>(() => {
        const now = new Date();
        return new Date(now.getFullYear(), now.getMonth(), 1);
    });
    const [calendarTasks, setCalendarTasks] = useState<CalendarTask[]>([]);

    // Gantt view state
    const ganttStatuses: GanttStatus[] = useMemo(() => statuses.map((s) => ({ id: s.id, name: s.name, color: s.color })), [statuses]);
    const [ganttFeatures, setGanttFeatures] = useState<GanttFeature[]>([]);

    // Sync all view states when tasks prop changes (intentional state sync)
    /* eslint-disable react-hooks/set-state-in-effect -- Syncing derived state from props is intentional */
    useEffect(() => {
        const todayISO = toISODate(new Date());

        // Build list tasks
        const newListTasks = tasks.map((task) => ({
            id: task.id,
            name: task.title,
            statusId: task.status_id,
        }));
        setListTasks(newListTasks);

        // Build kanban tasks from list tasks
        setKanbanTasks(
            newListTasks.map((task) => ({
                id: task.id,
                name: task.name,
                column: task.statusId,
            })),
        );

        // Build calendar tasks
        setCalendarTasks(
            tasks.map((task) => {
                const date = task.due_date ? new Date(task.due_date) : null;
                const dueDateISO = date && !Number.isNaN(date.getTime()) ? toISODate(date) : todayISO;

                return {
                    id: task.id,
                    title: task.title,
                    dueDateISO,
                    statusId: task.status_id,
                };
            }),
        );

        // Build gantt features
        if (ganttStatuses.length === 0) {
            setGanttFeatures([]);
            return;
        }

        setGanttFeatures(
            tasks.map((task) => {
                const due = parseDate(task.due_date);
                const startAt = new Date(due.getFullYear(), due.getMonth(), due.getDate() - 2);
                const endAt = new Date(due.getFullYear(), due.getMonth(), due.getDate() + 1);
                const status = ganttStatuses.find((s) => s.id === task.status_id) ?? ganttStatuses[0]!;

                return {
                    id: task.id,
                    name: task.title,
                    startAt,
                    endAt,
                    status,
                    lane: status.id,
                };
            }),
        );
    }, [tasks, ganttStatuses]);
    /* eslint-enable react-hooks/set-state-in-effect */

    // Persist task update to backend
    const persistTaskUpdate = (taskId: string, data: Record<string, unknown>) => {
        const route = tasksRoutes.update(taskId);
        router.patch(route.url, data, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    // Handle drag-and-drop between statuses (syncs list + kanban + persists)
    const handleListDragEnd = ({ active, over }: DragEndEvent) => {
        if (!over) return;

        const fromStatusId = active.data.current?.parent as string | undefined;
        const toStatusId = String(over.id);

        if (!fromStatusId || fromStatusId === toStatusId) return;

        const activeId = String(active.id);

        setListTasks((current) => current.map((task) => (task.id === activeId ? { ...task, statusId: toStatusId } : task)));

        setKanbanTasks((current) => current.map((task) => (task.id === activeId ? { ...task, column: toStatusId } : task)));

        // Persist to backend
        persistTaskUpdate(activeId, { status_id: toStatusId });
    };

    // Handle kanban data change (persists status changes)
    const handleKanbanDataChange = (next: KanbanTask[], prevData: KanbanTask[]) => {
        setKanbanTasks(next);
        setListTasks(
            next.map((task) => ({
                id: task.id,
                name: task.name,
                statusId: task.column,
            })),
        );

        // Find tasks whose column changed and persist
        for (const task of next) {
            const prev = prevData.find((t) => t.id === task.id);
            if (prev && prev.column !== task.column) {
                persistTaskUpdate(task.id, { status_id: task.column });
            }
        }
    };

    // Handle calendar task date change (persists due_date)
    const handleCalendarTaskMove = (taskId: string, newDueDateISO: string) => {
        setCalendarTasks((current) => current.map((t) => (t.id === taskId ? { ...t, dueDateISO: newDueDateISO } : t)));

        // Persist to backend
        const route = tasksRoutes.update(taskId);
        router.patch(route.url, { due_date: newDueDateISO }, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => toast.success('Due date updated'),
        });
    };

    // Handle gantt feature move (persists due_date)
    const handleGanttMove = (id: string, startAt: Date, endAt: Date) => {
        setGanttFeatures((current) => current.map((f) => (f.id === id ? { ...f, startAt, endAt } : f)));

        // Persist the due_date (end date) to backend
        const dueDateISO = toISODate(endAt);
        const route = tasksRoutes.update(id);
        router.patch(route.url, { due_date: dueDateISO }, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => toast.success('Due date updated'),
        });
    };

    return {
        // List
        listTasks,
        handleListDragEnd,

        // Kanban
        kanbanTasks,
        handleKanbanDataChange,

        // Calendar
        calendarMonth,
        setCalendarMonth,
        calendarTasks,
        handleCalendarTaskMove,

        // Gantt
        ganttStatuses,
        ganttFeatures,
        handleGanttMove,
    };
}
