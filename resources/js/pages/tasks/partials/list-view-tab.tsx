import type { DragEndEvent } from '@/components/list-view';
import { ListGroup, ListItem, ListItems, ListProvider } from '@/components/list-view';
import type { ListTask, Status } from '../types';

type Props = {
    statuses: Status[];
    listTasks: ListTask[];
    onDragEnd: (event: DragEndEvent) => void;
    onOpenTask: (taskId: string) => void;
};

export default function ListViewTab({ statuses, listTasks, onDragEnd, onOpenTask }: Props) {
    return (
        <ListProvider onDragEnd={onDragEnd} className="gap-4">
            {statuses.map((status) => {
                const items = listTasks.filter((task) => task.statusId === status.id);

                return (
                    <ListGroup key={status.id} id={status.id} name={status.name} color={status.color} className="overflow-hidden rounded-md border">
                        <ListItems>
                            {items.length ? (
                                items.map((task, index) => (
                                    <ListItem
                                        key={task.id}
                                        id={task.id}
                                        name={task.name}
                                        index={index}
                                        parent={status.id}
                                        onSelectItem={onOpenTask}
                                    />
                                ))
                            ) : (
                                <div className="text-sm text-muted-foreground">No items.</div>
                            )}
                        </ListItems>
                    </ListGroup>
                );
            })}
        </ListProvider>
    );
}
