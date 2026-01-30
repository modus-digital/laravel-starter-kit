import { Avatar, AvatarFallback } from '@/shared/components/ui/avatar';
import { ScrollArea } from '@/shared/components/ui/scroll-area';
import { Skeleton } from '@/shared/components/ui/skeleton';
import type { ActivitiesWidgetProps } from '@/types/widgets';
import { formatDistanceToNow } from 'date-fns';
import { Widget } from './widget';

function getInitials(name: string): string {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
}

function getEventColor(event: string): string {
    switch (event) {
        case 'created':
            return 'bg-green-500';
        case 'updated':
            return 'bg-blue-500';
        case 'deleted':
            return 'bg-red-500';
        default:
            return 'bg-gray-500';
    }
}

export function ActivitiesWidget({ data, isLoading, onRemove }: ActivitiesWidgetProps) {
    if (isLoading) {
        return (
            <Widget title="Recent Activities" description="Latest activity feed" onRemove={onRemove}>
                <div className="space-y-3">
                    {[...Array(5)].map((_, i) => (
                        <div key={i} className="flex items-start gap-3">
                            <Skeleton className="h-8 w-8 rounded-full" />
                            <div className="flex-1 space-y-1">
                                <Skeleton className="h-4 w-3/4" />
                                <Skeleton className="h-3 w-1/4" />
                            </div>
                        </div>
                    ))}
                </div>
            </Widget>
        );
    }

    return (
        <Widget title="Recent Activities" description="Latest activity feed" onRemove={onRemove}>
            <ScrollArea className="h-full pr-4">
                {!data || data.length === 0 ? (
                    <div className="text-muted-foreground flex h-full items-center justify-center text-sm">No recent activities</div>
                ) : (
                    <div className="space-y-3">
                        {data.map((activity) => (
                            <div key={activity.id} className="flex items-start gap-3">
                                <div className="relative">
                                    <Avatar className="h-8 w-8">
                                        <AvatarFallback className="text-xs">
                                            {activity.causer ? getInitials(activity.causer.name) : 'SY'}
                                        </AvatarFallback>
                                    </Avatar>
                                    <span className={`absolute -right-0.5 -bottom-0.5 h-2.5 w-2.5 rounded-full border-2 border-white dark:border-gray-900 ${getEventColor(activity.event)}`} />
                                </div>
                                <div className="min-w-0 flex-1">
                                    <p className="truncate text-sm">{activity.translated_description || activity.description}</p>
                                    <p className="text-muted-foreground text-xs">
                                        {activity.causer?.name ?? 'System'} &middot;{' '}
                                        {formatDistanceToNow(new Date(activity.created_at), { addSuffix: true })}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </ScrollArea>
        </Widget>
    );
}
