import { Skeleton } from '@/shared/components/ui/skeleton';
import type { ActivityChartWidgetProps } from '@/types/widgets';
import { format, parseISO } from 'date-fns';
import { Area, AreaChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
import { Widget } from './widget';

export function ActivityChartWidget({ data, isLoading, onRemove }: ActivityChartWidgetProps) {
    if (isLoading) {
        return (
            <Widget title="Activity Trends" description="Activity over the last 30 days" onRemove={onRemove}>
                <Skeleton className="h-full w-full" />
            </Widget>
        );
    }

    const chartData =
        data?.map((item) => ({
            date: item.date,
            count: item.count,
            formattedDate: format(parseISO(item.date), 'MMM d'),
        })) ?? [];

    return (
        <Widget title="Activity Trends" description="Activity over the last 30 days" onRemove={onRemove}>
            {chartData.length === 0 ? (
                <div className="flex h-full items-center justify-center text-sm text-muted-foreground">No activity data available</div>
            ) : (
                <ResponsiveContainer width="100%" height="100%">
                    <AreaChart data={chartData} margin={{ top: 5, right: 5, left: -20, bottom: 0 }}>
                        <defs>
                            <linearGradient id="activityGradient" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="5%" stopColor="hsl(var(--primary))" stopOpacity={0.3} />
                                <stop offset="95%" stopColor="hsl(var(--primary))" stopOpacity={0} />
                            </linearGradient>
                        </defs>
                        <XAxis
                            dataKey="formattedDate"
                            axisLine={false}
                            tickLine={false}
                            tick={{ fontSize: 10 }}
                            tickMargin={8}
                            interval="preserveStartEnd"
                            className="text-muted-foreground"
                        />
                        <YAxis axisLine={false} tickLine={false} tick={{ fontSize: 10 }} tickMargin={8} className="text-muted-foreground" />
                        <Tooltip
                            content={({ active, payload }) => {
                                if (active && payload && payload.length) {
                                    const item = payload[0].payload;
                                    return (
                                        <div className="rounded-lg border bg-popover px-3 py-2 text-popover-foreground shadow-md">
                                            <p className="text-xs text-muted-foreground">{format(parseISO(item.date), 'MMMM d, yyyy')}</p>
                                            <p className="text-sm font-medium">{item.count} activities</p>
                                        </div>
                                    );
                                }
                                return null;
                            }}
                        />
                        <Area type="monotone" dataKey="count" stroke="hsl(var(--primary))" strokeWidth={2} fill="url(#activityGradient)" />
                    </AreaChart>
                </ResponsiveContainer>
            )}
        </Widget>
    );
}
