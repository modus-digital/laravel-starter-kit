<?php

namespace App\Services\Validators;

use App\Enums\Settings\Appearance;
use App\Enums\Settings\Language;
use App\Enums\Settings\Theme;
use App\Enums\Settings\TwoFactor;
use App\Enums\Settings\UserSettings;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UserSettingsValidators
{
    public static function byKey(UserSettings $key, array $data): Validator
    {
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
            'locale' => 'required|string|in:' . implode(',', Language::values()),
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
            'password_last_changed_at' => 'nullable|date',
            'two_factor' => 'required|array',
            'two_factor.status' => 'required|string|in:' . implode(',', TwoFactor::values()),
            'two_factor.secret' => 'string|nullable|required_if:two_factor.status,' . TwoFactor::ENABLED->value,
            'two_factor.confirmed_at' => 'date|nullable|required_if:two_factor.status,' . TwoFactor::ENABLED->value,
            'two_factor.recovery_codes' => 'array|required_if:two_factor.status,' . TwoFactor::ENABLED->value,
            'two_factor.recovery_codes.*' => 'string|required_if:two_factor.status,' . TwoFactor::ENABLED->value,
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
        $defaults = [];

        foreach ($rules as $field => $rule) {
            // If the rule is a string and contains the 'nullable' rule, and the field isn't already in the data
            if (is_string($rule) && strpos($rule, 'nullable') !== false && !array_key_exists($field, $data)) {
                $defaults[$field] = null;
            }
            // If the rule is an array and contains the 'nullable' rule, and the field isn't already in the data
            elseif (is_array($rule) && in_array('nullable', $rule) && !array_key_exists($field, $data)) {
                $defaults[$field] = null;
            }
        }

        return array_merge($defaults, $data);
    }
}
