import { index as dashboard } from '@/routes/admin';
import { update as updateLayout } from '@/routes/admin/dashboard/layout';
import { Button } from '@/shared/components/ui/button';
import { Card } from '@/shared/components/ui/card';
import {
    ActivitiesWidget,
    ActivityChartWidget,
    ClientsWidget,
    EmailWidget,
    StatsWidget,
    WidgetDrawer,
    WidgetGrid,
    WidgetGridItem,
} from '@/shared/components/widgets';
import AdminLayout from '@/shared/layouts/admin/layout';
import { type BreadcrumbItem } from '@/types';
import type { AvailableWidget, WidgetData, WidgetLayout } from '@/types/widgets';
import { Head, usePage, WhenVisible } from '@inertiajs/react';
import axios from 'axios';
import { LayoutGrid, Plus } from 'lucide-react';
import { useCallback, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useDebouncedCallback } from 'use-debounce';

interface PageProps {
    layout: WidgetLayout[];
    availableWidgets: AvailableWidget[];
    widgetData?: WidgetData;
    [key: string]: unknown;
}

function WidgetRenderer({ type, data, isLoading, onRemove }: { type: string; data?: WidgetData; isLoading: boolean; onRemove?: () => void }) {
    switch (type) {
        case 'stats':
            return <StatsWidget data={data?.stats} isLoading={isLoading} onRemove={onRemove} />;
        case 'activities':
            return <ActivitiesWidget data={data?.recentActivities} isLoading={isLoading} onRemove={onRemove} />;
        case 'clients':
            return <ClientsWidget data={data?.clientStats} isLoading={isLoading} onRemove={onRemove} />;
        case 'email':
            return <EmailWidget data={data?.emailStats} isLoading={isLoading} onRemove={onRemove} />;
        case 'activity-chart':
            return <ActivityChartWidget data={data?.activityTrends} isLoading={isLoading} onRemove={onRemove} />;
        default:
            return <div className="flex h-full items-center justify-center text-muted-foreground">Unknown widget: {type}</div>;
    }
}

export default function AdminDashboard() {
    const { t } = useTranslation();
    const { layout: initialLayout, availableWidgets, widgetData } = usePage<PageProps>().props;
    const [currentLayout, setCurrentLayout] = useState<WidgetLayout[]>(initialLayout);
    const [isDrawerOpen, setIsDrawerOpen] = useState<boolean>(false);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('admin.title', 'Admin'),
            href: dashboard().url,
        },
        {
            title: t('dashboard.title', 'Dashboard'),
            href: '',
        },
    ];

    const addedWidgetIds = useMemo(() => currentLayout.map((item) => item.i), [currentLayout]);
    const availableToAdd = useMemo(() => availableWidgets.filter((w) => !addedWidgetIds.includes(w.id)), [availableWidgets, addedWidgetIds]);

    const saveLayout = useDebouncedCallback(async (newLayout: WidgetLayout[]) => {
        try {
            await axios.put(updateLayout().url, {
                layout: newLayout.map((item) => ({
                    i: item.i,
                    x: Math.round(item.x),
                    y: Math.round(item.y),
                    w: Math.round(item.w),
                    h: Math.round(item.h),
                    minH: item.minH,
                    minW: item.minW,
                })),
            });
        } catch (error: unknown) {
            const errorMessage = error instanceof Error ? error.message : 'Unknown error';
            console.error('Failed to save layout:', error, errorMessage);
        }
    }, 1000);

    const handleLayoutChange = useCallback(
        (newLayout: WidgetLayout[]) => {
            setCurrentLayout(newLayout);
            saveLayout(newLayout);
        },
        [saveLayout],
    );

    const handleAddWidget = useCallback(
        (widgetId: string) => {
            const widget = availableWidgets.find((w) => w.id === widgetId);
            if (!widget) return;

            // Find the next available position
            const maxY = currentLayout.length > 0 ? Math.max(...currentLayout.map((item) => item.y + item.h)) : 0;

            const newWidget: WidgetLayout = {
                i: widget.id,
                x: 0,
                y: maxY,
                w: widget.defaultSize.w,
                h: widget.defaultSize.h,
                minH: 2,
                minW: 2,
            };

            const newLayout = [...currentLayout, newWidget];
            setCurrentLayout(newLayout);
            saveLayout(newLayout);
            setIsDrawerOpen(false);
        },
        [availableWidgets, currentLayout, saveLayout],
    );

    const handleRemoveWidget = useCallback(
        (widgetId: string) => {
            const newLayout = currentLayout.filter((item) => item.i !== widgetId);
            setCurrentLayout(newLayout);
            saveLayout(newLayout);
        },
        [currentLayout, saveLayout],
    );

    const isLoading = !widgetData;

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={t('admin.dashboard.title', 'Admin Dashboard')} />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
                {/* Header with Add Widget Button */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">{t('admin.dashboard.title', 'Dashboard')}</h1>
                        <p className="text-sm text-muted-foreground">{t('admin.dashboard.description', 'Customize your dashboard')}</p>
                    </div>
                    <Button onClick={() => setIsDrawerOpen(true)}>
                        <Plus className="mr-2 h-4 w-4" />
                        Add Widget
                    </Button>
                </div>

                {/* Empty State */}
                {currentLayout.length === 0 && (
                    <Card className="flex flex-col items-center justify-center py-12">
                        <LayoutGrid className="mb-4 h-12 w-12 text-muted-foreground" />
                        <h3 className="mb-2 text-lg font-semibold">No widgets added yet</h3>
                        <p className="mb-4 text-sm text-muted-foreground">Start customizing your dashboard by adding widgets</p>
                        <Button onClick={() => setIsDrawerOpen(true)}>
                            <Plus className="mr-2 h-4 w-4" />
                            Add Your First Widget
                        </Button>
                    </Card>
                )}

                {/* Widget Grid */}
                {currentLayout.length > 0 && (
                    <WhenVisible fallback={<div className="h-full" />}>
                        <WidgetGrid layout={currentLayout} onLayoutChange={handleLayoutChange}>
                            {currentLayout.map((item) => (
                                <WidgetGridItem key={item.i}>
                                    <WidgetRenderer
                                        type={item.i}
                                        data={widgetData}
                                        isLoading={isLoading}
                                        onRemove={() => handleRemoveWidget(item.i)}
                                    />
                                </WidgetGridItem>
                            ))}
                        </WidgetGrid>
                    </WhenVisible>
                )}
            </div>

            {/* Widget Drawer */}
            <WidgetDrawer open={isDrawerOpen} onOpenChange={setIsDrawerOpen} availableWidgets={availableToAdd} onAddWidget={handleAddWidget} />
        </AdminLayout>
    );
}
