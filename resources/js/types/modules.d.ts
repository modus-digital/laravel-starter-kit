export interface ModulesConfig {
    socialite: {
        enabled: boolean;
        providers: {
            google: boolean;
            github: boolean;
            microsoft: boolean;
        };
    };
    clients: {
        enabled: boolean;
        role_management: boolean;
    };
    saas: {
        enabled: boolean;
    };
    registration: {
        enabled: boolean;
    };
    api: {
        enabled: boolean;
        documentation_path: string;
        documentation_url: string;
    };
    comments: {
        enabled: boolean;
    };
    tasks: {
        enabled: boolean;
        options: {
            list: boolean;
            kanban: boolean;
            calendar: boolean;
        };
    };
    import_export: {
        enabled: boolean;
    };
}

export interface SocialiteProvider {
    id: string;
    name: string;
}
