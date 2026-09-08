/* SSD Prayas — front-end behaviour */
(function () {
    'use strict';

    /* Mobile menu */
    var toggle = document.getElementById('navToggle');
    var menu   = document.getElementById('navMenu');

    if (toggle && menu) {
        toggle.addEventListener('click', function () {
            var open = menu.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.innerHTML = open ? '<i class="fas fa-xmark"></i>' : '<i class="fas fa-bars"></i>';
        });

        menu.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                menu.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.innerHTML = '<i class="fas fa-bars"></i>';
            });
        });
    }

    /* Header shadow once scrolled */
    var header = document.getElementById('siteHeader');
    if (header) {
        var onScroll = function () {
            header.classList.toggle('is-stuck', window.scrollY > 8);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    /* Reveal on scroll */
    var revealables = document.querySelectorAll('.reveal');
    var showAll = function () {
        revealables.forEach(function (el) { el.classList.add('is-visible'); });
    };

    if (revealables.length) {
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });

            revealables.forEach(function (el) {
                // Anything already on screen (e.g. landing on an #anchor) shows straight away.
                var box = el.getBoundingClientRect();
                if (box.top < window.innerHeight && box.bottom > 0) {
                    el.classList.add('is-visible');
                } else {
                    observer.observe(el);
                }
            });

            // Failsafe: never leave content hidden if the observer misbehaves.
            setTimeout(showAll, 2500);
        } else {
            showAll();
        }
    }

    /* Count-up for stat numbers */
    var counters = document.querySelectorAll('[data-count]');
    if (counters.length && 'IntersectionObserver' in window) {
        var countObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                var el     = entry.target;
                var target = parseInt(el.dataset.count, 10) || 0;
                var suffix = el.dataset.suffix || '';
                var start  = performance.now();
                var run = function (now) {
                    var progress = Math.min((now - start) / 1400, 1);
                    var eased    = 1 - Math.pow(1 - progress, 3);
                    el.textContent = Math.round(target * eased).toLocaleString('en-IN') + suffix;
                    if (progress < 1) requestAnimationFrame(run);
                };
                requestAnimationFrame(run);
                countObserver.unobserve(el);
            });
        }, { threshold: 0.5 });
        counters.forEach(function (el) { countObserver.observe(el); });
    }

    /* Auto-dismiss alerts */
    document.querySelectorAll('.alert[data-autohide]').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity .4s ease';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 400);
        }, 6000);
    });

    /* Testimonial dots interactive highlight */
    var tbDots = document.querySelectorAll('.tb-dot');
    var tbCards = document.querySelectorAll('.tb-review-card');
    if (tbDots.length && tbCards.length) {
        tbDots.forEach(function (dot, idx) {
            dot.addEventListener('click', function () {
                tbDots.forEach(function (d) { d.classList.remove('is-active'); });
                dot.classList.add('is-active');
                tbCards.forEach(function (card, cIdx) {
                    if (cIdx === idx) {
                        card.style.transform = 'translateY(-6px)';
                        card.style.background = 'rgba(255, 255, 255, 0.18)';
                        card.style.borderColor = 'rgba(255, 255, 255, 0.45)';
                    } else {
                        card.style.transform = '';
                        card.style.background = '';
                        card.style.borderColor = '';
                    }
                });
            });
        });
    }

    /* Continuous Programmes Slider: Pause on Touch for Mobile Devices */
    var progTrack = document.querySelector('.prog-slider-track');
    if (progTrack) {
        progTrack.addEventListener('touchstart', function () {
            progTrack.style.animationPlayState = 'paused';
        }, { passive: true });
        progTrack.addEventListener('touchend', function () {
            progTrack.style.animationPlayState = 'running';
        }, { passive: true });
    }

    /* Continuous Home Tracks Slider: Pause on Touch for Mobile Devices */
    var homeTracksTrack = document.querySelector('.tracks-slider-track');
    if (homeTracksTrack) {
        homeTracksTrack.addEventListener('touchstart', function () {
            homeTracksTrack.style.animationPlayState = 'paused';
        }, { passive: true });
        homeTracksTrack.addEventListener('touchend', function () {
            homeTracksTrack.style.animationPlayState = 'running';
        }, { passive: true });
    }

    /* Continuous School Programme Marquees: Pause on Touch for Mobile Devices */
    document.querySelectorAll('.school-marquee-track').forEach(function (track) {
        track.addEventListener('touchstart', function () {
            track.style.animationPlayState = 'paused';
        }, { passive: true });
        track.addEventListener('touchend', function () {
            track.style.animationPlayState = 'running';
        }, { passive: true });
    });

    /* Interactive 3D Flip Cards (MVV & Google Certificates) */
    var flipCards = document.querySelectorAll('.mvv-flip-card, .cert-flip-card');
    if (flipCards.length) {
        flipCards.forEach(function (card) {
            card.addEventListener('click', function (e) {
                if (e.target.closest('a')) {
                    return;
                }
                card.classList.toggle('is-flipped');
            });
            card.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    if (e.target.closest('a')) {
                        return;
                    }
                    e.preventDefault();
                    card.classList.toggle('is-flipped');
                }
            });
        });
    }

    /* Continuous Four Pillars Slider: Pause on Touch for Mobile Devices */
    var pillarsTrack = document.querySelector('.pillars-slider-track');
    if (pillarsTrack) {
        pillarsTrack.addEventListener('touchstart', function () {
            pillarsTrack.style.animationPlayState = 'paused';
        }, { passive: true });
        pillarsTrack.addEventListener('touchend', function () {
            pillarsTrack.style.animationPlayState = 'running';
        }, { passive: true });
    }

    /* Continuous Career Snapshot Ticker: Pause on Touch for Mobile Devices */
    var careerTrack = document.querySelector('.career-ticker-track');
    if (careerTrack) {
        careerTrack.addEventListener('touchstart', function () {
            careerTrack.style.animationPlayState = 'paused';
        }, { passive: true });
        careerTrack.addEventListener('touchend', function () {
            careerTrack.style.animationPlayState = 'running';
        }, { passive: true });
    }
})();
