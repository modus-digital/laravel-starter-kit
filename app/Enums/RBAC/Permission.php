<?php

declare(strict_types=1);

namespace App\Enums\RBAC;

enum Permission: string
{
    case ACCESS_CONTROL_PANEL = 'can_access_control_panel';
    case IMPERSONATE_USERS = 'can_impersonate_users';
    case ACCESS_BACKUPS = 'can_access_backups';
    case ACCESS_HEALTH_CHECK = 'can_access_health_check';
    case ACCESS_ACTIVITY_LOGS = 'can_access_activity_logs';
    case MANAGE_SETTINGS = 'can_manage_settings';

    public function getFilamentColor(): string
    {
        return 'primary';
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::ACCESS_CONTROL_PANEL => __('enums.rbac.permission.label.access_control_panel'),
            self::IMPERSONATE_USERS => __('enums.rbac.permission.label.impersonate_users'),
            self::ACCESS_BACKUPS => __('enums.rbac.permission.label.access_backups'),
            self::ACCESS_HEALTH_CHECK => __('enums.rbac.permission.label.access_health_check'),
            self::ACCESS_ACTIVITY_LOGS => __('enums.rbac.permission.label.access_activity_logs'),
            self::MANAGE_SETTINGS => __('enums.rbac.permission.label.manage_settings'),
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::ACCESS_CONTROL_PANEL => __('enums.rbac.permission.description.access_control_panel'),
            self::IMPERSONATE_USERS => __('enums.rbac.permission.description.impersonate_users'),
            self::ACCESS_BACKUPS => __('enums.rbac.permission.description.access_backups'),
            self::ACCESS_HEALTH_CHECK => __('enums.rbac.permission.description.access_health_check'),
            self::ACCESS_ACTIVITY_LOGS => __('enums.rbac.permission.description.access_activity_logs'),
            self::MANAGE_SETTINGS => __('enums.rbac.permission.description.manage_settings'),
        };
    }
}
