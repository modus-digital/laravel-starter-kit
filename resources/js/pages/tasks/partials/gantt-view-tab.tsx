import {
    GanttFeatureItem,
    GanttFeatureList,
    GanttFeatureListGroup,
    GanttHeader,
    GanttProvider,
    GanttSidebar,
    GanttSidebarGroup,
    GanttSidebarItem,
    GanttTimeline,
    GanttToday,
    type GanttFeature,
    type GanttStatus,
} from '@/components/gantt-view';

type Props = {
    ganttStatuses: GanttStatus[];
    ganttFeatures: GanttFeature[];
    onMove: (id: string, startAt: Date, endAt: Date) => void;
    onOpenTask: (taskId: string) => void;
};

export default function GanttViewTab({ ganttStatuses, ganttFeatures, onMove, onOpenTask }: Props) {
    return (
        <GanttProvider range="daily" zoom={100} className="h-136">
            <GanttSidebar>
                {ganttStatuses.map((status) => (
                    <GanttSidebarGroup key={status.id} name={status.name}>
                        {ganttFeatures
                            .filter((f) => f.status.id === status.id)
                            .map((feature) => (
                                <GanttSidebarItem key={feature.id} feature={feature} onSelectItem={onOpenTask} />
                            ))}
                    </GanttSidebarGroup>
                ))}
            </GanttSidebar>

            <GanttTimeline>
                <GanttHeader />
                <GanttToday className="bg-primary" />

                <GanttFeatureList>
                    {ganttStatuses.map((status) => (
                        <GanttFeatureListGroup key={status.id}>
                            {ganttFeatures
                                .filter((f) => f.status.id === status.id)
                                .map((feature) => (
                                    <GanttFeatureItem
                                        key={feature.id}
                                        {...feature}
                                        onMove={(id, startAt, endAt) => endAt && onMove(id, startAt, endAt)}
                                    />
                                ))}
                        </GanttFeatureListGroup>
                    ))}
                </GanttFeatureList>
            </GanttTimeline>
        </GanttProvider>
    );
}
