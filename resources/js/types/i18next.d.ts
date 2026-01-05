import type en from '@lang/en.json';
import 'i18next';

declare module 'i18next' {
    interface CustomTypeOptions {
        defaultNS: 'en';
        resources: {
            en: typeof en;
        };
    }
}
