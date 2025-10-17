<?php

declare(strict_types=1);

namespace ModusDigital\SocialAuthentication\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use ModusDigital\SocialAuthentication\Enums\AuthenticationProvider;

final class SocialiteProvidersSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            [
                'name' => 'Google',
                'provider' => AuthenticationProvider::GOOGLE->value,
                'client_id' => null,
                'client_secret' => null,
                'redirect_uri' => null,
                'is_enabled' => false,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'GitHub',
                'provider' => AuthenticationProvider::GITHUB->value,
                'client_id' => null,
                'client_secret' => null,
                'redirect_uri' => null,
                'is_enabled' => false,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Facebook',
                'provider' => AuthenticationProvider::FACEBOOK->value,
                'client_id' => null,
                'client_secret' => null,
                'redirect_uri' => null,
                'is_enabled' => false,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($providers as $provider) {
            DB::table('oauth_providers')->updateOrInsert(
                ['provider' => $provider['provider']],
                $provider
            );
        }
    }
}
