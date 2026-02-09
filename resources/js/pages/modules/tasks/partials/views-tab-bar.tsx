import { Button } from '@/shared/components/ui/button';
import { Checkbox } from '@/shared/components/ui/checkbox';
import { ContextMenu, ContextMenuContent, ContextMenuItem, ContextMenuSeparator, ContextMenuTrigger } from '@/shared/components/ui/context-menu';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/shared/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/shared/components/ui/dropdown-menu';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { ScrollArea } from '@/shared/components/ui/scroll-area';
import { cn } from '@/shared/lib/utils';
import { ChevronDown, Pencil, Settings2, Star, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import type { CreateViewPayload, Status, TaskView, ViewType } from '../types';
import { viewTypes } from '../types';

type Props = {
    views: TaskView[];
    statuses: Status[];
    activeViewId: string;
    onActiveViewChange: (id: string) => void;
    onCreateView: (payload: CreateViewPayload) => void;
    onRenameView: (viewId: string, name: string) => void;
    onUpdateViewStatuses: (viewId: string, statusIds: string[]) => void;
    onMakeDefaultView: (viewId: string) => void;
    onDeleteView: (viewId: string) => void;
};

export default function ViewsTabBar({
    views,
    statuses,
    activeViewId,
    onActiveViewChange,
    onCreateView,
    onRenameView,
    onUpdateViewStatuses,
    onMakeDefaultView,
    onDeleteView,
}: Props) {
    const { t } = useTranslation();
    // Create view dialog state
    const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
    const [pendingType, setPendingType] = useState<ViewType | null>(null);
    const [name, setName] = useState('');
    const [selectedStatusIds, setSelectedStatusIds] = useState<string[]>([]);

    // Rename dialog state
    const [isRenameDialogOpen, setIsRenameDialogOpen] = useState(false);
    const [renameViewId, setRenameViewId] = useState<string | null>(null);
    const [renameName, setRenameName] = useState('');

    // Configure columns dialog state
    const [isConfigureDialogOpen, setIsConfigureDialogOpen] = useState(false);
    const [configureViewId, setConfigureViewId] = useState<string | null>(null);
    const [configureStatusIds, setConfigureStatusIds] = useState<string[]>([]);

    // Delete confirmation dialog state
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
    const [deleteViewId, setDeleteViewId] = useState<string | null>(null);

    const defaultNameByType: Record<ViewType, string> = useMemo(
        () => ({
            list: t('tasks.views.list'),
            kanban: t('tasks.views.kanban'),
            calendar: t('tasks.views.calendar'),
            gantt: t('tasks.views.gantt'),
        }),
        [t],
    );

    // Create view dialog handlers
    const openCreateDialog = (type: ViewType) => {
        const countOfType = views.filter((v) => v.type === type).length;
        const nextNumber = countOfType + 1;

        setPendingType(type);
        setName(`${defaultNameByType[type]} ${nextNumber}`);
        setSelectedStatusIds(statuses.map((s) => s.id));
        setIsCreateDialogOpen(true);
    };

    const closeCreateDialog = () => {
        setIsCreateDialogOpen(false);
        setPendingType(null);
        setName('');
        setSelectedStatusIds([]);
    };

    const toggleStatus = (statusId: string) => {
        setSelectedStatusIds((current) => (current.includes(statusId) ? current.filter((id) => id !== statusId) : [...current, statusId]));
    };

    const selectAllStatuses = () => {
        setSelectedStatusIds(statuses.map((s) => s.id));
    };

    const deselectAllStatuses = () => {
        setSelectedStatusIds([]);
    };

    const submitCreateView = () => {
        if (!pendingType || !name.trim()) return;

        onCreateView({
            type: pendingType,
            name: name.trim(),
            status_ids: selectedStatusIds,
        });

        closeCreateDialog();
    };

    const isCreateFormValid = pendingType && name.trim() && selectedStatusIds.length > 0;

    // Rename dialog handlers
    const openRenameDialog = (view: TaskView) => {
        setRenameViewId(view.id);
        setRenameName(view.name);
        setIsRenameDialogOpen(true);
    };

    const closeRenameDialog = () => {
        setIsRenameDialogOpen(false);
        setRenameViewId(null);
        setRenameName('');
    };

    const submitRenameView = () => {
        if (!renameViewId || !renameName.trim()) return;

        onRenameView(renameViewId, renameName.trim());
        closeRenameDialog();
    };

    // Configure columns dialog handlers
    const openConfigureDialog = (view: TaskView) => {
        setConfigureViewId(view.id);
        // Pre-select current view statuses, or all if none
        const currentStatusIds = view.statuses?.map((s) => s.id) ?? [];
        setConfigureStatusIds(currentStatusIds.length > 0 ? currentStatusIds : statuses.map((s) => s.id));
        setIsConfigureDialogOpen(true);
    };

    const closeConfigureDialog = () => {
        setIsConfigureDialogOpen(false);
        setConfigureViewId(null);
        setConfigureStatusIds([]);
    };

    const toggleConfigureStatus = (statusId: string) => {
        setConfigureStatusIds((current) => (current.includes(statusId) ? current.filter((id) => id !== statusId) : [...current, statusId]));
    };

    const selectAllConfigureStatuses = () => {
        setConfigureStatusIds(statuses.map((s) => s.id));
    };

    const deselectAllConfigureStatuses = () => {
        setConfigureStatusIds([]);
    };

    const submitConfigureView = () => {
        if (!configureViewId || configureStatusIds.length === 0) return;

        onUpdateViewStatuses(configureViewId, configureStatusIds);
        closeConfigureDialog();
    };

    const viewToConfigure = configureViewId ? views.find((v) => v.id === configureViewId) : null;

    // Delete dialog handlers
    const openDeleteDialog = (view: TaskView) => {
        setDeleteViewId(view.id);
        setIsDeleteDialogOpen(true);
    };

    const closeDeleteDialog = () => {
        setIsDeleteDialogOpen(false);
        setDeleteViewId(null);
    };

    const confirmDeleteView = () => {
        if (!deleteViewId) return;

        onDeleteView(deleteViewId);
        closeDeleteDialog();
    };

    const viewToDelete = deleteViewId ? views.find((v) => v.id === deleteViewId) : null;

    return (
        <>
            <div className="flex items-center gap-2 border-b border-sidebar-border/50 px-6 py-2">
                <div className="flex min-w-0 flex-1 items-center gap-2 overflow-x-auto overflow-y-hidden pr-3 whitespace-nowrap">
                    {views.map((view) => (
                        <ContextMenu key={view.id}>
                            <ContextMenuTrigger asChild>
                                <button
                                    type="button"
                                    onClick={() => onActiveViewChange(view.id)}
                                    className={cn(
                                        'relative shrink-0 rounded-md px-3 py-1.5 text-sm font-medium transition-colors',
                                        activeViewId === view.id
                                            ? 'bg-muted/50 text-foreground'
                                            : 'text-muted-foreground hover:bg-muted/30 hover:text-foreground',
                                    )}
                                >
                                    <span className="flex items-center gap-1.5">
                                        {view.is_default && <Star className="size-3 fill-current text-amber-500" />}
                                        {view.name}
                                    </span>
                                    {activeViewId === view.id && <span className="absolute inset-x-0 -bottom-[9px] h-0.5 bg-primary" />}
                                </button>
                            </ContextMenuTrigger>
                            <ContextMenuContent>
                                <ContextMenuItem onClick={() => openRenameDialog(view)}>
                                    <Pencil className="mr-2 size-4" />
                                    {t('tasks.views.rename_view')}
                                </ContextMenuItem>
                                <ContextMenuItem onClick={() => openConfigureDialog(view)}>
                                    <Settings2 className="mr-2 size-4" />
                                    {t('tasks.views.configure_columns')}
                                </ContextMenuItem>
                                <ContextMenuItem onClick={() => onMakeDefaultView(view.id)} disabled={view.is_default}>
                                    <Star className="mr-2 size-4" />
                                    {t('tasks.views.set_as_default')}
                                </ContextMenuItem>
                                <ContextMenuSeparator />
                                <ContextMenuItem variant="destructive" onClick={() => openDeleteDialog(view)} disabled={view.is_default}>
                                    <Trash2 className="mr-2 size-4" />
                                    {t('tasks.views.delete')}
                                </ContextMenuItem>
                            </ContextMenuContent>
                        </ContextMenu>
                    ))}
                </div>

                <div className="shrink-0">
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button type="button" size="sm" variant="outline" className="gap-2">
                                {t('tasks.views.create_view')}
                                <ChevronDown className="size-4 opacity-70" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            {viewTypes.map((type) => (
                                <DropdownMenuItem key={type} onClick={() => openCreateDialog(type)}>
                                    {t(`tasks.views.${type}`)}
                                </DropdownMenuItem>
                            ))}
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>

            {/* Create View Dialog */}
            <Dialog open={isCreateDialogOpen} onOpenChange={(open) => !open && closeCreateDialog()}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('tasks.views.create_view')}</DialogTitle>
                        <DialogDescription>
                            {pendingType
                                ? t('tasks.views.create_description', { type: t(`tasks.views.${pendingType}`) })
                                : t('tasks.views.create_description', { type: 'view' })}
                        </DialogDescription>
                    </DialogHeader>

                    <form
                        className="space-y-4"
                        onSubmit={(e) => {
                            e.preventDefault();
                            submitCreateView();
                        }}
                    >
                        <div className="space-y-2">
                            <Label htmlFor="create-view-name">{t('tasks.views.name')}</Label>
                            <Input
                                id="create-view-name"
                                value={name}
                                onChange={(e) => setName(e.target.value)}
                                placeholder={t('tasks.views.name_placeholder')}
                                autoFocus
                            />
                        </div>

                        <div className="space-y-2">
                            <div className="flex items-center justify-between">
                                <Label>{t('tasks.views.statuses_columns')}</Label>
                                <div className="flex gap-2">
                                    <button type="button" className="text-xs text-muted-foreground hover:text-foreground" onClick={selectAllStatuses}>
                                        {t('tasks.views.select_all')}
                                    </button>
                                    <span className="text-xs text-muted-foreground">·</span>
                                    <button
                                        type="button"
                                        className="text-xs text-muted-foreground hover:text-foreground"
                                        onClick={deselectAllStatuses}
                                    >
                                        {t('tasks.views.deselect_all')}
                                    </button>
                                </div>
                            </div>

                            <ScrollArea className="h-48 rounded-md border">
                                <div className="space-y-1 p-3">
                                    {statuses.length === 0 ? (
                                        <p className="py-2 text-center text-sm text-muted-foreground">{t('tasks.views.no_statuses')}</p>
                                    ) : (
                                        statuses.map((status) => (
                                            <label
                                                key={status.id}
                                                className="flex cursor-pointer items-center gap-3 rounded-md px-2 py-2 hover:bg-muted/50"
                                            >
                                                <Checkbox
                                                    checked={selectedStatusIds.includes(status.id)}
                                                    onCheckedChange={() => toggleStatus(status.id)}
                                                />
                                                <span className="size-3 shrink-0 rounded-full" style={{ backgroundColor: status.color }} />
                                                <span className="text-sm">{status.name}</span>
                                            </label>
                                        ))
                                    )}
                                </div>
                            </ScrollArea>

                            {selectedStatusIds.length === 0 && <p className="text-xs text-destructive">{t('tasks.views.select_at_least_one')}</p>}
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={closeCreateDialog}>
                                {t('common.actions.cancel')}
                            </Button>
                            <Button type="submit" disabled={!isCreateFormValid}>
                                {t('common.actions.create')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Rename View Dialog */}
            <Dialog open={isRenameDialogOpen} onOpenChange={(open) => !open && closeRenameDialog()}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('tasks.views.rename_view')}</DialogTitle>
                        <DialogDescription>{t('tasks.views.rename_description')}</DialogDescription>
                    </DialogHeader>

                    <form
                        className="space-y-4"
                        onSubmit={(e) => {
                            e.preventDefault();
                            submitRenameView();
                        }}
                    >
                        <div className="space-y-2">
                            <Label htmlFor="rename-view-name">{t('tasks.views.name')}</Label>
                            <Input
                                id="rename-view-name"
                                value={renameName}
                                onChange={(e) => setRenameName(e.target.value)}
                                placeholder={t('tasks.views.name_placeholder')}
                                autoFocus
                            />
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={closeRenameDialog}>
                                {t('common.actions.cancel')}
                            </Button>
                            <Button type="submit" disabled={!renameName.trim()}>
                                {t('common.actions.save')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Configure Columns Dialog */}
            <Dialog open={isConfigureDialogOpen} onOpenChange={(open) => !open && closeConfigureDialog()}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('tasks.views.configure_columns')}</DialogTitle>
                        <DialogDescription>{t('tasks.views.configure_description', { name: viewToConfigure?.name ?? '' })}</DialogDescription>
                    </DialogHeader>

                    <form
                        className="space-y-4"
                        onSubmit={(e) => {
                            e.preventDefault();
                            submitConfigureView();
                        }}
                    >
                        <div className="space-y-2">
                            <div className="flex items-center justify-between">
                                <Label>{t('tasks.views.statuses_columns')}</Label>
                                <div className="flex gap-2">
                                    <button
                                        type="button"
                                        className="text-xs text-muted-foreground hover:text-foreground"
                                        onClick={selectAllConfigureStatuses}
                                    >
                                        {t('tasks.views.select_all')}
                                    </button>
                                    <span className="text-xs text-muted-foreground">·</span>
                                    <button
                                        type="button"
                                        className="text-xs text-muted-foreground hover:text-foreground"
                                        onClick={deselectAllConfigureStatuses}
                                    >
                                        {t('tasks.views.deselect_all')}
                                    </button>
                                </div>
                            </div>

                            <ScrollArea className="h-48 rounded-md border">
                                <div className="space-y-1 p-3">
                                    {statuses.length === 0 ? (
                                        <p className="py-2 text-center text-sm text-muted-foreground">{t('tasks.views.no_statuses')}</p>
                                    ) : (
                                        statuses.map((status) => (
                                            <label
                                                key={status.id}
                                                className="flex cursor-pointer items-center gap-3 rounded-md px-2 py-2 hover:bg-muted/50"
                                            >
                                                <Checkbox
                                                    checked={configureStatusIds.includes(status.id)}
                                                    onCheckedChange={() => toggleConfigureStatus(status.id)}
                                                />
                                                <span className="size-3 shrink-0 rounded-full" style={{ backgroundColor: status.color }} />
                                                <span className="text-sm">{status.name}</span>
                                            </label>
                                        ))
                                    )}
                                </div>
                            </ScrollArea>

                            {configureStatusIds.length === 0 && <p className="text-xs text-destructive">{t('tasks.views.select_at_least_one')}</p>}
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={closeConfigureDialog}>
                                {t('common.actions.cancel')}
                            </Button>
                            <Button type="submit" disabled={configureStatusIds.length === 0}>
                                {t('common.actions.save')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Delete Confirmation Dialog */}
            <Dialog open={isDeleteDialogOpen} onOpenChange={(open) => !open && closeDeleteDialog()}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('tasks.views.delete')}</DialogTitle>
                        <DialogDescription>{t('tasks.views.delete_description', { name: viewToDelete?.name ?? '' })}</DialogDescription>
                    </DialogHeader>

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={closeDeleteDialog}>
                            {t('common.actions.cancel')}
                        </Button>
                        <Button type="button" variant="destructive" onClick={confirmDeleteView}>
                            {t('common.actions.delete')}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
