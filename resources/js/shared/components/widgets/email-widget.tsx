import { Skeleton } from '@/shared/components/ui/skeleton';
import type { EmailWidgetProps } from '@/types/widgets';
import { CheckCircle2, Send, XCircle } from 'lucide-react';
import { Widget } from './widget';

export function EmailWidget({ data, isLoading, onRemove }: EmailWidgetProps) {
    const deliveryRate = data && data.total_sent > 0 ? Math.round((data.delivered / data.total_sent) * 100) : 0;

    const stats = [
        {
            label: 'Total Sent',
            value: data?.total_sent ?? 0,
            icon: Send,
            color: 'text-blue-500',
            bgColor: 'bg-blue-500/10',
        },
        {
            label: 'Delivered',
            value: data?.delivered ?? 0,
            icon: CheckCircle2,
            color: 'text-green-500',
            bgColor: 'bg-green-500/10',
        },
        {
            label: 'Failed',
            value: data?.failed ?? 0,
            icon: XCircle,
            color: 'text-red-500',
            bgColor: 'bg-red-500/10',
        },
    ];

    return (
        <Widget title="Email Analytics" description="Email delivery statistics" onRemove={onRemove}>
            <div className="flex h-full flex-col gap-3">
                <div className="grid grid-cols-3 gap-3">
                    {stats.map((stat) => (
                        <div key={stat.label} className="flex flex-col items-center justify-center rounded-lg border p-2 text-center">
                            <div className={`mb-1 rounded-full p-1.5 ${stat.bgColor}`}>
                                <stat.icon className={`h-4 w-4 ${stat.color}`} />
                            </div>
                            {isLoading ? (
                                <Skeleton className="my-1 h-5 w-10" />
                            ) : (
                                <span className="text-lg font-bold">{stat.value.toLocaleString()}</span>
                            )}
                            <span className="text-xs text-muted-foreground">{stat.label}</span>
                        </div>
                    ))}
                </div>
                <div className="flex flex-col gap-1">
                    <div className="flex items-center justify-between text-sm">
                        <span className="text-muted-foreground">Delivery Rate</span>
                        {isLoading ? <Skeleton className="h-4 w-10" /> : <span className="font-medium">{deliveryRate}%</span>}
                    </div>
                    <div className="h-2 w-full overflow-hidden rounded-full bg-muted">
                        {isLoading ? (
                            <Skeleton className="h-full w-full" />
                        ) : (
                            <div className="h-full bg-green-500 transition-all" style={{ width: `${deliveryRate}%` }} />
                        )}
                    </div>
                </div>
            </div>
        </Widget>
    );
}
