import { Skeleton } from '@/shared/components/ui/skeleton';
import type { ClientsWidgetProps } from '@/types/widgets';
import { Building2, CalendarPlus, UserCheck } from 'lucide-react';
import { Widget } from './widget';

export function ClientsWidget({ data, isLoading, onRemove }: ClientsWidgetProps) {
    const stats = [
        {
            label: 'Total Clients',
            value: data?.total ?? 0,
            icon: Building2,
            color: 'text-indigo-500',
            bgColor: 'bg-indigo-500/10',
        },
        {
            label: 'Active',
            value: data?.active ?? 0,
            icon: UserCheck,
            color: 'text-emerald-500',
            bgColor: 'bg-emerald-500/10',
        },
        {
            label: 'New This Month',
            value: data?.new_this_month ?? 0,
            icon: CalendarPlus,
            color: 'text-sky-500',
            bgColor: 'bg-sky-500/10',
        },
    ];

    return (
        <Widget title="Client Overview" description="Client statistics" onRemove={onRemove}>
            <div className="grid h-full grid-cols-1 gap-3">
                {stats.map((stat) => (
                    <div key={stat.label} className="flex items-center gap-3 rounded-lg border p-3">
                        <div className={`rounded-full p-2 ${stat.bgColor}`}>
                            <stat.icon className={`h-5 w-5 ${stat.color}`} />
                        </div>
                        <div className="flex-1">
                            <p className="text-muted-foreground text-xs">{stat.label}</p>
                            {isLoading ? <Skeleton className="mt-1 h-6 w-16" /> : <p className="text-xl font-bold">{stat.value.toLocaleString()}</p>}
                        </div>
                    </div>
                ))}
            </div>
        </Widget>
    );
}
