import InputError from '@/components/input-error';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DatePicker } from '@/components/ui/datepicker';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import RichTextEditor from '@/components/ui/text-editor/index';
import { RichTextRenderer } from '@/components/ui/text-editor/renderer';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import tasksRoutes from '@/routes/tasks';
import type { BreadcrumbItem, SharedData } from '@/types';
import { Form, Head, Link, router, usePage } from '@inertiajs/react';
import { format } from 'date-fns';
import { Flag, MessageSquare } from 'lucide-react';
import type { JSONContent } from 'novel';
import { useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';
import type { Activity, Status, Task, TaskActivityProperties, TaskActivityValue, TaskPriority } from './types';

type Props = {
    task: Task;
    statuses: Status[];
    activities: Activity[];
};

const UNASSIGNED_VALUE = '__unassigned__';

const toDate = (iso: string | null | undefined): Date | undefined => {
    if (!iso) return undefined;
    const d = new Date(iso);
    return Number.isNaN(d.getTime()) ? undefined : d;
};

const renderActivityBadge = (value: TaskActivityValue | undefined, field?: string) => {
    if (!value) return null;

    if (typeof value === 'object' && value !== null) {
        // Handle status with color badge
        if (field === 'status_id' && value.name && value.color) {
            return (
                <Badge
                    variant="outline"
                    className="ml-1 border-0"
                    style={{
                        backgroundColor: `${value.color}20`,
                        color: value.color,
                    }}
                >
                    <div className="mr-1.5 h-2 w-2 rounded-full" style={{ backgroundColor: value.color }} />
                    {value.name}
                </Badge>
            );
        }

        // Handle priority with icon
        if (field === 'priority' && value.label) {
            const priorityColor =
                {
                    low: 'text-muted-foreground',
                    normal: 'text-blue-500',
                    high: 'text-orange-500',
                    critical: 'text-red-500',
                }[value.value as TaskPriority] || 'text-muted-foreground';

            return (
                <span className="ml-1 inline-flex items-center gap-1">
                    <Flag className={cn('h-3.5 w-3.5', priorityColor)} />
                    <span>{value.label}</span>
                </span>
            );
        }
    }

    return null;
};

export default function Show({ task, statuses = [], activities = [] }: Props) {
    const { auth } = usePage<SharedData>().props;

    const { t } = useTranslation();

    const priorityOptions = useMemo(
        () => [
            { value: 'low' as const, label: t('enums.task_priority.low'), color: 'text-muted-foreground' },
            { value: 'normal' as const, label: t('enums.task_priority.normal'), color: 'text-blue-500' },
            { value: 'high' as const, label: t('enums.task_priority.high'), color: 'text-orange-500' },
            { value: 'critical' as const, label: t('enums.task_priority.critical'), color: 'text-red-500' },
        ],
        [t],
    );

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('tasks.title'),
            href: '/tasks',
        },
        {
            title: task.title,
            href: `/tasks/${task.id}`,
        },
    ];

    const assigneeOptions = [
        { id: UNASSIGNED_VALUE, name: t('tasks.unassigned') },
        ...(auth?.user?.id !== undefined ? [{ id: String(auth.user.id), name: auth.user.name }] : []),
    ];

    const [selectedAssigneeId, setSelectedAssigneeId] = useState<string>(task.assigned_to_id ?? UNASSIGNED_VALUE);
    const [selectedPriority, setSelectedPriority] = useState<TaskPriority>(task.priority ?? 'normal');
    const [dueDate, setDueDate] = useState<Date | undefined>(toDate(task.due_date));
    const [selectedStatusId, setSelectedStatusId] = useState<string>(task.status_id ?? statuses[0]?.id ?? '');

    // Parse description - handle both string (legacy) and JSON formats
    const parseDescription = (desc: string | null | undefined): JSONContent | undefined => {
        if (!desc) return undefined;
        try {
            // Try parsing as JSON first
            const parsed = JSON.parse(desc);
            return parsed as JSONContent;
        } catch {
            // If not JSON, treat as plain text and convert to TipTap format
            return {
                type: 'doc',
                content: [
                    {
                        type: 'paragraph',
                        content: [{ type: 'text', text: desc }],
                    },
                ],
            };
        }
    };

    const [descriptionContent, setDescriptionContent] = useState<JSONContent | undefined>(parseDescription(task.description));
    const [descriptionEditorKey, setDescriptionEditorKey] = useState(0);
    const [commentContent, setCommentContent] = useState<JSONContent | undefined>(undefined);
    const [commentEditorKey, setCommentEditorKey] = useState(0);
    // Sync description content when task changes (e.g., after save and Inertia reload)
    useEffect(() => {
        const newContent = parseDescription(task.description);
        setDescriptionContent(newContent);
        setDescriptionEditorKey((prev) => prev + 1);
    }, [task.description]);

    const handleCommentSubmit = () => {
        if (!commentContent) {
            toast.error(t('tasks.comment_error'));
            return;
        }

        router.post(
            tasksRoutes.comments.add(task.id).url,
            { comment: commentContent },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setCommentContent(undefined);
                    setCommentEditorKey((prev) => prev + 1);
                    toast.success(t('tasks.comment_added'));
                },
                onError: () => {
                    toast.error(t('tasks.comment_failed'));
                },
            },
        );
    };

    const currentStatus = statuses?.find((s) => s.id === selectedStatusId);
    const currentAssignee = assigneeOptions.find((opt) => opt.id === selectedAssigneeId);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={task.title} />

            <div className="flex h-full flex-col">
                <Form
                    key={task.id}
                    {...tasksRoutes.update.form(task.id)}
                    options={{ preserveScroll: true }}
                    disableWhileProcessing
                    onSuccess={() => {
                        toast.success(t('tasks.updated'));
                    }}
                    className="flex h-full flex-col"
                >
                    {({ processing, errors }: { processing: boolean; errors: Record<string, string | undefined> }) => (
                        <div className="flex h-full flex-col">
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
                                                        <SelectValue placeholder={t('tasks.select_assignee')}>
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
                                                    placeholder={t('tasks.select_due_date')}
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
                                                        <SelectValue placeholder={t('tasks.select_priority')}>
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
                                            <RichTextEditor
                                                key={descriptionEditorKey}
                                                initialContent={descriptionContent}
                                                name="description"
                                                onUpdate={(content) => {
                                                    setDescriptionContent(content);
                                                }}
                                                className="relative min-h-24 w-full rounded-md border border-input bg-background px-3 py-2 shadow-xs transition-[color,box-shadow] focus-within:border-ring focus-within:ring-[3px] focus-within:ring-ring/50"
                                            />
                                            <InputError message={errors.description as string | undefined} />
                                        </div>
                                    </div>
                                </ScrollArea>

                                {/* Activity Sidebar */}
                                <div className="my-4 mr-4 flex w-lg shrink-0 flex-col overflow-hidden rounded-lg border bg-muted/30">
                                    <div className="flex shrink-0 items-center justify-between border-b px-4 py-3">
                                        <h3 className="text-sm font-medium">{t('tasks.activity')}</h3>
                                        <div className="flex items-center gap-2">
                                            <Button type="button" variant="ghost" size="icon" className="h-6 w-6">
                                                <MessageSquare className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                    <div className="flex-1 overflow-y-auto">
                                        <div className="space-y-4 px-4 py-3">
                                            {activities.length === 0 ? (
                                                <div className="text-sm text-muted-foreground">No activity yet</div>
                                            ) : (
                                                activities.map((activity) => {
                                                    const props = activity.properties as TaskActivityProperties;
                                                    const isStatusChange = props.field === 'status_id';
                                                    const isPriorityChange = props.field === 'priority';
                                                    const isComment = activity.event === 'tasks.comments.created';
                                                    const commentContent = isComment && props.comment ? (props.comment as JSONContent) : null;

                                                    const userName = activity.causer?.name ?? 'System';
                                                    const userInitials = userName
                                                        .split(' ')
                                                        .map((n) => n[0])
                                                        .join('')
                                                        .toUpperCase();

                                                    return (
                                                        <div key={activity.id} className="space-y-1.5">
                                                            <div className="flex gap-2">
                                                                <Avatar className="h-6 w-6 shrink-0">
                                                                    <AvatarFallback className="text-xs">{userInitials}</AvatarFallback>
                                                                </Avatar>
                                                                <div className="flex-1 space-y-1.5">
                                                                    <div className="text-sm leading-relaxed">
                                                                        <span className="font-medium">{userName}</span>{' '}
                                                                        <span className="text-muted-foreground">{activity.description}</span>
                                                                        {(isStatusChange || isPriorityChange) && (
                                                                            <div className="mt-1.5 flex items-center gap-1.5">
                                                                                {props.old && renderActivityBadge(props.old, props.field)}
                                                                                {props.old && props.new && (
                                                                                    <span className="text-muted-foreground">→</span>
                                                                                )}
                                                                                {props.new && renderActivityBadge(props.new, props.field)}
                                                                            </div>
                                                                        )}
                                                                        {commentContent && (
                                                                            <div className="mt-2 rounded-md border bg-muted/50 p-3">
                                                                                <RichTextRenderer content={commentContent} />
                                                                            </div>
                                                                        )}
                                                                    </div>
                                                                    <div className="text-xs text-muted-foreground">
                                                                        {format(new Date(activity.created_at), 'MMM d')} at{' '}
                                                                        {format(new Date(activity.created_at), 'HH:mm')}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    );
                                                })
                                            )}
                                        </div>
                                    </div>
                                    <div className="border-t px-4 py-3">
                                        <div className="flex items-start gap-2">
                                            <div className="flex-1">
                                                <RichTextEditor
                                                    key={commentEditorKey}
                                                    initialContent={undefined}
                                                    onUpdate={(content) => {
                                                        setCommentContent(content);
                                                    }}
                                                    onSubmit={handleCommentSubmit}
                                                    className="relative max-h-48 min-h-10 w-full overflow-y-auto rounded-md border border-input bg-background px-3 py-2 shadow-xs transition-[color,box-shadow] focus-within:border-ring focus-within:ring-[3px] focus-within:ring-ring/50"
                                                />
                                            </div>
                                            <Button
                                                type="button"
                                                size="icon"
                                                className="h-8 w-8 shrink-0 self-end"
                                                onClick={handleCommentSubmit}
                                                disabled={!commentContent}
                                            >
                                                <MessageSquare className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Footer */}
                            <div className="flex items-center justify-end gap-2 border-t px-6 py-4">
                                <Button type="button" variant="outline" asChild>
                                    <Link href="/tasks">Cancel</Link>
                                </Button>
                                <Button type="submit" disabled={processing}>
                                    {processing ? t('tasks.saving') : t('tasks.save')}
                                </Button>
                            </div>
                        </div>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}
