<?php

namespace App\Filament\Pages;

use ShuvroRoy\FilamentSpatieLaravelHealth\Pages\HealthCheckResults as BaseHealthCheckResults;

class HealthChecksResultsPage extends BaseHealthCheckResults
{

  public static function getSlug(): string
  {
    return '/core/health-checks';
  }
  
  public static function getNavigationLabel(): string
  {
    return 'Gezondheidschecks';
  }

  public function getHeading(): string
  {
      return 'Gezondheidschecks';
  }

  public static function getNavigationGroup(): ?string
  {
      return 'Applicatie-info';
  }
  
}