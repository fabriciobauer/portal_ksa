@php
    $measurementId = \App\Support\Analytics::measurementId();
    $pageEvents = collect(\Illuminate\Support\Arr::wrap($analyticsPageEvents ?? []));

    if (isset($analyticsPageEvent) && is_array($analyticsPageEvent)) {
        $pageEvents->prepend($analyticsPageEvent);
    }

    $pageEvents = $pageEvents
        ->filter(fn ($event) => is_array($event) && filled($event['name'] ?? null))
        ->map(fn (array $event) => \App\Support\Analytics::pageEvent($event['name'], (array) ($event['params'] ?? [])))
        ->values()
        ->all();

    $flashedEvents = \App\Support\Analytics::flashedEvents();
@endphp

@if (\App\Support\Analytics::enabled() && $measurementId)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $measurementId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());
        gtag('config', @js($measurementId));

        window.ksaAnalytics = window.ksaAnalytics || {
            parseParams(rawParams) {
                if (!rawParams) {
                    return {};
                }

                try {
                    return JSON.parse(rawParams);
                } catch (error) {
                    return {};
                }
            },
            track(eventName, params = {}) {
                if (!eventName || typeof gtag !== 'function') {
                    return;
                }

                gtag('event', eventName, params);
            },
        };

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-ga-event]').forEach((element) => {
                const eventName = element.dataset.gaEvent;
                const trigger = element.dataset.gaTrigger || 'click';

                element.addEventListener(trigger, () => {
                    window.ksaAnalytics.track(
                        eventName,
                        window.ksaAnalytics.parseParams(element.dataset.gaParams)
                    );
                }, { passive: true });
            });

            document.querySelectorAll('[data-ga-submit-event]').forEach((form) => {
                form.addEventListener('submit', () => {
                    window.ksaAnalytics.track(
                        form.dataset.gaSubmitEvent,
                        window.ksaAnalytics.parseParams(form.dataset.gaSubmitParams)
                    );
                });
            });

            const queuedEvents = [...@js($pageEvents), ...@js($flashedEvents)];

            queuedEvents.forEach((eventPayload) => {
                if (!eventPayload || !eventPayload.name) {
                    return;
                }

                window.ksaAnalytics.track(eventPayload.name, eventPayload.params || {});
            });
        });
    </script>
@endif
