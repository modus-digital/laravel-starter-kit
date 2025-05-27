import '../../vendor/masmerise/livewire-toaster/resources/js';
import * as Sentry from '@sentry/browser';
import axios from 'axios';

// Setup Sentry for the frontend-side of the application
axios.get('/api/sentry').then( response => {
    const { environment, sentry, enabled } = response.data;

    if (enabled) {
        const replaysSessionSampleRate: number = environment === 'production' ? 0.1 : 0.75;
        const replaysOnErrorSampleRate: number = 1.0;

        const shouldEnableFeedback: boolean = environment !== 'production' || window.location.href.includes('/admin');
        const feedbackIntegration = shouldEnableFeedback
            ? [Sentry.feedbackIntegration({ colorScheme: 'system' })]
            : [];

        Sentry.init({
            dsn: sentry.dsn,
            integrations: [...feedbackIntegration],
            replaysSessionSampleRate,
            replaysOnErrorSampleRate,
        });
    }
})
