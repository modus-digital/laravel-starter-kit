export const viewTypes = ['list', 'kanban', 'calendar', 'gantt'] as const;

export type ViewType = (typeof viewTypes)[number];

export type TaskType = 'task' | 'bug' | 'feature' | 'documentation' | 'other';

export type TaskPriority = 'low' | 'normal' | 'high' | 'critical';

export type Task = {
    id: string;
    taskable_id?: string;
    taskable_type?: string;
    title: string;
    description?: string | null;
    type?: TaskType;
    priority?: TaskPriority;
    status_id: string;
    order?: number | null;
    due_date?: string | null; // ISO 8601 datetime string
    completed_at?: string | null; // ISO 8601 datetime string
    created_by_id?: string | null;
    assigned_to_id?: string | null;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string | null;
};

export type TaskView = {
    id: string;
    taskable_type?: string;
    taskable_id?: string;
    is_default?: boolean;
    name: string;
    slug?: string;
    type: ViewType;
    metadata?: Record<string, unknown> | null;
    statuses?: Status[];
};

export type CreateViewPayload = {
    type: ViewType;
    name: string;
    status_ids: string[];
};

export type Status = {
    id: string;
    name: string;
    color: string;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string | null;
};

export type ListTask = {
    id: string;
    name: string;
    statusId: string;
};

export type KanbanTask = {
    id: string;
    name: string;
    column: string;
};

export type CalendarTask = {
    id: string;
    title: string;
    dueDateISO: string; // YYYY-MM-DD
    statusId: string;
};

export type TaskActivityValue =
    | {
          id?: string;
          name?: string;
          color?: string;
          value?: string;
          label?: string;
      }
    | string
    | null;

export type TaskActivityProperties = {
    task?: {
        id: string;
        title: string;
    };
    field?: string;
    old?: TaskActivityValue;
    new?: TaskActivityValue;
    comment?: unknown; // JSONContent from TipTap
    issuer?: {
        name: string;
        email?: string;
        ip_address?: string;
        user_agent?: string;
    };
    [key: string]: unknown;
};

export type Activity = {
    id: number;
    log_name: string;
    description: string | null;
    translated_description?: string;
    translation?: {
        key: string;
        replacements: Record<string, string>;
    };
    subject_type: string;
    subject_id: string;
    event: string | null;
    causer_type: string | null;
    causer_id: string | null;
    properties: TaskActivityProperties | Record<string, unknown>;
    created_at: string;
    updated_at: string;
    causer?: {
        id: string;
        name: string;
        email: string;
    } | null;
};
