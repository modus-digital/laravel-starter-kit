import type { GanttStatus } from '@/shared/components/gantt-view';
import AppLayout from '@/shared/layouts/app-layout';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import CalendarViewTab from './partials/calendar-view-tab';
import CreateNewTask from './partials/create-new-task';
import GanttViewTab from './partials/gantt-view-tab';
import KanbanViewTab from './partials/kanban-view-tab';
import ListViewTab from './partials/list-view-tab';
import TaskDetailsDialog from './partials/task-details-dialog';
import ViewsTabBar from './partials/views-tab-bar';

import { useState } from 'react';
import { useTasksState, useViewsState } from './hooks';
import type { Status, Task, TaskView } from './types';

type PageProps = SharedData & {
    tasks?: Task[];
    taskViews?: TaskView[];
    statuses?: Status[];
};

export default function Index() {
    const { tasks = [], taskViews = [], statuses = [] } = usePage<PageProps>().props;

    const [isTaskDialogOpen, setIsTaskDialogOpen] = useState(false);
    const [activeTaskId, setActiveTaskId] = useState<string | null>(null);

    const { views, activeViewId, activeView, setActiveViewId, createView, renameView, updateViewStatuses, makeDefaultView, deleteView } =
        useViewsState({
            taskViews,
        });

    const {
        listTasks,
        handleListDragEnd,
        kanbanTasks,
        handleKanbanDataChange,
        calendarMonth,
        setCalendarMonth,
        calendarTasks,
        handleCalendarTaskMove,
        ganttFeatures,
        handleGanttMove,
    } = useTasksState({ tasks, statuses });

    // Use view-specific statuses if available, otherwise fall back to all statuses
    const viewStatuses = activeView?.statuses?.length ? activeView.statuses : statuses;
    const viewStatusIds = useMemo(() => new Set(viewStatuses.map((s) => s.id)), [viewStatuses]);

    // Filter tasks to only include those with statuses in this view
    const viewListTasks = useMemo(() => listTasks.filter((t) => viewStatusIds.has(t.statusId)), [listTasks, viewStatusIds]);
    const viewKanbanTasks = useMemo(() => kanbanTasks.filter((t) => viewStatusIds.has(t.column)), [kanbanTasks, viewStatusIds]);
    const viewCalendarTasks = useMemo(() => calendarTasks.filter((t) => viewStatusIds.has(t.statusId)), [calendarTasks, viewStatusIds]);
    const viewGanttFeatures = useMemo(() => ganttFeatures.filter((f) => viewStatusIds.has(f.status.id)), [ganttFeatures, viewStatusIds]);

    // Compute view-specific gantt statuses
    const viewGanttStatuses: GanttStatus[] = useMemo(() => viewStatuses.map((s) => ({ id: s.id, name: s.name, color: s.color })), [viewStatuses]);

    const activeTask = useMemo(() => (activeTaskId ? (tasks.find((t) => t.id === activeTaskId) ?? null) : null), [activeTaskId, tasks]);

    const openTask = (taskId: string) => {
        setActiveTaskId(taskId);
        setIsTaskDialogOpen(true);
    };

    const { t } = useTranslation();

    const renderViewContent = () => {
        if (viewStatuses.length === 0) {
            return <p className="text-sm text-muted-foreground">{t('tasks.views.no_statuses_found')}</p>;
        }

        switch (activeView?.type) {
            case 'list':
                return <ListViewTab statuses={viewStatuses} listTasks={viewListTasks} onDragEnd={handleListDragEnd} onOpenTask={openTask} />;

            case 'kanban':
                return (
                    <KanbanViewTab
                        statuses={viewStatuses}
                        kanbanTasks={viewKanbanTasks}
                        onDataChange={handleKanbanDataChange}
                        onOpenTask={openTask}
                    />
                );

            case 'calendar':
                return (
                    <CalendarViewTab
                        statuses={viewStatuses}
                        calendarMonth={calendarMonth}
                        setCalendarMonth={setCalendarMonth}
                        calendarTasks={viewCalendarTasks}
                        onMoveTask={handleCalendarTaskMove}
                        onOpenTask={openTask}
                    />
                );

            case 'gantt':
                return (
                    <GanttViewTab
                        ganttStatuses={viewGanttStatuses}
                        ganttFeatures={viewGanttFeatures}
                        onMove={handleGanttMove}
                        onOpenTask={openTask}
                    />
                );

            default:
                return null;
        }
    };

    return (
        <AppLayout>
            <ViewsTabBar
                views={views}
                statuses={statuses}
                activeViewId={activeViewId}
                onActiveViewChange={setActiveViewId}
                onCreateView={createView}
                onRenameView={renameView}
                onUpdateViewStatuses={updateViewStatuses}
                onMakeDefaultView={makeDefaultView}
                onDeleteView={deleteView}
            />

            <div className="px-6 py-4">
                {activeView ? (
                    <div className="rounded-lg border border-border bg-card">
                        <header className="flex items-start justify-between gap-4 border-b border-border p-4">
                            <div className="min-w-0">
                                <h2 className="text-lg font-medium">{activeView.name}</h2>
                                <p className="mt-1 text-sm text-muted-foreground capitalize">
                                    {t('tasks.views.view_type', { type: activeView.type })}
                                </p>
                            </div>

                            <div className="shrink-0">
                                <CreateNewTask />
                            </div>
                        </header>

                        <div className="p-4">{renderViewContent()}</div>
                    </div>
                ) : (
                    <p className="text-sm text-muted-foreground">{t('tasks.views.no_views')}</p>
                )}
            </div>

            <TaskDetailsDialog task={activeTask} statuses={viewStatuses} open={isTaskDialogOpen} onOpenChange={setIsTaskDialogOpen} />
        </AppLayout>
    );
}
