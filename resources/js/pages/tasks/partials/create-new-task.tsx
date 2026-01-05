import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { DatePicker } from '@/components/ui/datepicker';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { cn } from '@/lib/utils';
import tasksRoutes from '@/routes/tasks';
import type { SharedData } from '@/types';
import { Form, usePage } from '@inertiajs/react';
import { Flag } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

const UNASSIGNED_VALUE = '__unassigned__';
const priorityOptions = [
    { value: 'low', label: 'Low', color: 'text-muted-foreground' },
    { value: 'normal', label: 'Normal', color: 'text-blue-500' },
    { value: 'high', label: 'High', color: 'text-orange-500' },
    { value: 'critical', label: 'Critical', color: 'text-red-500' },
] as const;

export default function CreateNewTask() {
    const [open, setOpen] = useState(false);
    const [selectedAssigneeId, setSelectedAssigneeId] = useState<string>(UNASSIGNED_VALUE);
    const [selectedPriority, setSelectedPriority] = useState<(typeof priorityOptions)[number]['value']>('normal');
    const [dueDate, setDueDate] = useState<Date | undefined>(undefined);

    const { auth } = usePage<SharedData>().props;

    const assigneeOptions = [
        { id: UNASSIGNED_VALUE, name: 'Unassigned' },
        ...(auth?.user?.id !== undefined ? [{ id: String(auth.user.id), name: auth.user.name }] : []),
    ];

    const close = () => {
        setOpen(false);
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <Button type="button" size="sm" onClick={() => setOpen(true)}>
                New task
            </Button>

            <DialogContent className="md:max-w-3xl">
                <DialogHeader>
                    <DialogTitle>Create task</DialogTitle>
                    <DialogDescription>Create a new task for the current scope.</DialogDescription>
                </DialogHeader>

                <Form
                    {...tasksRoutes.store.form()}
                    options={{ preserveScroll: true }}
                    disableWhileProcessing
                    resetOnSuccess={['title', 'description', 'due_date', 'assigned_to_id', 'priority']}
                    onSuccess={() => {
                        setSelectedAssigneeId(UNASSIGNED_VALUE);
                        setSelectedPriority('normal');
                        setDueDate(undefined);
                        close();
                        toast.success('Task created');
                    }}
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-4 md:grid-cols-3">
                                <div className="space-y-2 md:col-span-3">
                                    <Label htmlFor="task-title">Task title</Label>
                                    <Input id="task-title" name="title" required autoFocus placeholder="e.g. Follow up with client" />
                                    <InputError message={errors.title as string | undefined} />
                                </div>

                                <div className="space-y-2 md:col-span-3">
                                    <Label htmlFor="task-description">Description</Label>
                                    <textarea
                                        id="task-description"
                                        name="description"
                                        placeholder="Optional"
                                        className={cn(
                                            'w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none selection:bg-primary selection:text-primary-foreground placeholder:text-muted-foreground md:text-sm',
                                            'focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50',
                                            'aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40',
                                            'min-h-24 resize-y',
                                        )}
                                    />
                                    <InputError message={errors.description as string | undefined} />
                                </div>

                                <div className="space-y-2">
                                    <Label>Assignee</Label>

                                    <input
                                        type="hidden"
                                        name="assigned_to_id"
                                        value={selectedAssigneeId === UNASSIGNED_VALUE ? '' : selectedAssigneeId}
                                    />

                                    <Select value={selectedAssigneeId} onValueChange={setSelectedAssigneeId}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select assignee" />
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

                                <div className="space-y-2">
                                    <Label>Priority</Label>

                                    <input type="hidden" name="priority" value={selectedPriority} />

                                    <Select value={selectedPriority} onValueChange={(value) => setSelectedPriority(value as typeof selectedPriority)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select priority">
                                                {selectedPriority && (
                                                    <div className="flex items-center gap-2">
                                                        <Flag
                                                            className={cn(
                                                                'h-4 w-4',
                                                                priorityOptions.find((opt) => opt.value === selectedPriority)?.color,
                                                            )}
                                                        />
                                                        <span>{priorityOptions.find((opt) => opt.value === selectedPriority)?.label}</span>
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

                                <div className="space-y-2">
                                    <Label htmlFor="task-due-date">Due date</Label>
                                    <input type="hidden" name="due_date" value={dueDate ? dueDate.toISOString().split('T')[0] : ''} />
                                    <DatePicker id="task-due-date" value={dueDate} onChange={setDueDate} placeholder="Select due date" />
                                    <InputError message={errors.due_date as string | undefined} />
                                </div>
                            </div>

                            <DialogFooter>
                                <Button type="button" variant="outline" onClick={close}>
                                    Cancel
                                </Button>
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Creating…' : 'Create'}
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
