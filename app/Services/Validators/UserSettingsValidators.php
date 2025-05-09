<?php

namespace App\Services\Validators;

use App\Enums\Settings\Appearance;
use App\Enums\Settings\Language;
use App\Enums\Settings\Theme;
use App\Enums\Settings\TwoFactor;
use App\Enums\Settings\UserSettings;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;

final readonly class UserSettingsValidators
{
    public static function byKey(UserSettings $key, array $data): Validator
    {
        dump($key, $data);
        switch ($key) {
            case UserSettings::LOCALIZATION:
                return self::localization($data);

            case UserSettings::SECURITY:
                return self::security($data);

            case UserSettings::DISPLAY:
                return self::display($data);

            case UserSettings::NOTIFICATIONS:
                return self::notifications($data);

            default:
                throw new \InvalidArgumentException('Invalid user setting key');
        }
    }

    public static function localization(array $data): Validator
    {
        $rules = [
            'language' => 'required|string|in:' . implode(',', Language::values()),
            'timezone' => 'required|string',
            'date_format' => 'required|string',
        ];

        $data = self::ensureNullableFieldsExist($data, $rules);
        $validator = ValidatorFacade::make(
            data: $data,
            rules: $rules
        );

        return $validator;
    }

    public static function security(array $data): Validator
    {
        $rules = [
            'password_last_changed_at' => 'required|date',
            'two_factor' => 'required|array',
            'two_factor.status' => 'required|string|in:' . implode(',', TwoFactor::values()),
            'two_factor.secret' => 'required_if:two_factor.status,' . TwoFactor::ENABLED . '|string',
            'two_factor.confirmed_at' => 'required_if:two_factor.status,' . TwoFactor::ENABLED . '|date',
            'two_factor.recovery_codes' => 'required_if:two_factor.status,' . TwoFactor::ENABLED . '|array',
            'two_factor.recovery_codes.*' => 'required_if:two_factor.status,' . TwoFactor::ENABLED . '|string',
        ];

        $data = self::ensureNullableFieldsExist($data, $rules);
        $validator = ValidatorFacade::make(
            data: $data,
            rules: $rules
        );

        return $validator;
    }

    public static function display(array $data): Validator
    {
        $rules = [
            'appearance' => 'required|string|in:' . implode(',', Appearance::values()),
            'theme' => 'required|string|in:' . implode(',', Theme::values()),
        ];

        $data = self::ensureNullableFieldsExist($data, $rules);
        $validator = ValidatorFacade::make(
            data: $data,
            rules: $rules
        );

        return $validator;
    }

    public static function notifications(array $data): Validator
    {
        $rules = [
            'email' => 'required|boolean',
            'push' => 'required|boolean',
        ];

        $data = self::ensureNullableFieldsExist($data, $rules);
        $validator = ValidatorFacade::make(
            data: $data,
            rules: $rules
        );

        return $validator;
    }

    private static function ensureNullableFieldsExist(array $data, array $rules): array
    {
        foreach ($rules as $field => $rule) {
            // Skip wildcard rules as they don't represent a single field to default.
            if (str_contains($field, '*')) {
                continue;
            }

            $isNullable = false;
            // Check if 'nullable' is present in the rule string or array
            if (is_string($rule) && strpos($rule, 'nullable') !== false) {
                $isNullable = true;
            } elseif (is_array($rule) && in_array('nullable', $rule, true)) {
                $isNullable = true;
            }

            // If the field is nullable and doesn't exist in the data (even with dot notation),
            // set it to null in the data array.
            if ($isNullable && !Arr::has($data, $field)) {
                Arr::set($data, $field, null);
            }
        }

        return $data; // Return the directly modified data array
    }
}
