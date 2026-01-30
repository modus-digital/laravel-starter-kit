import { Button } from '@/shared/components/ui/button';
import { Card, CardDescription, CardHeader, CardTitle } from '@/shared/components/ui/card';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from '@/shared/components/ui/sheet';
import type { WidgetDrawerProps } from '@/types/widgets';
import { Plus } from 'lucide-react';

export function WidgetDrawer({ open, onOpenChange, availableWidgets, onAddWidget }: WidgetDrawerProps) {
    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent side="right" className="w-full rounded-lg sm:max-w-md">
                <SheetHeader>
                    <SheetTitle>Add Widget</SheetTitle>
                    <SheetDescription>Choose a widget to add to your dashboard</SheetDescription>
                </SheetHeader>

                <div className="mt-6 space-y-4 px-4">
                    {availableWidgets.length === 0 ? (
                        <div className="py-8 text-center text-muted-foreground">
                            <p>All available widgets have been added</p>
                        </div>
                    ) : (
                        availableWidgets.map((widget) => (
                            <Card key={widget.id} className="cursor-pointer transition-colors hover:bg-muted/50">
                                <CardHeader>
                                    <div className="flex items-start justify-between gap-4">
                                        <div className="flex-1">
                                            <CardTitle className="text-base">{widget.name}</CardTitle>
                                            <CardDescription className="mt-1">{widget.description}</CardDescription>
                                        </div>
                                        <Button size="sm" onClick={() => onAddWidget(widget.id)}>
                                            <Plus className="h-4 w-4" />
                                        </Button>
                                    </div>
                                </CardHeader>
                            </Card>
                        ))
                    )}
                </div>
            </SheetContent>
        </Sheet>
    );
}
