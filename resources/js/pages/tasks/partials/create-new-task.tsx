import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { DatePicker } from '@/components/ui/datepicker';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import RichTextEditor from '@/components/ui/text-editor';
import { cn } from '@/lib/utils';
import tasksRoutes from '@/routes/tasks';
import type { SharedData } from '@/types';
import { Form, usePage } from '@inertiajs/react';
import { Flag } from 'lucide-react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

const UNASSIGNED_VALUE = '__unassigned__';

export default function CreateNewTask() {
    const { t } = useTranslation();
    const [open, setOpen] = useState(false);
    const [selectedAssigneeId, setSelectedAssigneeId] = useState<string>(UNASSIGNED_VALUE);
    const [selectedPriority, setSelectedPriority] = useState<'low' | 'normal' | 'high' | 'critical'>('normal');
    const [dueDate, setDueDate] = useState<Date | undefined>(undefined);

    const { auth } = usePage<SharedData>().props;

    const priorityOptions = useMemo(
        () => [
            { value: 'low' as const, label: t('enums.task_priority.low'), color: 'text-muted-foreground' },
            { value: 'normal' as const, label: t('enums.task_priority.normal'), color: 'text-blue-500' },
            { value: 'high' as const, label: t('enums.task_priority.high'), color: 'text-orange-500' },
            { value: 'critical' as const, label: t('enums.task_priority.critical'), color: 'text-red-500' },
        ],
        [t],
    );

    const assigneeOptions = [
        { id: UNASSIGNED_VALUE, name: t('tasks.unassigned') },
        ...(auth?.user?.id !== undefined ? [{ id: String(auth.user.id), name: auth.user.name }] : []),
    ];

    const close = () => {
        setOpen(false);
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <Button type="button" size="sm" onClick={() => setOpen(true)}>
                {t('tasks.new_task')}
            </Button>

            <DialogContent className="md:max-w-3xl">
                <DialogHeader>
                    <DialogTitle>{t('tasks.create_task')}</DialogTitle>
                    <DialogDescription>{t('tasks.create_task_description')}</DialogDescription>
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
                        toast.success(t('tasks.created'));
                    }}
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-4 md:grid-cols-3">
                                <div className="space-y-2 md:col-span-3">
                                    <Label htmlFor="task-title">{t('tasks.task_title')}</Label>
                                    <Input id="task-title" name="title" required autoFocus placeholder={t('tasks.task_title_placeholder')} />
                                    <InputError message={errors.title as string | undefined} />
                                </div>

                                <div className="space-y-2 md:col-span-3">
                                    <Label htmlFor="task-description">{t('tasks.description')}</Label>
                                    <RichTextEditor name="description" />
                                    <InputError message={errors.description as string | undefined} />
                                </div>

                                <div className="space-y-2">
                                    <Label>{t('tasks.assignee')}</Label>

                                    <input
                                        type="hidden"
                                        name="assigned_to_id"
                                        value={selectedAssigneeId === UNASSIGNED_VALUE ? '' : selectedAssigneeId}
                                    />

                                    <Select value={selectedAssigneeId} onValueChange={setSelectedAssigneeId}>
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('tasks.select_assignee')} />
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
                                    <Label>{t('tasks.priority')}</Label>

                                    <input type="hidden" name="priority" value={selectedPriority} />

                                    <Select value={selectedPriority} onValueChange={(value) => setSelectedPriority(value as typeof selectedPriority)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('tasks.select_priority')}>
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
                                    <Label htmlFor="task-due-date">{t('tasks.due_date')}</Label>
                                    <input type="hidden" name="due_date" value={dueDate ? dueDate.toISOString().split('T')[0] : ''} />
                                    <DatePicker id="task-due-date" value={dueDate} onChange={setDueDate} placeholder={t('tasks.select_due_date')} />
                                    <InputError message={errors.due_date as string | undefined} />
                                </div>
                            </div>

                            <DialogFooter>
                                <Button type="button" variant="outline" onClick={close}>
                                    {t('common.actions.cancel')}
                                </Button>
                                <Button type="submit" disabled={processing}>
                                    {processing ? t('tasks.creating') : t('tasks.create')}
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
