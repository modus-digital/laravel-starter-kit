<?php

namespace App\Enums\RBAC;

/**
 * The Role enum represents the different roles that are available
 * in the system by default.
 */
enum Role: string {
  
  /**
   * The super admin role.
   */
  case SUPER_ADMIN = 'Super-administrator';
  
  /**
   * The default role for users.
   */
  case USER = 'Gebruiker';

  /**
   * Get the description for the role.
   *
   * This method returns a description string based on the role.
   * The descriptions are provided in Dutch and explain the permissions
   * and responsibilities associated with each role.
   *
   * @return string The description of the role.
   */
  public function getDescription(): string
  {
    return match ($this) {
      self::SUPER_ADMIN => 'Als super-administrator heb je toegang tot alle functies, features en instellingen van de applicatie.',
      self::USER => 'Als gebruiker heb je standaard toegang tot de applicatie.',
    };
  }
}