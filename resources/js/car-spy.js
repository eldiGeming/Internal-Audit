/**
 * CAR page: remembers which CAR sheet is active, either the one picked from the list / jump bar or the
 * one being read while scrolling, so that sheet and its jump button can be highlighted.
 * Used as x-data="carSpy" around the CAR list; sheets carry [data-car-sheet] and an id like "car-001".
 */
document.addEventListener('alpine:init', () => {
    window.Alpine.data('carSpy', () => ({
        active: null,
        observer: null,

        init() {
            this.active = window.location.hash.slice(1) || null;

            // A thin band a third of the way down the screen: whichever sheet crosses it is "being read".
            this.observer = new IntersectionObserver(
                (entries) => {
                    const reading = entries.find((entry) => entry.isIntersecting);

                    if (reading) {
                        this.active = reading.target.id;
                    }
                },
                { rootMargin: '-30% 0px -65% 0px' },
            );

            this.$el.querySelectorAll('[data-car-sheet]').forEach((sheet) => this.observer.observe(sheet));

            // Arriving from a link such as the dashboard's "CAR-002": open at that sheet once the page has settled.
            document.addEventListener(
                'livewire:navigated',
                () => {
                    if (this.active) {
                        document.getElementById(this.active)?.scrollIntoView({ block: 'start' });
                    }
                },
                { once: true },
            );
        },

        destroy() {
            this.observer?.disconnect();
        },

        go(id) {
            this.active = id;

            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            document.getElementById(id)?.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
            window.history.replaceState(window.history.state, '', `#${id}`);
        },

        isActive(id) {
            return this.active === id;
        },
    }));
});
