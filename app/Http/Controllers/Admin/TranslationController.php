<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\Language;
use App\Http\Controllers\Controller;
use App\Services\TranslationService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

final class TranslationController extends Controller
{
    public function __construct(
        private readonly TranslationService $translationService
    ) {}

    public function index(Request $request): Response
    {
        $targetLanguage = $this->translationService->getTargetLanguage();
        $groups = $this->translationService->getRootGroups();

        $groupsData = collect($groups)
            ->map(function (string $group) use ($targetLanguage): array {
                $progress = $this->translationService->getTranslationProgress($targetLanguage, $group);

                return [
                    'key' => $group,
                    'name' => Str::headline($group),
                    'status' => $progress['total'] > 0 && $progress['missing'] === 0,
                    'missing' => $progress['missing'],
                    'total' => $progress['total'],
                    'translated' => $progress['translated'],
                ];
            })
            ->sortBy('key')
            ->values()
            ->all();

        $availableLanguages = collect($this->translationService->getAvailableLanguages())
            ->mapWithKeys(fn (string $code): array => [
                $code => Language::tryFrom($code)?->label() ?? Str::headline($code),
            ])
            ->all();

        return Inertia::render('core/admin/translations/index', [
            'groups' => $groupsData,
            'availableLanguages' => $availableLanguages,
            'targetLanguage' => $targetLanguage,
        ]);
    }

    public function show(string $group, Request $request): Response
    {
        $targetLanguage = $this->translationService->getTargetLanguage();

        $englishGroup = data_get($this->translationService->getLanguageFile('en'), $group, []);
        $targetGroup = data_get($this->translationService->getLanguageFile($targetLanguage), $group, []);

        if (is_array($englishGroup)) {
            $englishGroup = $this->translationService->filterGroupTranslationsByModules($englishGroup, $group);
        }

        if (is_array($targetGroup)) {
            $targetGroup = $this->translationService->filterGroupTranslationsByModules($targetGroup, $group);
        }

        $englishFlat = $this->translationService->flattenTranslations(is_array($englishGroup) ? $englishGroup : []);
        $targetFlat = $this->translationService->flattenTranslations(is_array($targetGroup) ? $targetGroup : []);

        $translations = collect($englishFlat)
            ->map(fn (mixed $value, string $key): array => [
                'key' => $key,
                'full_key' => ($group !== '' && $group !== '0' ? $group.'.' : '').$key,
                'english' => $value,
                'translation' => $targetFlat[$key] ?? '',
            ])
            ->values()
            ->all();

        $availableLanguages = collect($this->translationService->getAvailableLanguages())
            ->mapWithKeys(fn (string $code): array => [
                $code => Language::tryFrom($code)?->label() ?? Str::headline($code),
            ])
            ->all();

        $progress = $this->translationService->getTranslationProgress($targetLanguage, $group);

        return Inertia::render('core/admin/translations/[group]/index', [
            'group' => $group,
            'groupName' => Str::headline($group),
            'translations' => $translations,
            'availableLanguages' => $availableLanguages,
            'targetLanguage' => $targetLanguage,
            'progress' => $progress,
        ]);
    }

    public function update(Request $request, string $group): RedirectResponse
    {
        $request->validate([
            'key' => ['required', 'string'],
            'translation' => ['required', 'string'],
        ]);

        $targetLanguage = $this->translationService->getTargetLanguage();
        $this->translationService->setTranslation($targetLanguage, $request->key, $request->translation);

        return redirect()->back()
            ->with('success', __('admin.translations.updated_successfully'));
    }

    public function quickTranslate(string $group): Response|RedirectResponse
    {
        $targetLanguage = $this->translationService->getTargetLanguage();
        $missingTranslations = $this->translationService->getMissingTranslations($targetLanguage, $group);

        if ($missingTranslations === []) {
            return redirect()->route('admin.translations.show', $group)
                ->with('success', __('admin.translations.notifications.all_translations_complete_group.title'));
        }

        $currentKey = array_key_first($missingTranslations);
        $currentEnglish = $missingTranslations[$currentKey] ?? '';

        $availableLanguages = collect($this->translationService->getAvailableLanguages())
            ->mapWithKeys(fn (string $code): array => [
                $code => Language::tryFrom($code)?->label() ?? Str::headline($code),
            ])
            ->all();

        $progress = $this->translationService->getTranslationProgress($targetLanguage, $group);

        return Inertia::render('core/admin/translations/[group]/quick-translate', [
            'group' => $group,
            'groupName' => Str::headline($group),
            'currentKey' => $currentKey,
            'currentEnglish' => $currentEnglish,
            'remainingCount' => count($missingTranslations),
            'availableLanguages' => $availableLanguages,
            'targetLanguage' => $targetLanguage,
            'progress' => $progress,
        ]);
    }

    public function saveQuickTranslate(Request $request, string $group): RedirectResponse
    {
        $request->validate([
            'translation_key' => ['required', 'string'],
            'translation' => ['required', 'string'],
        ]);

        $targetLanguage = $this->translationService->getTargetLanguage();

        // Save the translation
        $this->translationService->setTranslation($targetLanguage, $request->translation_key, $request->translation);

        // Verify it was saved by reading it back
        $saved = $this->translationService->getTranslation($targetLanguage, $request->translation_key);

        // Check if there are more missing translations
        $missingTranslations = $this->translationService->getMissingTranslations($targetLanguage, $group);

        if ($missingTranslations === []) {
            return redirect()->route('admin.translations.show', $group)
                ->with('success', __('admin.translations.notifications.all_translations_complete_group.title'));
        }

        return redirect()->route('admin.translations.quick-translate', $group);
    }

    public function createLanguage(Request $request): RedirectResponse
    {
        $request->validate([
            'language_code' => ['required', 'string', 'max:2', 'regex:/^[a-z]{2}$/'],
        ]);

        if ($this->translationService->languageExists($request->language_code)) {
            return redirect()->back()
                ->withErrors(['language_code' => __('admin.translations.notifications.language_exists.body')]);
        }

        try {
            $this->translationService->createLanguage($request->language_code);

            return redirect()->back()
                ->with('success', __('admin.translations.notifications.language_created.title'));
        } catch (Exception $e) {
            return redirect()->back()
                ->withErrors(['language_code' => __('admin.translations.notifications.language_creation_failed.body')]);
        }
    }

    public function setTargetLanguage(Request $request): RedirectResponse
    {
        $request->validate([
            'language' => ['required', 'string'],
        ]);

        try {
            $this->translationService->setTargetLanguage($request->language);

            return redirect()->back();
        } catch (InvalidArgumentException $e) {
            return redirect()->back()
                ->withErrors(['language' => $e->getMessage()]);
        }
    }
}
