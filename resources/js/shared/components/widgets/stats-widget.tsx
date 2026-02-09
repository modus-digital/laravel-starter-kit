import { Skeleton } from '@/shared/components/ui/skeleton';
import type { StatsWidgetProps } from '@/types/widgets';
import { Activity, Key, Shield, Users } from 'lucide-react';
import { Widget } from './widget';

export function StatsWidget({ data, isLoading, onRemove }: StatsWidgetProps) {
    const stats = [
        {
            label: 'Users',
            value: data?.total_users ?? 0,
            icon: Users,
            color: 'text-blue-500',
            bgColor: 'bg-blue-500/10',
        },
        {
            label: 'Roles',
            value: data?.total_roles ?? 0,
            icon: Shield,
            color: 'text-green-500',
            bgColor: 'bg-green-500/10',
        },
        {
            label: 'Permissions',
            value: data?.total_permissions ?? 0,
            icon: Key,
            color: 'text-amber-500',
            bgColor: 'bg-amber-500/10',
        },
        {
            label: 'Activities',
            value: data?.total_activities ?? 0,
            icon: Activity,
            color: 'text-purple-500',
            bgColor: 'bg-purple-500/10',
        },
    ];

    return (
        <Widget title="Statistics" description="System overview" onRemove={onRemove}>
            <div className="grid h-full grid-cols-2 gap-4 lg:grid-cols-4">
                {stats.map((stat) => (
                    <div key={stat.label} className="flex flex-col items-center justify-center gap-2 rounded-lg border p-3">
                        <div className={`rounded-full p-2 ${stat.bgColor}`}>
                            <stat.icon className={`h-5 w-5 ${stat.color}`} />
                        </div>
                        {isLoading ? <Skeleton className="h-7 w-12" /> : <span className="text-2xl font-bold">{stat.value.toLocaleString()}</span>}
                        <span className="text-xs text-muted-foreground">{stat.label}</span>
                    </div>
                ))}
            </div>
        </Widget>
    );
}
