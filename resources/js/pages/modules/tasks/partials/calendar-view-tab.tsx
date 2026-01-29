import { Button } from '@/shared/components/ui/button';
import { PlaceholderPattern } from '@/shared/components/ui/placeholder-pattern';
import { cn } from '@/shared/lib/utils';
import {
    DndContext,
    DragOverlay,
    MouseSensor,
    rectIntersection,
    TouchSensor,
    useDraggable,
    useDroppable,
    useSensor,
    useSensors,
    type DragEndEvent as DndDragEndEvent,
    type DragStartEvent as DndDragStartEvent,
} from '@dnd-kit/core';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useMemo, useState, type Dispatch, type ReactNode, type SetStateAction } from 'react';
import { useTranslation } from 'react-i18next';
import type { CalendarTask, Status } from '../types';

type Props = {
    statuses: Status[];
    calendarMonth: Date;
    setCalendarMonth: Dispatch<SetStateAction<Date>>;
    calendarTasks: CalendarTask[];
    onMoveTask: (taskId: string, newDueDateISO: string) => void;
    onOpenTask: (taskId: string) => void;
};

const toISODate = (date: Date): string => {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
};

const startOfMonth = (date: Date): Date => new Date(date.getFullYear(), date.getMonth(), 1);

const startOfWeekMonday = (date: Date): Date => {
    const day = date.getDay(); // 0 = Sun, 1 = Mon, ...
    const diff = (day + 6) % 7; // Mon => 0, Sun => 6
    return new Date(date.getFullYear(), date.getMonth(), date.getDate() - diff);
};

const addDays = (date: Date, days: number): Date => new Date(date.getFullYear(), date.getMonth(), date.getDate() + days);

function CalendarTaskChip({
    id,
    title,
    statusId,
    statuses,
    onSelectItem,
}: {
    id: string;
    title: string;
    statusId: string;
    statuses: Status[];
    onSelectItem?: (id: string) => void;
}) {
    const { attributes, listeners, setNodeRef, transform, isDragging } = useDraggable({
        id,
        data: { title },
    });

    const statusColor = statuses.find((s) => s.id === statusId)?.color ?? '#6B7280';

    return (
        <div
            ref={setNodeRef}
            {...listeners}
            {...attributes}
            className={cn(
                'flex cursor-pointer items-center gap-2 truncate rounded-sm border border-border bg-muted/20 px-2 py-1 text-xs text-foreground transition-colors select-none hover:border-ring hover:bg-accent/30',
                isDragging && 'cursor-grabbing opacity-60',
            )}
            onClick={() => onSelectItem?.(id)}
            style={{
                transform: transform ? `translateX(${transform.x}px) translateY(${transform.y}px)` : undefined,
            }}
            title={title}
        >
            <span className="h-2 w-2 shrink-0 rounded-full" style={{ backgroundColor: statusColor }} />
            <span className="truncate">{title}</span>
        </div>
    );
}

function CalendarDayCell({ dayISO, isCurrentMonth, children }: { dayISO: string; isCurrentMonth: boolean; children: ReactNode }) {
    const { setNodeRef, isOver } = useDroppable({ id: dayISO });

    return (
        <div ref={setNodeRef} className={cn('relative min-h-24 bg-background p-2', isOver && 'bg-accent/20')}>
            {!isCurrentMonth && (
                <PlaceholderPattern className="pointer-events-none absolute inset-0 size-full stroke-neutral-900/12 dark:stroke-neutral-100/12" />
            )}
            {children}
        </div>
    );
}

