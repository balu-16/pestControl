/**
 * MOTION DESIGN SYSTEM - Premium scroll & cursor-reactive effects
 */
(function() {
    'use strict';

    const CONFIG = {
        revealThreshold: 0.15,
        revealRootMargin: '0px 0px -50px 0px',
        parallaxIntensity: 0.15,
        navHideThreshold: 100,
        navScrollDelta: 10,
        cursorGlowEnabled: true,
        magneticStrength: 0.3,
        throttleMs: 16,
        reducedMotion: false
    };

    // Utilities
    const throttle = (func, limit) => {
        let inThrottle;
        return (...args) => {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    };

    const lerp = (start, end, factor) => start + (end - start) * factor;
    const clamp = (value, min, max) => Math.min(Math.max(value, min), max);
    const isTouchDevice = () => 'ontouchstart' in window || navigator.maxTouchPoints > 0;
    const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Scroll Reveal System
    class ScrollReveal {
        constructor() {
            this.observer = null;
            this.init();
        }
        init() {
            if (CONFIG.reducedMotion) return;
            const elements = document.querySelectorAll('[data-motion]');
            if (!elements.length) return;
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('motion-visible');
                    } else if (entry.target.dataset.motionOnce !== 'true') {
                        entry.target.classList.remove('motion-visible');
                    }
                });
            }, { rootMargin: CONFIG.revealRootMargin, threshold: CONFIG.revealThreshold });
            elements.forEach(el => this.observer.observe(el));
        }
    }

    // Parallax System
    class ParallaxSystem {
        constructor() {
            this.elements = document.querySelectorAll('[data-parallax]');
            this.ticking = false;
            if (CONFIG.reducedMotion || !this.elements.length) return;
            window.addEventListener('scroll', () => {
                if (!this.ticking) {
                    requestAnimationFrame(() => { this.update(); this.ticking = false; });
                    this.ticking = true;
                }
            }, { passive: true });
        }
        update() {
            this.elements.forEach(el => {
                const speed = parseFloat(el.dataset.parallax) || CONFIG.parallaxIntensity;
                const rect = el.getBoundingClientRect();
                const offset = (rect.top + rect.height / 2 - window.innerHeight / 2) * speed;
                el.style.transform = `translate3d(0, ${offset}px, 0)`;
            });
        }
    }

    // Scroll Progress
    class ScrollProgress {
        constructor() {
            const container = document.createElement('div');
            container.className = 'scroll-progress';
            container.setAttribute('aria-hidden', 'true');
            this.bar = document.createElement('div');
            this.bar.className = 'scroll-progress-bar';
            container.appendChild(this.bar);
            document.body.appendChild(container);
            window.addEventListener('scroll', () => {
                requestAnimationFrame(() => {
                    const progress = window.scrollY / (document.documentElement.scrollHeight - window.innerHeight);
                    this.bar.style.transform = `scaleX(${progress})`;
                });
            }, { passive: true });
        }
    }

    // Navbar Scroll Behavior
    class NavbarScroll {
        constructor() {
            this.navbar = document.querySelector('.navbar');
            if (!this.navbar) return;
            this.lastY = 0;
            window.addEventListener('scroll', () => {
                const y = window.scrollY;
                const dir = y > this.lastY ? 'down' : 'up';
                if (y > CONFIG.navHideThreshold) {
                    this.navbar.classList.toggle('nav-hidden', dir === 'down');
                } else {
                    this.navbar.classList.remove('nav-hidden');
                }
                this.navbar.classList.toggle('nav-shadow', y > 50);
                this.lastY = y;
            }, { passive: true });
        }
    }

    // Cursor Glow
    class CursorGlow {
        constructor() {
            if (CONFIG.reducedMotion || isTouchDevice()) return;
            this.glow = document.createElement('div');
            this.glow.className = 'cursor-glow';
            this.glow.setAttribute('aria-hidden', 'true');
            document.body.appendChild(this.glow);
            this.x = 0; this.y = 0; this.cx = 0; this.cy = 0;
            document.addEventListener('mousemove', throttle(e => {
                this.x = e.clientX; this.y = e.clientY;
                this.glow.classList.add('active');
            }, CONFIG.throttleMs));
            document.addEventListener('mouseleave', () => this.glow.classList.remove('active'));
            this.animate();
        }
        animate() {
            this.cx = lerp(this.cx, this.x, 0.15);
            this.cy = lerp(this.cy, this.y, 0.15);
            this.glow.style.left = `${this.cx}px`;
            this.glow.style.top = `${this.cy}px`;
            requestAnimationFrame(() => this.animate());
        }
    }

    // Magnetic Elements
    class MagneticElements {
        constructor() {
            if (CONFIG.reducedMotion || isTouchDevice()) return;
            document.querySelectorAll('.btn, .social-icons a').forEach(el => {
                el.addEventListener('mousemove', e => {
                    const rect = el.getBoundingClientRect();
                    const dx = (e.clientX - rect.left - rect.width / 2) * CONFIG.magneticStrength;
                    const dy = (e.clientY - rect.top - rect.height / 2) * CONFIG.magneticStrength;
                    el.style.transform = `translate(${clamp(dx, -15, 15)}px, ${clamp(dy, -15, 15)}px)`;
                });
                el.addEventListener('mouseleave', () => el.style.transform = '');
            });
        }
    }

    // Auto-apply motion attributes
    class MotionAutoApply {
        constructor() {
            if (CONFIG.reducedMotion) return;
            this.apply('.about-text', 'fade-left');
            this.apply('.about-image', 'fade-right');
            this.applyAll('.section-header', 'fade-up');
            this.applyStagger('.service-card', 'fade-up', 3);
            this.applyStagger('.step-card', 'fade-up', 4);
            this.applyStagger('.testimonial-card', 'scale-up', 3);
            this.applyStagger('.about-features li', 'fade-left', 4);
            this.apply('.cta-container', 'fade-up');
            this.applyStagger('.footer-grid > div', 'fade-up', 4);
        }
        apply(sel, motion) {
            const el = document.querySelector(sel);
            if (el && !el.hasAttribute('data-motion')) {
                el.setAttribute('data-motion', motion);
                el.setAttribute('data-motion-once', 'true');
            }
        }
        applyAll(sel, motion) {
            document.querySelectorAll(sel).forEach(el => {
                if (!el.hasAttribute('data-motion')) {
                    el.setAttribute('data-motion', motion);
                    el.setAttribute('data-motion-once', 'true');
                }
            });
        }
        applyStagger(sel, motion, mod) {
            document.querySelectorAll(sel).forEach((el, i) => {
                if (!el.hasAttribute('data-motion')) {
                    el.setAttribute('data-motion', motion);
                    el.setAttribute('data-motion-delay', String((i % mod) + 1));
                    el.setAttribute('data-motion-once', 'true');
                }
            });
        }
    }

    // Hover Enhancer
    class HoverEnhancer {
        constructor() {
            if (CONFIG.reducedMotion) return;
            document.querySelectorAll('.service-card, .testimonial-card, .step-card').forEach(c => c.classList.add('motion-hover-lift'));
            document.querySelectorAll('.social-icons a i').forEach(i => i.classList.add('motion-icon-scale'));
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        CONFIG.reducedMotion = prefersReducedMotion();
        new MotionAutoApply();
        new ScrollReveal();
        new ParallaxSystem();
        new ScrollProgress();
        new NavbarScroll();
        new CursorGlow();
        new MagneticElements();
        new HoverEnhancer();
        console.log('Motion system initialized', CONFIG.reducedMotion ? '(reduced motion)' : '');
    });
})();
