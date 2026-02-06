import type { ReactNode } from 'react';

// ============================================================================
// Widget Layout & Grid Types
// ============================================================================

export interface WidgetLayout {
    i: string;
    x: number;
    y: number;
    w: number;
    h: number;
    minH?: number;
    minW?: number;
    maxH?: number;
    maxW?: number;
    static?: boolean;
    isDraggable?: boolean;
    isResizable?: boolean;
}

export interface WidgetGridProps {
    layout: WidgetLayout[];
    onLayoutChange: (layout: WidgetLayout[]) => void;
    children: ReactNode;
    isEditing?: boolean;
}

export interface WidgetGridItemProps {
    children: ReactNode;
}

// ============================================================================
// Widget Configuration Types
// ============================================================================

export interface AvailableWidget {
    id: string;
    name: string;
    description: string;
    defaultSize: {
        w: number;
        h: number;
    };
}

export interface WidgetProps {
    title: string;
    description?: string;
    children: ReactNode;
    className?: string;
    isLoading?: boolean;
    onRemove?: () => void;
}

export interface WidgetDrawerProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    availableWidgets: AvailableWidget[];
    onAddWidget: (widgetId: string) => void;
}

// ============================================================================
// Widget Data Types
// ============================================================================

export interface StatsData {
    total_users: number;
    total_roles: number;
    total_permissions: number;
    total_activities: number;
}

export interface ActivityItem {
    id: string;
    description: string;
    translated_description?: string;
    event: string;
    causer?: {
        id: string;
        name: string;
        email: string;
    } | null;
    created_at: string;
}

export interface ClientStats {
    total: number;
    active: number;
    new_this_month: number;
}

export interface EmailStats {
    total_sent: number;
    delivered: number;
    failed: number;
}

export interface ActivityTrend {
    date: string;
    count: number;
}

export interface WidgetData {
    stats?: StatsData;
    recentActivities?: ActivityItem[];
    clientStats?: ClientStats;
    emailStats?: EmailStats;
    activityTrends?: ActivityTrend[];
}

// ============================================================================
// Widget Component Props
// ============================================================================

export interface StatsWidgetProps {
    data?: StatsData;
    isLoading?: boolean;
    onRemove?: () => void;
}

export interface ActivitiesWidgetProps {
    data?: ActivityItem[];
    isLoading?: boolean;
    onRemove?: () => void;
}

export interface ClientsWidgetProps {
    data?: ClientStats;
    isLoading?: boolean;
    onRemove?: () => void;
}

export interface EmailWidgetProps {
    data?: EmailStats;
    isLoading?: boolean;
    onRemove?: () => void;
}

export interface ActivityChartWidgetProps {
    data?: ActivityTrend[];
    isLoading?: boolean;
    onRemove?: () => void;
}
