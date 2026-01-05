import { KanbanBoard, KanbanCard, KanbanCards, KanbanHeader, KanbanProvider } from '@/components/kanban-view';
import { useRef } from 'react';
import type { KanbanTask, Status } from '../types';

type Props = {
    statuses: Status[];
    kanbanTasks: KanbanTask[];
    onDataChange: (next: KanbanTask[], prev: KanbanTask[]) => void;
    onOpenTask: (taskId: string) => void;
};

export default function KanbanViewTab({ statuses, kanbanTasks, onDataChange, onOpenTask }: Props) {
    const kanbanColumns = statuses.map((status) => ({
        id: status.id,
        name: status.name,
    }));

    // Keep a ref to the previous data so we can detect changes
    const prevDataRef = useRef(kanbanTasks);

    return (
        <KanbanProvider
            columns={kanbanColumns}
            data={kanbanTasks}
            onDataChange={(next) => {
                onDataChange(next, prevDataRef.current);
                prevDataRef.current = next;
            }}
            className="min-h-72"
        >
            {(column) => (
                <KanbanBoard id={column.id}>
                    <KanbanHeader className="text-sm">{column.name}</KanbanHeader>
                    <KanbanCards id={column.id}>{(item) => <KanbanCard key={item.id} {...item} onSelectItem={onOpenTask} />}</KanbanCards>
                </KanbanBoard>
            )}
        </KanbanProvider>
    );
}
