export { ActivitiesWidget } from './activities-widget';
export { ActivityChartWidget } from './activity-chart-widget';
export { ClientsWidget } from './clients-widget';
export { EmailWidget } from './email-widget';
export { StatsWidget } from './stats-widget';
export { Widget } from './widget';
export { WidgetDrawer } from './widget-drawer';
export { WidgetGrid, WidgetGridItem } from './widget-grid';

// Re-export types from central types file
export type {
    ActivitiesWidgetProps,
    ActivityChartWidgetProps,
    ActivityItem,
    ActivityTrend,
    AvailableWidget,
    ClientStats,
    ClientsWidgetProps,
    EmailStats,
    EmailWidgetProps,
    StatsData,
    StatsWidgetProps,
    WidgetData,
    WidgetDrawerProps,
    WidgetGridItemProps,
    WidgetGridProps,
    WidgetLayout,
    WidgetProps,
} from '@/types/widgets';
