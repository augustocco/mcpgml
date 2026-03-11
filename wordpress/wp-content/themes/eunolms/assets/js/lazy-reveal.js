window.initEunoLazyReveal = function() {
    // 1. Identificar automáticamente elementos nativos de WordPress/Gutenberg y añadir clase base
    const selectors = [
        '.wp-block-image',
        '.wp-block-columns',
        '.wp-block-group',
        '.wp-block-cover',
        '.wp-block-media-text',
        '.euno-curriculum-card',
        '.euno-main-box'
    ];

    const elementsToReveal = document.querySelectorAll(selectors.join(', '));
    
    // Si no hay elementos, no cargar el observador
    if (elementsToReveal.length === 0) return;

    elementsToReveal.forEach(el => {
        if (!el.classList.contains('euno-is-revealed') && !el.classList.contains('euno-reveal-item')) {
            el.classList.add('euno-reveal-item');
        }
    });

    // 2. Configurar el Intersection Observer (Alta eficiencia, no usa el evento scroll de window)
    const observerOptions = {
        root: null, // Usa el viewport
        rootMargin: '0px',
        threshold: 0.15 // Dispara la animación cuando el 15% del elemento es visible
    };

    const revealCallback = (entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Agregar clase que activa la animación CSS
                entry.target.classList.add('euno-is-revealed');
                // Dejar de observar el elemento una vez animado (mejor rendimiento)
                observer.unobserve(entry.target);
            }
        });
    };

    const revealObserver = new IntersectionObserver(revealCallback, observerOptions);

    // 3. Empezar a observar
    document.querySelectorAll('.euno-reveal-item:not(.euno-is-revealed)').forEach(el => {
        revealObserver.observe(el);
    });
};

document.addEventListener('DOMContentLoaded', window.initEunoLazyReveal);
