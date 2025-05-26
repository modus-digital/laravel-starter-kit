<?php

namespace App\Enums\RBAC;

/**
 * The Permission enum represents the different permissions that are available
 * in the system by default.
 */
enum Permission: string
{
    case HAS_ACCESS_TO_ADMIN_PANEL = 'Toegang tot het administrators-dashboard';
    case CAN_IMPERSONATE_USERS = 'Kan andere gebruikers imiteren';
    case CAN_ACCESS_BACKUPS = 'Kan back-ups bekijken';
    case CAN_ACCESS_HEALTH_CHECKS = 'Kan de status van de applicatie bekijken';
    case CAN_ACCESS_SETTINGS = 'Kan de instellingen bekijken';

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
            self::CAN_ACCESS_BACKUPS => 'De gebruiker kan back-ups bekijken. Deze back-ups kunnen gebruikt worden om de applicatie te herstellen naar een eerder bekende goede toestand.',
            self::CAN_ACCESS_HEALTH_CHECKS => 'De gebruiker kan de status van de applicatie bekijken. Deze status omvat onder andere de database, cache, en de applicatie zelf.',
            self::CAN_ACCESS_SETTINGS => 'De gebruiker kan de instellingen bekijken en aanpassen.',
        };
    }
}