export default function CalendarViewTab({ statuses, calendarMonth, setCalendarMonth, calendarTasks, onMoveTask, onOpenTask }: Props) {
    const { t } = useTranslation();
    const calendarGridStart = startOfWeekMonday(startOfMonth(calendarMonth));
    const calendarDays = useMemo(() => Array.from({ length: 42 }, (_, i) => addDays(calendarGridStart, i)), [calendarGridStart]);

    const tasksByDay = useMemo(
        () =>
            calendarTasks.reduce<Record<string, CalendarTask[]>>((acc, item) => {
                const key = item.dueDateISO;
                acc[key] = acc[key] ?? [];
                acc[key].push(item);
                return acc;
            }, {}),
        [calendarTasks],
    );

    const [activeCalendarTask, setActiveCalendarTask] = useState<{
        id: string;
        title: string;
        statusId: string;
    } | null>(null);

    const sensors = useSensors(
        useSensor(MouseSensor, { activationConstraint: { distance: 8 } }),
        useSensor(TouchSensor, { activationConstraint: { distance: 8 } }),
    );

    return (
        <div className="space-y-4">
            <div className="flex items-center justify-between gap-2">
                <div className="min-w-0">
                    <div className="text-sm font-medium">
                        {calendarMonth.toLocaleString(undefined, {
                            month: 'long',
                            year: 'numeric',
                        })}
                    </div>
                    <div className="text-sm text-muted-foreground">
                        {calendarTasks.length ? 'Drag tasks onto a date to schedule them.' : 'No tasks yet.'}
                    </div>
                </div>

                <div className="flex shrink-0 items-center gap-2">
                    <Button type="button" size="sm" variant="outline" onClick={() => setCalendarMonth(startOfMonth(new Date()))}>
                        Today
                    </Button>
                    <Button
                        type="button"
                        size="icon"
                        variant="outline"
                        onClick={() => setCalendarMonth(startOfMonth(new Date(calendarMonth.getFullYear(), calendarMonth.getMonth() - 1, 1)))}
                        aria-label={t('tasks.calendar.previous_month')}
                    >
                        <ChevronLeft className="size-4" />
                    </Button>
                    <Button
                        type="button"
                        size="icon"
                        variant="outline"
                        onClick={() => setCalendarMonth(startOfMonth(new Date(calendarMonth.getFullYear(), calendarMonth.getMonth() + 1, 1)))}
                        aria-label={t('tasks.calendar.next_month')}
                    >
                        <ChevronRight className="size-4" />
                    </Button>
                </div>
            </div>

            <div className="grid grid-cols-7 gap-2 text-xs text-muted-foreground">
                {[
                    t('tasks.calendar.monday'),
                    t('tasks.calendar.tuesday'),
                    t('tasks.calendar.wednesday'),
                    t('tasks.calendar.thursday'),
                    t('tasks.calendar.friday'),
                    t('tasks.calendar.saturday'),
                    t('tasks.calendar.sunday'),
                ].map((d) => (
                    <div key={d} className="px-1">
                        {d}
                    </div>
                ))}
            </div>

            <DndContext
                sensors={sensors}
                collisionDetection={rectIntersection}
                onDragStart={(event: DndDragStartEvent) => {
                    const id = String(event.active.id);
                    const title = event.active.data.current?.title as string | undefined;
                    const statusId = calendarTasks.find((t) => t.id === id)?.statusId ?? statuses[0]!.id;

                    setActiveCalendarTask(title ? { id, title, statusId } : null);
                }}
                onDragEnd={(event: DndDragEndEvent) => {
                    setActiveCalendarTask(null);

                    if (!event.over) {
                        return;
                    }

                    const taskId = String(event.active.id);
                    const dayISO = String(event.over.id);

                    onMoveTask(taskId, dayISO);
                }}
                onDragCancel={() => setActiveCalendarTask(null)}
            >
                <div className="overflow-hidden rounded-lg border border-border">
                    <div className="grid grid-cols-7 gap-px bg-border">
                        {calendarDays.map((day) => {
                            const isCurrentMonth = day.getMonth() === calendarMonth.getMonth();
                            const dayISO = toISODate(day);
                            const dayTasks = tasksByDay[dayISO] ?? [];
                            const visible = dayTasks.slice(0, 3);
                            const remaining = dayTasks.length - visible.length;

                            return (
                                <CalendarDayCell key={dayISO} dayISO={dayISO} isCurrentMonth={isCurrentMonth}>
                                    <div className="flex items-center justify-between">
                                        <div className="text-xs font-medium">{day.getDate()}</div>
                                    </div>

                                    <div className="mt-2 space-y-1">
                                        {visible.map((t) => (
                                            <CalendarTaskChip
                                                key={t.id}
                                                id={t.id}
                                                title={t.title}
                                                statusId={t.statusId}
                                                statuses={statuses}
                                                onSelectItem={onOpenTask}
                                            />
                                        ))}
                                        {remaining > 0 && <div className="text-xs text-muted-foreground">+{remaining} more</div>}
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
                                    backgroundColor: statuses.find((s) => s.id === activeCalendarTask.statusId)?.color ?? '#6B7280',
                                }}
                            />
                            <span className="truncate">{activeCalendarTask.title}</span>
                        </div>
                    ) : null}
                </DragOverlay>
            </DndContext>
        </div>
    );
}
