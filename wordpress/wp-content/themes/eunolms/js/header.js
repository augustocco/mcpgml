(function() {
    'use strict';
    
    const header = document.querySelector('.euno-header');
    
    if (!header) return;
    
    let lastScroll = 0;
    let scrollTimer;
    
    function handleScroll() {
        const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
        
        if (currentScroll > 50) {
            header.classList.add('euno-header-scrolled');
        } else {
            header.classList.remove('euno-header-scrolled');
        }
        
        lastScroll = currentScroll;
    }
    
    function throttledHandleScroll() {
        if (scrollTimer) return;
        scrollTimer = setTimeout(function() {
            handleScroll();
            scrollTimer = null;
        }, 10);
    }
    
    window.addEventListener('scroll', throttledHandleScroll, { passive: true });
    
    handleScroll();
})();