import './bootstrap';
import Swiper from 'swiper';
import 'swiper/css';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
window.Swiper = Swiper;

Alpine.start();

// The CSS keeps [data-reveal] elements hidden only while `.no-js` is absent and
// JS is present. Drop the `no-js` class the instant this script runs, so a
// failed/blocked bundle leaves content visible rather than blank.
document.documentElement.classList.remove('no-js');

/**
 * Reveal-on-scroll. Elements marked [data-reveal] fade + slide into place the
 * first time they enter the viewport, then are unobserved so it never repeats.
 *
 * Guarded three ways: prefers-reduced-motion short-circuits to "show all",
 * a missing IntersectionObserver (very old browsers) does the same, and the
 * CSS `.no-js` fallback covers the no-JS case above.
 */
function initScrollReveal() {
    const elements = document.querySelectorAll('[data-reveal]');

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion || !('IntersectionObserver' in window)) {
        elements.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries, obs) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    obs.unobserve(entry.target);
                }
            });
        },
        // Trigger a little before the element is fully on screen so the motion
        // reads as it arrives, not after.
        { threshold: 0.1, rootMargin: '0px 0px -8% 0px' }
    );

    elements.forEach((el) => observer.observe(el));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initScrollReveal);
} else {
    initScrollReveal();
}
