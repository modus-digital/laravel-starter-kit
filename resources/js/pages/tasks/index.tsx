import {
    GanttFeatureList,
    GanttFeatureListGroup,
    GanttFeatureRow,
    GanttHeader,
    GanttProvider,
    GanttSidebar,
    GanttSidebarGroup,
    GanttSidebarItem,
    GanttTimeline,
    GanttToday,
    type GanttFeature,
    type GanttStatus,
} from '@/components/gantt-view';
import {
    KanbanBoard,
    KanbanCard,
    KanbanCards,
    KanbanHeader,
    KanbanProvider,
} from '@/components/kanban-view';
import {
    ListGroup,
    ListItem,
    ListItems,
    ListProvider,
    type DragEndEvent,
} from '@/components/list-view';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import {
    DndContext,
    DragOverlay,
    rectIntersection,
    useDraggable,
    useDroppable,
    type DragEndEvent as DndDragEndEvent,
    type DragStartEvent as DndDragStartEvent,
} from '@dnd-kit/core';
import { usePage } from '@inertiajs/react';
import { ChevronDown, ChevronLeft, ChevronRight } from 'lucide-react';
import { useState } from 'react';

const Index = () => {
    type TaskDto = {
        id: string;
        title: string;
        status_id?: string | null;
        due_date?: string | null;
    };

    const ViewTypes = ['list', 'kanban', 'calendar', 'gantt'] as const;
    type ViewType = (typeof ViewTypes)[number];

    type TaskView = {
        id: string;
        name: string;
        type: ViewType;
    };

    const statuses = [
        {
            id: 'planned',
            name: 'Planned',
            color: '#6B7280',
        },
        {
            id: 'in-progress',
            name: 'In Progress',
            color: '#F59E0B',
        },
        {
            id: 'completed',
            name: 'Done',
            color: '#10B981',
        },
    ];

    const { tasks } = usePage<SharedData>().props as SharedData & {
        tasks?: TaskDto[];
    };

    const demoTasks: TaskDto[] = [
        {
            id: 'demo-1',
            title: 'Write documentation',
            status_id: 'planned',
            due_date: new Date().toISOString(),
        },
        {
            id: 'demo-2',
            title: 'Setup CI/CD pipeline',
            status_id: 'in-progress',
            due_date: new Date(
                Date.now() + 2 * 24 * 60 * 60 * 1000,
            ).toISOString(),
        },
        {
            id: 'demo-3',
            title: 'Fix critical bug',
            status_id: 'completed',
            due_date: new Date(
                Date.now() - 1 * 24 * 60 * 60 * 1000,
            ).toISOString(),
        },
    ];

    const effectiveTasks = tasks?.length ? tasks : demoTasks;

    const toISODate = (date: Date): string => {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    };

    const startOfMonth = (date: Date): Date =>
        new Date(date.getFullYear(), date.getMonth(), 1);

    const startOfWeekMonday = (date: Date): Date => {
        const day = date.getDay(); // 0 = Sun, 1 = Mon, ...
        const diff = (day + 6) % 7; // Mon => 0, Sun => 6
        return new Date(
            date.getFullYear(),
            date.getMonth(),
            date.getDate() - diff,
        );
    };

    const addDays = (date: Date, days: number): Date =>
        new Date(date.getFullYear(), date.getMonth(), date.getDate() + days);

    type CalendarTask = {
        id: string;
        title: string;
        dueDateISO: string; // YYYY-MM-DD
        statusId: string;
    };

    const [views, setViews] = useState<TaskView[]>([
        { id: 'list', name: 'List View', type: 'list' },
    ]);
    const [activeViewId, setActiveViewId] = useState<string>('list');

    const activeView =
        views.find((view) => view.id === activeViewId) ?? views[0] ?? null;

    const createNewView = (type: ViewType) => {
        const timestamp = Date.now();

        setViews((current) => {
            const countOfType = current.filter((v) => v.type === type).length;
            const nextNumber = countOfType + 1;

            let name: string;

            switch (type) {
                case 'list': {
                    name = `List View ${nextNumber}`;
                    break;
                }
                case 'kanban': {
                    name = `Kanban Board ${nextNumber}`;
                    break;
                }
                case 'calendar': {
                    name = `Calendar ${nextNumber}`;
                    break;
                }
                case 'gantt': {
                    name = `Gantt Chart ${nextNumber}`;
                    break;
                }
            }

            const id = `${type}-${timestamp}-${nextNumber}`;

            setActiveViewId(id);

            return [...current, { id, name, type }];
        });
    };

    type ListTask = {
        id: string;
        name: string;
        statusId: string;
    };

    const [listTasks, setListTasks] = useState<ListTask[]>(() =>
        effectiveTasks.map((task) => ({
            id: task.id,
            name: task.title,
            statusId: task.status_id ?? statuses[0]!.id,
        })),
    );

    type KanbanTask = {
        id: string;
        name: string;
        column: string;
    };

    const [kanbanTasks, setKanbanTasks] = useState<KanbanTask[]>(() =>
        listTasks.map((task) => ({
            id: task.id,
            name: task.name,
            column: task.statusId,
        })),
    );

    const [calendarMonth, setCalendarMonth] = useState<Date>(() =>
        startOfMonth(new Date()),
    );

    const todayISO = toISODate(new Date());

    const [calendarTasks, setCalendarTasks] = useState<CalendarTask[]>(() =>
        effectiveTasks.map((task) => {
            if (!task.due_date) {
                return {
                    id: task.id,
                    title: task.title,
                    dueDateISO: todayISO,
                    statusId: task.status_id ?? statuses[0]!.id,
                };
            }

            const date = new Date(task.due_date);
            const dueDateISO = Number.isNaN(date.getTime())
                ? todayISO
                : toISODate(date);

            return {
                id: task.id,
                title: task.title,
                dueDateISO,
                statusId: task.status_id ?? statuses[0]!.id,
            };
        }),
    );

    const [activeCalendarTask, setActiveCalendarTask] =
        useState<CalendarTask | null>(null);

    const ganttStatuses: GanttStatus[] = statuses.map((s) => ({
        id: s.id,
        name: s.name,
        color: s.color,
    }));

    const [ganttFeatures, setGanttFeatures] = useState<GanttFeature[]>(() => {
        const parseDate = (iso: string | null | undefined): Date => {
            const d = iso ? new Date(iso) : new Date();
            return Number.isNaN(d.getTime()) ? new Date() : d;
        };

        return effectiveTasks.map((task) => {
            const due = parseDate(task.due_date);
            const startAt = new Date(
                due.getFullYear(),
                due.getMonth(),
                due.getDate() - 2,
            );
            const endAt = new Date(
                due.getFullYear(),
                due.getMonth(),
                due.getDate() + 1,
            );
            const statusId = task.status_id ?? statuses[0]!.id;
            const status =
                ganttStatuses.find((s) => s.id === statusId) ??
                ganttStatuses[0]!;

            return {
                id: task.id,
                name: task.title,
                startAt,
                endAt,
                status,
                lane: status.id,
            };
        });
    });

    const handleListDragEnd = ({ active, over }: DragEndEvent) => {
        if (!over) {
            return;
        }

        const fromStatusId = active.data.current?.parent as string | undefined;
        const toStatusId = String(over.id);

        if (!fromStatusId || fromStatusId === toStatusId) {
            return;
        }

        const activeId = String(active.id);

        setListTasks((current) =>
            current.map((task) =>
                task.id === activeId ? { ...task, statusId: toStatusId } : task,
            ),
        );

        setKanbanTasks((current) =>
            current.map((task) =>
                task.id === activeId ? { ...task, column: toStatusId } : task,
            ),
        );
    };

    const kanbanColumns = statuses.map((status) => ({
        id: status.id,
        name: status.name,
    }));

    const calendarGridStart = startOfWeekMonday(startOfMonth(calendarMonth));
    const calendarDays = Array.from({ length: 42 }, (_, i) =>
        addDays(calendarGridStart, i),
    );

    const tasksByDay = calendarTasks.reduce<Record<string, CalendarTask[]>>(
        (acc, item) => {
            const key = item.dueDateISO;
            acc[key] = acc[key] ?? [];
            acc[key].push(item);
            return acc;
        },
        {},
    );

    const CalendarTaskChip = ({
        id,
        title,
        statusId,
    }: {
        id: string;
        title: string;
        statusId: string;
    }) => {
        const { attributes, listeners, setNodeRef, transform, isDragging } =
            useDraggable({
                id,
                data: { title },
            });

        const statusColor =
            statuses.find((s) => s.id === statusId)?.color ?? '#6B7280';

        return (
            <div
                ref={setNodeRef}
                {...listeners}
                {...attributes}
                className={cn(
                    'flex cursor-grab items-center gap-2 truncate rounded-sm border border-border bg-muted/20 px-2 py-1 text-xs text-foreground transition-colors select-none',
                    isDragging && 'cursor-grabbing opacity-60',
                )}
                style={{
                    transform: transform
                        ? `translateX(${transform.x}px) translateY(${transform.y}px)`
                        : undefined,
                }}
                title={title}
            >
                <span
                    className="h-2 w-2 shrink-0 rounded-full"
                    style={{ backgroundColor: statusColor }}
                />
                <span className="truncate">{title}</span>
            </div>
        );
    };

    const CalendarDayCell = ({
        dayISO,
        isCurrentMonth,
        children,
    }: {
        dayISO: string;
        isCurrentMonth: boolean;
        children: React.ReactNode;
    }) => {
        const { setNodeRef, isOver } = useDroppable({ id: dayISO });

        return (
            <div
                ref={setNodeRef}
                className={cn(
                    'relative min-h-24 bg-background p-2',
                    isOver && 'bg-accent/20',
                )}
            >
                {!isCurrentMonth && (
                    <PlaceholderPattern className="pointer-events-none absolute inset-0 size-full stroke-neutral-900/12 dark:stroke-neutral-100/12" />
                )}
                {children}
            </div>
        );
    };

    return (
        <AppLayout>
            {/* Tab bar */}
            <div className="flex items-center gap-2 border-b border-sidebar-border/50 px-6 py-2">
                <div className="flex min-w-0 flex-1 items-center gap-2 overflow-x-auto overflow-y-hidden pr-3 whitespace-nowrap">
                    {views.map((view) => (
                        <button
                            key={view.id}
                            type="button"
                            onClick={() => setActiveViewId(view.id)}
                            className={cn(
                                'relative shrink-0 rounded-md px-3 py-1.5 text-sm font-medium transition-colors',
                                activeViewId === view.id
                                    ? 'text-foreground'
                                    : 'text-muted-foreground hover:text-foreground',
                            )}
                        >
                            {view.name}
                            {activeViewId === view.id && (
                                <span className="absolute inset-x-2 -bottom-[9px] h-0.5 rounded-full bg-foreground" />
                            )}
                        </button>
                    ))}
                </div>

                <div className="shrink-0">
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button
                                type="button"
                                size="sm"
                                variant="outline"
                                className="gap-2"
                            >
                                Create view
                                <ChevronDown className="size-4 opacity-70" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            {ViewTypes.map((type) => (
                                <DropdownMenuItem
                                    key={type}
                                    onClick={() => createNewView(type)}
                                >
                                    {type.charAt(0).toUpperCase() +
                                        type.slice(1)}{' '}
                                    view
                                </DropdownMenuItem>
                            ))}
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>

            {/* Content */}
            <div className="px-6 py-4">
                {activeView ? (
                    <div className="rounded-lg border border-border bg-card">
                        <div className="border-b border-border p-4">
                            <h2 className="text-lg font-medium">
                                {activeView.name}
                            </h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {activeView.type.charAt(0).toUpperCase() +
                                    activeView.type.slice(1)}{' '}
                                view
                            </p>
                        </div>

                        <div className="p-4">
                            {activeView.type === 'list' && (
                                <ListProvider
                                    onDragEnd={handleListDragEnd}
                                    className="gap-4"
                                >
                                    {statuses.map((status) => {
                                        const items = listTasks.filter(
                                            (task) =>
                                                task.statusId === status.id,
                                        );

                                        return (
                                            <ListGroup
                                                key={status.id}
                                                id={status.id}
                                                name={status.name}
                                                color={status.color}
                                                className="overflow-hidden rounded-md border"
                                            >
                                                <ListItems>
                                                    {items.length ? (
                                                        items.map(
                                                            (task, index) => (
                                                                <ListItem
                                                                    key={
                                                                        task.id
                                                                    }
                                                                    id={task.id}
                                                                    name={
                                                                        task.name
                                                                    }
                                                                    index={
                                                                        index
                                                                    }
                                                                    parent={
                                                                        status.id
                                                                    }
                                                                />
                                                            ),
                                                        )
                                                    ) : (
                                                        <div className="text-sm text-muted-foreground">
                                                            No items.
                                                        </div>
                                                    )}
                                                </ListItems>
                                            </ListGroup>
                                        );
                                    })}
                                </ListProvider>
                            )}

                            {activeView.type === 'kanban' && (
                                <KanbanProvider
                                    columns={kanbanColumns}
                                    data={kanbanTasks}
                                    onDataChange={(next) => {
                                        setKanbanTasks(next);
                                        setListTasks(
                                            next.map((task) => ({
                                                id: task.id,
                                                name: task.name,
                                                statusId: task.column,
                                            })),
                                        );
                                    }}
                                    className="min-h-[18rem]"
                                >
                                    {(column) => (
                                        <KanbanBoard id={column.id}>
                                            <KanbanHeader className="text-sm">
                                                {column.name}
                                            </KanbanHeader>
                                            <KanbanCards id={column.id}>
                                                {(item) => (
                                                    <KanbanCard
                                                        key={item.id}
                                                        {...item}
                                                    />
                                                )}
                                            </KanbanCards>
                                        </KanbanBoard>
                                    )}
                                </KanbanProvider>
                            )}

                            {activeView.type === 'calendar' && (
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between gap-2">
                                        <div className="min-w-0">
                                            <div className="text-sm font-medium">
                                                {calendarMonth.toLocaleString(
                                                    undefined,
                                                    {
                                                        month: 'long',
                                                        year: 'numeric',
                                                    },
                                                )}
                                            </div>
                                            <div className="text-sm text-muted-foreground">
                                                {calendarTasks.length
                                                    ? 'Drag tasks onto a date to schedule them.'
                                                    : 'No tasks yet.'}
                                            </div>
                                        </div>

                                        <div className="flex shrink-0 items-center gap-2">
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="outline"
                                                onClick={() =>
                                                    setCalendarMonth(
                                                        startOfMonth(
                                                            new Date(),
                                                        ),
                                                    )
                                                }
                                            >
                                                Today
                                            </Button>
                                            <Button
                                                type="button"
                                                size="icon"
                                                variant="outline"
                                                onClick={() =>
                                                    setCalendarMonth(
                                                        startOfMonth(
                                                            new Date(
                                                                calendarMonth.getFullYear(),
                                                                calendarMonth.getMonth() -
                                                                    1,
                                                                1,
                                                            ),
                                                        ),
                                                    )
                                                }
                                                aria-label="Previous month"
                                            >
                                                <ChevronLeft className="size-4" />
                                            </Button>
                                            <Button
                                                type="button"
                                                size="icon"
                                                variant="outline"
                                                onClick={() =>
                                                    setCalendarMonth(
                                                        startOfMonth(
                                                            new Date(
                                                                calendarMonth.getFullYear(),
                                                                calendarMonth.getMonth() +
                                                                    1,
                                                                1,
                                                            ),
                                                        ),
                                                    )
                                                }
                                                aria-label="Next month"
                                            >
                                                <ChevronRight className="size-4" />
                                            </Button>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-7 gap-2 text-xs text-muted-foreground">
                                        {[
                                            'Mon',
                                            'Tue',
                                            'Wed',
                                            'Thu',
                                            'Fri',
                                            'Sat',
                                            'Sun',
                                        ].map((d) => (
                                            <div key={d} className="px-1">
                                                {d}
                                            </div>
                                        ))}
                                    </div>

                                    <DndContext
                                        collisionDetection={rectIntersection}
                                        onDragStart={(
                                            event: DndDragStartEvent,
                                        ) => {
                                            const id = String(event.active.id);
                                            const title = event.active.data
                                                .current?.title as
                                                | string
                                                | undefined;
                                            const statusId =
                                                calendarTasks.find(
                                                    (t) => t.id === id,
                                                )?.statusId ?? statuses[0]!.id;

                                            setActiveCalendarTask(
                                                title
                                                    ? {
                                                          id,
                                                          title,
                                                          dueDateISO: todayISO,
                                                          statusId,
                                                      }
                                                    : null,
                                            );
                                        }}
                                        onDragEnd={(event: DndDragEndEvent) => {
                                            setActiveCalendarTask(null);

                                            if (!event.over) {
                                                return;
                                            }

                                            const taskId = String(
                                                event.active.id,
                                            );
                                            const dayISO = String(
                                                event.over.id,
                                            );

                                            setCalendarTasks((current) =>
                                                current.map((t) =>
                                                    t.id === taskId
                                                        ? {
                                                              ...t,
                                                              dueDateISO:
                                                                  dayISO,
                                                          }
                                                        : t,
                                                ),
                                            );
                                        }}
                                        onDragCancel={() =>
                                            setActiveCalendarTask(null)
                                        }
                                    >
                                        <div className="overflow-hidden rounded-lg border border-border">
                                            <div className="grid grid-cols-7 gap-px bg-border">
                                                {calendarDays.map((day) => {
                                                    const isCurrentMonth =
                                                        day.getMonth() ===
                                                        calendarMonth.getMonth();
                                                    const dayISO =
                                                        toISODate(day);
                                                    const dayTasks =
                                                        tasksByDay[dayISO] ??
                                                        [];
                                                    const visible =
                                                        dayTasks.slice(0, 3);
                                                    const remaining =
                                                        dayTasks.length -
                                                        visible.length;

                                                    return (
                                                        <CalendarDayCell
                                                            key={dayISO}
                                                            dayISO={dayISO}
                                                            isCurrentMonth={
                                                                isCurrentMonth
                                                            }
                                                        >
                                                            <div className="flex items-center justify-between">
                                                                <div className="text-xs font-medium">
                                                                    {day.getDate()}
                                                                </div>
                                                            </div>

                                                            <div className="mt-2 space-y-1">
                                                                {visible.map(
                                                                    (t) => (
                                                                        <CalendarTaskChip
                                                                            key={
                                                                                t.id
                                                                            }
                                                                            id={
                                                                                t.id
                                                                            }
                                                                            title={
                                                                                t.title
                                                                            }
                                                                            statusId={
                                                                                t.statusId
                                                                            }
                                                                        />
                                                                    ),
                                                                )}
                                                                {remaining >
                                                                    0 && (
                                                                    <div className="text-xs text-muted-foreground">
                                                                        +
                                                                        {
                                                                            remaining
                                                                        }{' '}
                                                                        more
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </CalendarDayCell>
                                                    );
                                                })}
                                            </div>
                                        </div>

                                        <DragOverlay>
                                            {activeCalendarTask ? (
                                                <div className="flex cursor-grabbing items-center gap-2 rounded-sm border border-border bg-background px-2 py-1 text-xs text-foreground shadow-md">
                                                    <span
                                                        className="h-2 w-2 shrink-0 rounded-full"
                                                        style={{
                                                            backgroundColor:
                                                                statuses.find(
                                                                    (s) =>
                                                                        s.id ===
                                                                        activeCalendarTask.statusId,
                                                                )?.color ??
                                                                '#6B7280',
                                                        }}
                                                    />
                                                    <span className="truncate">
                                                        {
                                                            activeCalendarTask.title
                                                        }
                                                    </span>
                                                </div>
                                            ) : null}
                                        </DragOverlay>
                                    </DndContext>
                                </div>
                            )}

                            {activeView.type === 'gantt' && (
                                <GanttProvider
                                    range="daily"
                                    zoom={100}
                                    className="h-[34rem]"
                                >
                                    <GanttSidebar>
                                        {ganttStatuses.map((status) => (
                                            <GanttSidebarGroup
                                                key={status.id}
                                                name={status.name}
                                            >
                                                {ganttFeatures
                                                    .filter(
                                                        (f) =>
                                                            f.status.id ===
                                                            status.id,
                                                    )
                                                    .map((feature) => (
                                                        <GanttSidebarItem
                                                            key={feature.id}
                                                            feature={feature}
                                                        />
                                                    ))}
                                            </GanttSidebarGroup>
                                        ))}
                                    </GanttSidebar>

                                    <GanttTimeline>
                                        <GanttHeader />
                                        <GanttToday className="bg-primary" />

                                        <GanttFeatureList>
                                            {ganttStatuses.map((status) => (
                                                <GanttFeatureListGroup
                                                    key={status.id}
                                                >
                                                    <GanttFeatureRow
                                                        features={ganttFeatures.filter(
                                                            (f) =>
                                                                f.status.id ===
                                                                status.id,
                                                        )}
                                                        onMove={(
                                                            id,
                                                            startAt,
                                                            endAt,
                                                        ) => {
                                                            setGanttFeatures(
                                                                (current) =>
                                                                    current.map(
                                                                        (f) =>
                                                                            f.id ===
                                                                            id
                                                                                ? {
                                                                                      ...f,
                                                                                      startAt,
                                                                                      endAt,
                                                                                  }
                                                                                : f,
                                                                    ),
                                                            );
                                                        }}
                                                    />
                                                </GanttFeatureListGroup>
                                            ))}
                                        </GanttFeatureList>
                                    </GanttTimeline>
                                </GanttProvider>
                            )}
                        </div>
                    </div>
                ) : (
                    <div className="text-sm text-muted-foreground">
                        No views yet.
                    </div>
                )}
            </div>
        </AppLayout>
    );
};

export default Index;
