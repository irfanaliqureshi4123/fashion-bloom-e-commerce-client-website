// About Page JavaScript (Optimized to run only if .about-hero exists)

document.addEventListener('DOMContentLoaded', function () {
    // Check if we are on the About page
    if (!document.querySelector('.about-hero')) return;

    // Initialize about page functionality
    initScrollAnimations();
    initCounterAnimations();
    initTeamHovers();
    initParallaxEffects();
    initScrollProgress();
    initLazyLoading();
    initTechFeatureClicks();
});

// Scroll Animations
function initScrollAnimations() {
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('animate-in');
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll(
        '.story-text, .story-image, .mv-card, .offer-card, .value-card, .tech-text, .tech-visual, .team-member, .choose-item'
    ).forEach(el => observer.observe(el));
}

// Counter Animations
function initCounterAnimations() {
    const counters = [
        { element: '.customers-count', target: 10000, suffix: '+' },
        { element: '.products-count', target: 500, suffix: '+' },
        { element: '.reviews-count', target: 5000, suffix: '+' },
        { element: '.satisfaction-count', target: 98, suffix: '%' }
    ];

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = counters.find(c => entry.target.matches(c.element));
                if (counter && !entry.target.classList.contains('counted')) {
                    animateCounter(entry.target, counter.target, counter.suffix);
                    entry.target.classList.add('counted');
                }
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(c => {
        const el = document.querySelector(c.element);
        if (el) observer.observe(el);
    });
}

function animateCounter(element, target, suffix = '') {
    let current = 0;
    const increment = target / 100;
    const stepTime = 2000 / 100;

    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current) + suffix;
    }, stepTime);
}

// Team Member Hover Effects
function initTeamHovers() {
    document.querySelectorAll('.team-member').forEach(member => {
        member.addEventListener('mouseenter', () => {
            member.style.transform = 'translateY(-10px) scale(1.02)';
        });
        member.addEventListener('mouseleave', () => {
            member.style.transform = 'translateY(0) scale(1)';
        });
    });
}

// Parallax Effects
function initParallaxEffects() {
    const parallaxElements = document.querySelectorAll('.about-hero, .technology');
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        parallaxElements.forEach(el => {
            el.style.transform = `translateY(${scrolled * -0.5}px)`;
        });
    });
}

// Scroll Progress Bar
function initScrollProgress() {
    const progressBar = document.createElement('div');
    progressBar.className = 'scroll-progress';
    progressBar.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 0%;
        height: 4px;
        background: linear-gradient(90deg, #d4af37, #f4d03f);
        z-index: 9999;
        transition: width 0.1s ease;
    `;
    document.body.appendChild(progressBar);

    window.addEventListener('scroll', () => {
        const scrollTop = window.pageYOffset;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        progressBar.style.width = `${(scrollTop / docHeight) * 100}%`;
    });
}

// Lazy Loading for Images
function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });
    images.forEach(img => observer.observe(img));
}

// Tech Feature Click Effects
function initTechFeatureClicks() {
    document.querySelectorAll('.tech-feature').forEach(feature => {
        feature.addEventListener('click', function () {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => this.style.transform = 'scale(1)', 150);

            const ripple = document.createElement('div');
            ripple.className = 'ripple';
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    });

    // Add ripple effect styles dynamically
    const style = document.createElement('style');
    style.textContent = `
        .animate-in { animation: slideInUp 0.8s ease forwards; }
        @keyframes slideInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes bounceIn { 0% { opacity: 0; transform: scale(0.3);} 50% { opacity: 1; transform: scale(1.05);} 70% { transform: scale(0.9);} 100% { transform: scale(1);} }
        .ripple { position: absolute; border-radius: 50%; background: rgba(212, 175, 55, 0.3); transform: scale(0); animation: rippleEffect 0.6s linear; pointer-events: none; }
        @keyframes rippleEffect { to { transform: scale(4); opacity: 0; } }
    `;
    document.head.appendChild(style);
}
