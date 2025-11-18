import "i18next";
import type en from "@lang/en.json";

declare module "i18next" {
    interface CustomTypeOptions {
        defaultNS: 'en';
        resources: {
            en: typeof en;
        };
    }
}