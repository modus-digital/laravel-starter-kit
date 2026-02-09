import { Badge } from '@/shared/components/ui/badge';
import { cn } from '@/shared/lib/utils';
import { Flag } from 'lucide-react';

type TaskPriority = 'low' | 'normal' | 'high' | 'critical';

export type TaskActivityValue =
    | {
          name?: string;
          label?: string;
          value?: string;
          color?: string;
      }
    | string
    | null
    | undefined;

type ActivityBadgeProps = {
    value: TaskActivityValue;
    field?: string;
};

export function ActivityBadge({ value, field }: ActivityBadgeProps) {
    if (!value) {
        return null;
    }

    if (typeof value === 'object' && value !== null) {
        // Handle status with color badge
        if (field === 'status_id' && value.name && value.color) {
            return (
                <Badge
                    variant="outline"
                    className="ml-1 border-0"
                    style={{
                        backgroundColor: `${value.color}20`,
                        color: value.color,
                    }}
                >
                    <div className="mr-1.5 h-2 w-2 rounded-full" style={{ backgroundColor: value.color }} />
                    {value.name}
                </Badge>
            );
        }

        // Handle priority with icon
        if (field === 'priority' && value.label) {
            const priorityColor =
                {
                    low: 'text-muted-foreground',
                    normal: 'text-blue-500',
                    high: 'text-orange-500',
                    critical: 'text-red-500',
                }[value.value as TaskPriority] || 'text-muted-foreground';

            return (
                <span className="ml-1 inline-flex items-center gap-1">
                    <Flag className={cn('h-3.5 w-3.5', priorityColor)} />
                    <span>{value.label}</span>
                </span>
            );
        }
    }

    return null;
}
