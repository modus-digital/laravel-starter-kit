'use client';

import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/shared/components/ui/collapsible';
import { cn } from '@/shared/lib/utils';
import {
    DndContext,
    type DragEndEvent,
    DragOverlay,
    type DragStartEvent,
    MouseSensor,
    rectIntersection,
    TouchSensor,
    useDraggable,
    useDroppable,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import { restrictToVerticalAxis } from '@dnd-kit/modifiers';
import { ChevronDown } from 'lucide-react';
import type { ReactNode } from 'react';
import { useState } from 'react';

export type { DragEndEvent } from '@dnd-kit/core';

type Status = {
    id: string;
    name: string;
    color: string;
};

type Feature = {
    id: string;
    name: string;
    startAt: Date;
    endAt: Date;
    status: Status;
};

export type ListItemsProps = {
    children: ReactNode;
    className?: string;
};

export const ListItems = ({ children, className }: ListItemsProps) => (
    <div className={cn('flex flex-1 flex-col gap-2 p-3', className)}>{children}</div>
);

export type ListHeaderProps =
    | {
          children: ReactNode;
      }
    | {
          name: Status['name'];
          color: Status['color'];
          className?: string;
      };

export const ListHeader = (props: ListHeaderProps) =>
    'children' in props ? (
        props.children
    ) : (
        <div className={cn('flex shrink-0 items-center gap-2 border-b bg-muted/30 p-3', props.className)}>
            <div className="h-2 w-2 rounded-full" style={{ backgroundColor: props.color }} />
            <p className="m-0 text-sm font-semibold">{props.name}</p>
        </div>
    );

export type ListGroupProps = {
    id: Status['id'];
    name?: Status['name'];
    color?: Status['color'];
    collapsible?: boolean;
    defaultOpen?: boolean;
    children: ReactNode;
    className?: string;
};

export const ListGroup = ({ id, name, color, collapsible = true, defaultOpen = true, children, className }: ListGroupProps) => {
    const { setNodeRef, isOver } = useDroppable({ id });
    const [open, setOpen] = useState(defaultOpen);

    const header = (
        <div className="flex min-w-0 items-center gap-2">
            {color ? <div className="h-2 w-2 shrink-0 rounded-full" style={{ backgroundColor: color }} /> : null}
            {name ? <p className="truncate text-sm font-semibold">{name}</p> : null}
        </div>
    );

    const containerClassName = cn('bg-card transition-colors', isOver && 'bg-accent/30', className);

    if (!collapsible) {
        return (
            <div className={containerClassName} ref={setNodeRef}>
                <div className="flex items-center justify-between border-b bg-muted/30 p-3">{header}</div>
                {children}
            </div>
        );
    }

    return (
        <Collapsible open={open} onOpenChange={setOpen}>
            <div className={containerClassName} ref={setNodeRef}>
                <CollapsibleTrigger asChild>
                    <button type="button" className="flex w-full items-center justify-between gap-2 border-b bg-muted/30 p-3 text-left">
                        {header}
                        <ChevronDown className={cn('size-4 shrink-0 opacity-70 transition-transform', open && 'rotate-180')} />
                    </button>
                </CollapsibleTrigger>
                <CollapsibleContent>{children}</CollapsibleContent>
            </div>
        </Collapsible>
    );
};

export type ListItemProps = Pick<Feature, 'id' | 'name'> & {
    readonly index: number;
    readonly parent: string;
    readonly onSelectItem?: (id: string) => void;
    readonly children?: ReactNode;
    readonly className?: string;
};

export const ListItem = ({ id, name, index, parent, onSelectItem, children, className }: ListItemProps) => {
    const { attributes, listeners, setNodeRef, transform, isDragging } = useDraggable({
        id,
        data: { index, parent, name },
    });

    return (
        <div
            className={cn(
                'flex cursor-pointer items-center gap-2 rounded-md border border-border bg-background p-2 shadow-sm transition-colors select-none hover:border-ring hover:bg-muted/40',
                isDragging && 'cursor-grabbing opacity-90',
                className,
            )}
            style={{
                transform: transform ? `translateX(${transform.x}px) translateY(${transform.y}px)` : 'none',
            }}
            {...listeners}
            {...attributes}
            ref={setNodeRef}
            onClick={() => onSelectItem?.(id)}
        >
            {children ?? <p className="m-0 text-sm font-medium">{name}</p>}
        </div>
    );
};

export type ListProviderProps = {
    children: ReactNode;
    onDragEnd: (event: DragEndEvent) => void;
    className?: string;
};

export const ListProvider = ({ children, onDragEnd, className }: ListProviderProps) => {
    const [activeItem, setActiveItem] = useState<{
        id: string;
        name?: string;
    } | null>(null);

    const sensors = useSensors(
        useSensor(MouseSensor, { activationConstraint: { distance: 8 } }),
        useSensor(TouchSensor, { activationConstraint: { distance: 8 } }),
    );

    const handleDragStart = ({ active }: DragStartEvent) => {
        setActiveItem({
            id: String(active.id),
            name: active.data.current?.name as string | undefined,
        });
    };

    const handleDragEnd = (event: DragEndEvent) => {
        setActiveItem(null);
        onDragEnd(event);
    };

    const handleDragCancel = () => {
        setActiveItem(null);
    };

    return (
        <DndContext
            sensors={sensors}
            collisionDetection={rectIntersection}
            modifiers={[restrictToVerticalAxis]}
            onDragStart={handleDragStart}
            onDragCancel={handleDragCancel}
            onDragEnd={handleDragEnd}
        >
            <div className={cn('flex size-full flex-col', className)}>{children}</div>

            <DragOverlay>
                {activeItem ? (
                    <div className="flex items-center gap-2 rounded-md border border-border bg-background p-2 shadow-md">
                        <p className="m-0 text-sm font-medium">{activeItem.name ?? ''}</p>
                    </div>
                ) : null}
            </DragOverlay>
        </DndContext>
    );
};
