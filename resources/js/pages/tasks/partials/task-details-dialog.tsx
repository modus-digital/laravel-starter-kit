import InputError from '@/components/input-error';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DatePicker } from '@/components/ui/datepicker';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Sheet, SheetContent, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { cn } from '@/lib/utils';
import tasksRoutes from '@/routes/tasks';
import type { SharedData } from '@/types';
import { Form, usePage } from '@inertiajs/react';
import { format } from 'date-fns';
import { ChevronDown, Flag, MessageSquare } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import type { Status, Task, TaskPriority } from '../types';

type Props = {
    task: Task | null;
    statuses: Status[];
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

const UNASSIGNED_VALUE = '__unassigned__';

const priorityOptions: Array<{ value: TaskPriority; label: string; color: string }> = [
    { value: 'low', label: 'Low', color: 'text-muted-foreground' },
    { value: 'normal', label: 'Normal', color: 'text-blue-500' },
    { value: 'high', label: 'High', color: 'text-orange-500' },
    { value: 'critical', label: 'Critical', color: 'text-red-500' },
];

const toDate = (iso: string | null | undefined): Date | undefined => {
    if (!iso) return undefined;
    const d = new Date(iso);
    return Number.isNaN(d.getTime()) ? undefined : d;
};

const formatDate = (date: Date | undefined): string => {
    if (!date) return '';
    return format(date, 'MMM d, yyyy');
};

export default function TaskDetailsDialog({ task, statuses = [], open, onOpenChange }: Props) {
    if (!task) {
        return null;
    }

    return <TaskDetailsDialogInner key={task.id} task={task} statuses={statuses} open={open} onOpenChange={onOpenChange} />;
}

function TaskDetailsDialogInner({
    task,
    statuses = [],
    open,
    onOpenChange,
}: {
    task: Task;
    statuses: Status[];
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const { auth, appUrl } = usePage<SharedData & { appUrl?: string }>().props;

    const assigneeOptions = [
        { id: UNASSIGNED_VALUE, name: 'Unassigned' },
        ...(auth?.user?.id !== undefined ? [{ id: String(auth.user.id), name: auth.user.name }] : []),
    ];

    const [selectedAssigneeId, setSelectedAssigneeId] = useState<string>(task.assigned_to_id ?? UNASSIGNED_VALUE);
    const [selectedPriority, setSelectedPriority] = useState<TaskPriority>(task.priority ?? 'normal');
    const [dueDate, setDueDate] = useState<Date | undefined>(toDate(task.due_date));
    const [selectedStatusId, setSelectedStatusId] = useState<string>(task.status_id ?? statuses[0]?.id ?? '');

    const currentStatus = statuses?.find((s) => s.id === selectedStatusId);
    const currentAssignee = assigneeOptions.find((opt) => opt.id === selectedAssigneeId);

    const handleCopyTaskUrl = async () => {
        const baseUrl = appUrl || window.location.origin;
        const taskUrl = `${baseUrl}/tasks/${task.id}`;

        try {
            await navigator.clipboard.writeText(taskUrl);
            toast.success('Task URL copied to clipboard');
        } catch (err) {
            toast.error('Failed to copy URL');
        }
    };

    // Mock activity data - in a real app, this would come from props or API
    const activities = [
        { id: '1', action: 'created', user: auth?.user?.name ?? 'You', date: task.created_at ? new Date(task.created_at) : new Date() },
        ...(task.assigned_to_id
            ? [
                  {
                      id: '2',
                      action: 'added follower',
                      user: auth?.user?.name ?? 'You',
                      date: task.updated_at ? new Date(task.updated_at) : new Date(),
                  },
              ]
            : []),
    ];

    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent
                side="right"
                className="right-6! left-6! flex h-[calc(100vh-3rem)] w-auto! max-w-none flex-col overflow-hidden rounded-lg p-0"
            >
                <Form
                    key={task.id}
                    {...tasksRoutes.update.form(task.id)}
                    options={{ preserveScroll: true }}
                    disableWhileProcessing
                    onSuccess={() => {
                        toast.success('Task updated');
                    }}
                    className="flex h-full flex-col"
                >
                    {({ processing, errors }) => (
                        <div className="flex h-full flex-col">
                            {/* Header */}
                            <SheetHeader className="border-b px-6 py-4">
                                <div className="flex items-center gap-2">
                                    <ChevronDown className="h-4 w-4 text-muted-foreground" />
                                    <SheetTitle className="text-base font-medium">Task</SheetTitle>
                                    <TooltipProvider>
                                        <Tooltip>
                                            <TooltipTrigger asChild>
                                                <Badge
                                                    variant="outline"
                                                    className="cursor-pointer text-xs transition-colors hover:bg-accent"
                                                    onClick={handleCopyTaskUrl}
                                                >
                                                    {task.id}
                                                </Badge>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p>Click to copy task URL</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                </div>
                            </SheetHeader>

                            <div className="flex flex-1 overflow-hidden">
                                {/* Main Content */}
                                <ScrollArea className="flex-1">
                                    <div className="space-y-6 px-48 py-6">
                                        {/* Task Title */}
                                        <div className="space-y-2">
                                            <Input
                                                id="task-title"
                                                name="title"
                                                required
                                                defaultValue={task.title}
                                                className="border-none px-4 py-6 text-3xl! font-semibold shadow-none hover:ring-1 hover:ring-ring/50 focus-visible:ring-1 focus-visible:ring-ring/50"
                                            />
                                            <InputError message={errors.title as string | undefined} />
                                        </div>

                                        {/* Properties Grid */}
                                        <div className="grid grid-cols-2 gap-4">
                                            {/* Status */}
                                            <div className="space-y-2">
                                                <Label className="text-xs text-muted-foreground">Status</Label>
                                                <input type="hidden" name="status_id" value={selectedStatusId} />
                                                <Select value={selectedStatusId} onValueChange={setSelectedStatusId}>
                                                    <SelectTrigger className="h-8">
                                                        <SelectValue>
                                                            {currentStatus && (
                                                                <div className="flex items-center gap-2">
                                                                    <div
                                                                        className="h-2 w-2 rounded-full"
                                                                        style={{ backgroundColor: currentStatus.color }}
                                                                    />
                                                                    <span>{currentStatus.name}</span>
                                                                </div>
                                                            )}
                                                        </SelectValue>
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {statuses.map((status) => (
                                                            <SelectItem key={status.id} value={status.id}>
                                                                <div className="flex items-center gap-2">
                                                                    <div className="h-2 w-2 rounded-full" style={{ backgroundColor: status.color }} />
                                                                    <span>{status.name}</span>
                                                                </div>
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            </div>

                                            {/* Assignees */}
                                            <div className="space-y-2">
                                                <Label className="text-xs text-muted-foreground">Assignees</Label>
                                                <input
                                                    type="hidden"
                                                    name="assigned_to_id"
                                                    value={selectedAssigneeId === UNASSIGNED_VALUE ? '' : selectedAssigneeId}
                                                />
                                                <Select value={selectedAssigneeId} onValueChange={setSelectedAssigneeId}>
                                                    <SelectTrigger className="h-8">
                                                        <SelectValue placeholder="Select assignee">
                                                            {currentAssignee && currentAssignee.id !== UNASSIGNED_VALUE ? (
                                                                <div className="flex items-center gap-2">
                                                                    <Avatar className="h-5 w-5">
                                                                        <AvatarFallback className="text-xs">
                                                                            {currentAssignee.name
                                                                                .split(' ')
                                                                                .map((n) => n[0])
                                                                                .join('')
                                                                                .toUpperCase()}
                                                                        </AvatarFallback>
                                                                    </Avatar>
                                                                    <span className="text-sm">{currentAssignee.name}</span>
                                                                </div>
                                                            ) : (
                                                                <span className="text-muted-foreground">Unassigned</span>
                                                            )}
                                                        </SelectValue>
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {assigneeOptions.map((option) => (
                                                            <SelectItem key={option.id} value={option.id}>
                                                                {option.name}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                                <InputError message={errors.assigned_to_id as string | undefined} />
                                            </div>

                                            {/* Due Date */}
                                            <div className="space-y-2">
                                                <Label className="text-xs text-muted-foreground">Due Date</Label>
                                                <input type="hidden" name="due_date" value={dueDate ? dueDate.toISOString().split('T')[0] : ''} />
                                                <DatePicker
                                                    value={dueDate}
                                                    onChange={setDueDate}
                                                    placeholder="Select due date"
                                                    className="h-8 text-xs"
                                                />
                                                <InputError message={errors.due_date as string | undefined} />
                                            </div>

                                            {/* Priority */}
                                            <div className="space-y-2">
                                                <Label className="text-xs text-muted-foreground">Priority</Label>
                                                <input type="hidden" name="priority" value={selectedPriority} />
                                                <Select
                                                    value={selectedPriority}
                                                    onValueChange={(value) => setSelectedPriority(value as TaskPriority)}
                                                >
                                                    <SelectTrigger className="h-8">
                                                        <SelectValue placeholder="Select priority">
                                                            {selectedPriority && (
                                                                <div className="flex items-center gap-2">
                                                                    <Flag
                                                                        className={cn(
                                                                            'h-4 w-4',
                                                                            priorityOptions.find((opt) => opt.value === selectedPriority)?.color,
                                                                        )}
                                                                    />
                                                                    <span className="text-sm">
                                                                        {priorityOptions.find((opt) => opt.value === selectedPriority)?.label}
                                                                    </span>
                                                                </div>
                                                            )}
                                                        </SelectValue>
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {priorityOptions.map((option) => (
                                                            <SelectItem key={option.value} value={option.value}>
                                                                <div className="flex items-center gap-2">
                                                                    <Flag className={cn('h-4 w-4', option.color)} />
                                                                    <span>{option.label}</span>
                                                                </div>
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                                <InputError message={errors.priority as string | undefined} />
                                            </div>
                                        </div>

                                        {/* Description */}
                                        <div className="space-y-2">
                                            <Label htmlFor="task-description" className="text-sm font-medium">
                                                Description
                                            </Label>
                                            <textarea
                                                id="task-description"
                                                name="description"
                                                defaultValue={task.description ?? ''}
                                                placeholder="Add description"
                                                className={cn(
                                                    'w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none selection:bg-primary selection:text-primary-foreground placeholder:text-muted-foreground',
                                                    'focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50',
                                                    'aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40',
                                                    'min-h-24 resize-y',
                                                )}
                                            />
                                            <InputError message={errors.description as string | undefined} />
                                        </div>
                                    </div>
                                </ScrollArea>

                                {/* Activity Sidebar */}
                                <div className="flex w-xl flex-col border-l bg-muted/30">
                                    <div className="flex items-center justify-between border-b px-4 py-3">
                                        <h3 className="text-sm font-medium">Activity</h3>
                                        <div className="flex items-center gap-2">
                                            <Button type="button" variant="ghost" size="icon" className="h-6 w-6">
                                                <MessageSquare className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                    <ScrollArea className="flex-1">
                                        <div className="space-y-4 px-4 py-3">
                                            {activities.map((activity) => (
                                                <div key={activity.id} className="space-y-1">
                                                    <div className="text-sm">
                                                        <span className="font-medium">{activity.user}</span>{' '}
                                                        <span className="text-muted-foreground">{activity.action} this task</span>
                                                    </div>
                                                    <div className="text-xs text-muted-foreground">
                                                        {format(activity.date, 'MMM d')} at {format(activity.date, 'HH:mm')}
                                                    </div>
                                                </div>
                                            ))}
                                            <Button type="button" variant="ghost" size="sm" className="text-xs">
                                                Show more
                                            </Button>
                                        </div>
                                    </ScrollArea>
                                    <div className="border-t px-4 py-3">
                                        <div className="flex items-center gap-2">
                                            <Input placeholder="Write a comment..." className="h-8 text-xs" />
                                            <Button type="button" size="icon" className="h-8 w-8">
                                                <MessageSquare className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Footer */}
                            <div className="flex items-center justify-end gap-2 border-t px-6 py-4">
                                <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                                    Close
                                </Button>
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Saving…' : 'Save'}
                                </Button>
                            </div>
                        </div>
                    )}
                </Form>
            </SheetContent>
        </Sheet>
    );
}
