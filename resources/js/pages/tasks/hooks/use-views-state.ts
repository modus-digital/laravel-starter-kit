import tasksRoutes from '@/routes/tasks';
import { router } from '@inertiajs/react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';
import type { CreateViewPayload, TaskView } from '../types';

type UseViewsStateProps = {
    taskViews: TaskView[];
};

export function useViewsState({ taskViews }: UseViewsStateProps) {
    const { t } = useTranslation();
    // Sort views: default first, then alphabetically
    const views = useMemo(() => {
        return [...taskViews].sort((a, b) => {
            if (a.is_default !== b.is_default) {
                return a.is_default ? -1 : 1;
            }
            return a.name.localeCompare(b.name);
        });
    }, [taskViews]);

    // Determine the default active view
    const defaultViewId = views.find((v) => v.is_default)?.id ?? views[0]?.id ?? '';

    const [activeViewId, setActiveViewId] = useState(defaultViewId);
    const activeView = views.find((v) => v.id === activeViewId) ?? views[0] ?? null;

    // Track pending view creation to auto-select after redirect
    const pendingView = useRef<CreateViewPayload | null>(null);

    // Reset active view if current one no longer exists
    useEffect(() => {
        if (!activeViewId || !views.some((v) => v.id === activeViewId)) {
            setActiveViewId(defaultViewId);
        }
    }, [activeViewId, defaultViewId, views]);

    // Auto-select newly created view after page refresh
    useEffect(() => {
        const pending = pendingView.current;
        if (!pending || views.length === 0) return;

        const created = views.find((v) => v.type === pending.type && v.name === pending.name);
        if (!created) return;

        pendingView.current = null;
        setActiveViewId(created.id);
    }, [views]);

    // Create a new view via backend
    const createView = ({ type, name, status_ids }: CreateViewPayload) => {
        pendingView.current = { type, name, status_ids };
        router.post(
            tasksRoutes.views.create().url,
            { type, name, status_ids },
            {
                preserveScroll: true,
                onSuccess: () => toast.success(t('tasks.views.view_created')),
            },
        );
    };

    // Rename a view via backend
    const renameView = (viewId: string, name: string) => {
        router.patch(
            tasksRoutes.views.update(viewId).url,
            { name },
            {
                preserveScroll: true,
                onSuccess: () => toast.success(t('tasks.views.view_renamed')),
            },
        );
    };

    // Update view statuses (columns) via backend
    const updateViewStatuses = (viewId: string, statusIds: string[]) => {
        router.patch(
            tasksRoutes.views.update(viewId).url,
            { status_ids: statusIds },
            {
                preserveScroll: true,
                onSuccess: () => toast.success(t('tasks.views.view_updated')),
            },
        );
    };

    // Set a view as default via backend
    const makeDefaultView = (viewId: string) => {
        router.patch(tasksRoutes.views.makeDefault(viewId).url, {}, { preserveScroll: true });
    };

    // Delete a view via backend
    const deleteView = (viewId: string) => {
        // If the deleted view is the active view, we'll let the useEffect handle resetting
        router.delete(tasksRoutes.views.delete(viewId).url, {
            preserveScroll: true,
            onSuccess: () => toast.success(t('tasks.views.view_deleted')),
        });
    };

    return {
        views,
        activeViewId,
        activeView,
        setActiveViewId,
        createView,
        renameView,
        updateViewStatuses,
        makeDefaultView,
        deleteView,
    };
}
