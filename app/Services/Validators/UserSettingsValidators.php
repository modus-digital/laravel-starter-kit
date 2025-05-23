<?php

namespace App\Services\Validators;

use App\Enums\Settings\Appearance;
use App\Enums\Settings\Language;
use App\Enums\Settings\Theme;
use App\Enums\Settings\TwoFactor;
use App\Enums\Settings\UserSettings;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Contracts\Validation\Validator;
use InvalidArgumentException;

class UserSettingsValidators
{
    /**
     * Validate the user settings based on the key
     *
     * @param  UserSettings|string  $key  The key of the user setting to validate
     * @param  array<string, mixed>|string  $data  The data to validate
     * @return Validator The validator instance
     */
    public static function byKey(UserSettings|string $key, array|string $data): Validator
    {
        return match ($key) {
            UserSettings::LOCALIZATION => self::localization($data),
            UserSettings::SECURITY => self::security($data),
            UserSettings::DISPLAY => self::display($data),
            UserSettings::NOTIFICATIONS => self::notifications($data),
            default => throw new InvalidArgumentException('Invalid user setting key'),
        };
    }

    /**
     * Validate the localization settings
     *
     * @param  array<string, mixed>  $data  The data to validate
     * @return Validator The validator instance
     */
    public static function localization(array $data): Validator
    {
        $rules = [
            'locale' => 'required|string|in:' . implode(',', Language::values()),
            'timezone' => 'required|string',
            'date_format' => 'required|string',
        ];

        $data = self::ensureNullableFieldsExist($data, $rules);

        return ValidatorFacade::make(
            data: $data,
            rules: $rules
        );
    }

    /**
     * Validate the security settings
     *
     * @param  array<string, mixed>  $data  The data to validate
     * @return Validator The validator instance
     */
    public static function security(array $data): Validator
    {
        $rules = [
            'password_last_changed_at' => 'nullable|date',
            'two_factor' => 'required|array',
            'two_factor.status' => 'required|string|in:' . implode(',', TwoFactor::values()),
            'two_factor.secret' => 'string|nullable|required_if:two_factor.status,' . TwoFactor::ENABLED->value,
            'two_factor.confirmed_at' => 'date|nullable|required_if:two_factor.status,' . TwoFactor::ENABLED->value,
            'two_factor.recovery_codes' => 'array|required_if:two_factor.status,' . TwoFactor::ENABLED->value,
            'two_factor.recovery_codes.*' => 'string|required_if:two_factor.status,' . TwoFactor::ENABLED->value,
        ];

        $data = self::ensureNullableFieldsExist($data, $rules);

        return ValidatorFacade::make(
            data: $data,
            rules: $rules
        );
    }

    /**
     * Validate the display settings
     *
     * @param  array<string, mixed>  $data  The data to validate
     * @return Validator The validator instance
     */
    public static function display(array $data): Validator
    {
        $rules = [
            'appearance' => 'required|string|in:' . implode(',', Appearance::values()),
            'theme' => 'required|string|in:' . implode(',', Theme::values()),
        ];

        $data = self::ensureNullableFieldsExist($data, $rules);

        return ValidatorFacade::make(
            data: $data,
            rules: $rules
        );
    }

    /**
     * Validate the notifications settings
     *
     * @param  array<string, mixed>  $data  The data to validate
     * @return Validator The validator instance
     */
    public static function notifications(array $data): Validator
    {
        $rules = [
            'email' => 'required|boolean',
            'push' => 'required|boolean',
        ];

        $data = self::ensureNullableFieldsExist($data, $rules);

        return ValidatorFacade::make(
            data: $data,
            rules: $rules
        );
    }

    /**
     * Ensure nullable fields exist in the data
     *
     * @param  array<string, mixed>  $data  The data to validate
     * @param  array<string, mixed>  $rules  The rules to validate against
     * @return array<string, mixed> The data with nullable fields
     */
    private static function ensureNullableFieldsExist(array $data, array $rules): array
    {
        $defaults = [];

        foreach ($rules as $field => $rule) {
            // If the rule is a string and contains the 'nullable' rule, and the field isn't already in the data
            if (is_string($rule) && str_contains($rule, 'nullable') && ! array_key_exists($field, $data)) {
                $defaults[$field] = null;
            }
            // If the rule is an array and contains the 'nullable' rule, and the field isn't already in the data
            elseif (is_array($rule) && in_array('nullable', $rule) && ! array_key_exists($field, $data)) {
                $defaults[$field] = null;
            }
        }

        return array_merge($defaults, $data);
    }
}
