<?php

namespace App\Http\Middleware\Filament;

use App\Enums\Settings\UserSettings;
use Closure;
use Filament\Facades\Filament;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyUserTheme
{
    /**
     * Applies the user's theme and appearance settings to the Filament admin panel,
     * syncing it with the user's custom application settings.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            $theme = $user->settings->first()->retrieve(UserSettings::DISPLAY, 'theme');
            $appearance = $user->settings->first()->retrieve(UserSettings::DISPLAY, 'appearance');

            if (in_array(needle: $theme, haystack: array_keys(Color::all()))) {
                FilamentColor::register([
                    'primary' => constant(name: Color::class . '::' . ucfirst($theme)),
                ]);
            }

            $appearance === 'dark' || ($appearance === 'system' && $request->cookie('pref_theme') === 'dark')
                ? Filament::getPanel(id: 'admin')->darkMode(condition: true, isForced: true)
                : Filament::getPanel(id: 'admin')->darkMode(condition: false, isForced: true);
        }

        return $next($request);
    }
}
