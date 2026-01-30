import type { WidgetGridItemProps, WidgetGridProps, WidgetLayout } from '@/types/widgets';
import { forwardRef } from 'react';
import { ResponsiveGridLayout, useContainerWidth } from 'react-grid-layout';
import 'react-grid-layout/css/styles.css';
import 'react-resizable/css/styles.css';

export function WidgetGrid({ layout, onLayoutChange, children, isEditing = true }: WidgetGridProps) {
    const { width, containerRef, mounted } = useContainerWidth();

    return (
        <div ref={containerRef} className="w-full">
            {mounted && width > 0 && (
                <ResponsiveGridLayout
                    width={width}
                    layouts={{ lg: layout, md: layout, sm: layout, xs: layout }}
                    breakpoints={{ lg: 1200, md: 996, sm: 768, xs: 480 }}
                    cols={{ lg: 12, md: 10, sm: 6, xs: 4 }}
                    rowHeight={100}
                    onLayoutChange={(currentLayout) => onLayoutChange(currentLayout as WidgetLayout[])}
                    dragConfig={{
                        enabled: isEditing,
                        handle: '.widget-drag-handle',
                        bounded: false,
                        threshold: 3,
                    }}
                    resizeConfig={{
                        enabled: isEditing,
                        handles: ['se'],
                    }}
                    margin={[16, 16]}
                    containerPadding={[0, 0]}
                >
                    {children}
                </ResponsiveGridLayout>
            )}
        </div>
    );
}

export const WidgetGridItem = forwardRef<HTMLDivElement, WidgetGridItemProps & React.HTMLAttributes<HTMLDivElement>>(
    ({ children, ...props }, ref) => {
        return (
            <div ref={ref} {...props}>
                {children}
            </div>
        );
    },
);
WidgetGridItem.displayName = 'WidgetGridItem';
