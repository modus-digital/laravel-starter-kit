<?php

namespace App\Enums\RBAC;

/**
 * The Permission enum represents the different permissions that are available
 * in the system by default.
 */
enum Permission: string
{
    /**
     * The permission to access the Filament admin panel.
     */
    case HAS_ACCESS_TO_ADMIN_PANEL = 'Toegang tot het administrators-dashboard';

    /**
     * The permission to impersonate other users.
     */
    case CAN_IMPERSONATE_USERS = 'Kan andere gebruikers imiteren';

    /**
     * Get the description for the permission.
     *
     * This method returns a description string based on the permission.
     * The descriptions are provided in Dutch and explain the permissions
     * and responsibilities associated with each permission.
     *
     * @return string The description of the permission.
     */
    public function getDescription(): string
    {
        return match ($this) {

            self::HAS_ACCESS_TO_ADMIN_PANEL => 'De gebruiker heeft toegang tot het administrators-dashboard, waar globale applicatie-instellingen, gebruikers en andere data beheerd kunnen worden.',

            self::CAN_IMPERSONATE_USERS => 'De gebruiker kan andere gebruikers imiteren, wat betekent dat ze als die gebruiker kunnen inloggen en het systeem kunnen gebruiken vanuit de geïmiteerde gebruiker.',
        };
    }
}
