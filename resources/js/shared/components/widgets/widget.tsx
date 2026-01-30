import { Button } from '@/shared/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/shared/components/ui/card';
import { cn } from '@/shared/lib/utils';
import type { WidgetProps } from '@/types/widgets';
import { GripVertical, X } from 'lucide-react';

export function Widget({ title, description, children, className, isLoading, onRemove }: WidgetProps) {
    return (
        <Card className={cn('flex h-full flex-col overflow-hidden', className)}>
            <CardHeader className="flex-row items-center justify-between space-y-0 pb-2">
                <GripVertical className="widget-drag-handle h-5 w-5 cursor-grab text-muted-foreground active:cursor-grabbing" />
                <div className="flex-1">
                    <CardTitle className="text-base font-semibold">{title}</CardTitle>
                    {description && <CardDescription className="text-xs">{description}</CardDescription>}
                </div>
                <div className="flex shrink-0 items-center gap-2">
                    {onRemove && (
                        <Button variant="ghost" size="sm" onClick={onRemove} className="h-8 w-8 p-0">
                            <X className="h-4 w-4" />
                        </Button>
                    )}
                </div>
            </CardHeader>
            <CardContent className="flex-1 overflow-auto pt-0">{children}</CardContent>
        </Card>
    );
}
