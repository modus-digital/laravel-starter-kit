import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

// Get all available languages from @lang
const languageModules = import.meta.glob<{ default: Record<string, unknown> }>('@lang/*.json', { eager: true });
const languages: Record<string, { translation: Record<string, unknown> }> = {};

for (const path in languageModules) {
    // Example: Extract 'en' from '@lang/en.json' -> 'en'
    const match = path.match(/\/([\w-]+)\.json$/);

    if (match) {
        const lang = match[1];
        languages[lang] = { translation: languageModules[path].default };
    }
}

i18n.use(initReactI18next).init({
    resources: { ...languages },
    lng: 'en',

    interpolation: {
        escapeValue: false,
        prefix: ':',
        suffix: '',
        prefixEscaped: ':',
        suffixEscaped: '(?=[^a-zA-Z0-9_]|$)',
    },

    fallbackLng: 'en',
});

export default i18n;
