export type Client = {
    id: string;
    name: string;
    contact_name?: string;
    contact_email?: string;
    contact_phone?: string;
    address?: string;
    postal_code?: string;
    city?: string;
    country?: string;
    status: string;
    created_at: string;
    updated_at: string;
    deleted_at?: string;
};

export type ClientUser = {
    id: string;
    name: string;
    email: string;
    status: string;
    role?: string | null;
};

export type RoleOption = {
    id: number;
    name: string;
    label: string;
};

export type AvailableUser = {
    id: string;
    name: string;
    email: string;
};

export type ClientActivity = {
    id: string;
    description: string;
    translated_description?: string;
    translation?: {
        key: string;
        replacements: Record<string, string>;
    };
    event?: string;
    causer?: {
        id: string;
        name: string;
        email: string;
    };
    properties?: Record<string, unknown>;
    created_at: string;
};

export type PaginatedData<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};
