document.addEventListener('DOMContentLoaded', function() {
    const spaRoot = document.getElementById('euno-spa-root');
    if (!spaRoot) return;

    // Helper to execute inline scripts after fetching
    function executeScripts(container) {
        const scripts = container.querySelectorAll('script');
        scripts.forEach(oldScript => {
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            if (oldScript.innerHTML) {
                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
            }
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    }

    // Helper to sync stylesheets (especially for Elementor post-specific CSS)
    function syncStylesheets(newDoc) {
        const newLinks = newDoc.querySelectorAll('link[rel="stylesheet"]');
        const currentLinks = document.head.querySelectorAll('link[rel="stylesheet"]');
        const currentHrefs = Array.from(currentLinks).map(l => l.href);

        newLinks.forEach(link => {
            if (!currentHrefs.includes(link.href)) {
                const newLink = document.createElement('link');
                newLink.rel = 'stylesheet';
                newLink.href = link.href;
                newLink.className = 'euno-dynamic-style';
                document.head.appendChild(newLink);
            }
        });
    }

    // Global SPA Navigation Function
    window.eunoSpaNavigate = function(href) {
        // Add loading state
        spaRoot.style.transition = 'opacity 0.3s ease';
        spaRoot.style.opacity = '0.4';
        spaRoot.style.pointerEvents = 'none';

        let loadingBar = document.getElementById('euno-spa-loader');
        if (!loadingBar) {
            loadingBar = document.createElement('div');
            loadingBar.id = 'euno-spa-loader';
            loadingBar.style.cssText = 'position:fixed;top:0;left:0;height:3px;background:var(--euno-primary);z-index:99999;width:0%;transition:width 0.3s ease;';
            document.body.appendChild(loadingBar);
        }
        setTimeout(() => loadingBar.style.width = '60%', 50);

        fetch(href)
            .then(res => {
                if (!res.ok) throw new Error('Network error');
                return res.text();
            })
            .then(html => {
                loadingBar.style.width = '100%';
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newSpaRoot = doc.getElementById('euno-spa-root');

                if (newSpaRoot) {
                    spaRoot.innerHTML = newSpaRoot.innerHTML;
                    document.title = doc.title;
                    if (window.location.pathname !== new URL(href, window.location.origin).pathname) {
                        window.history.pushState(null, '', href);
                    }
                    document.body.className = doc.body.className;

                    // Update Navigation active states
                    const currentMainNav = document.querySelector('.euno-main-nav');
                    const newMainNav = doc.querySelector('.euno-main-nav');
                    if (currentMainNav && newMainNav) currentMainNav.innerHTML = newMainNav.innerHTML;

                    syncStylesheets(doc);
                    executeScripts(spaRoot);

                    if (typeof window.initEunoLazyReveal === 'function') window.initEunoLazyReveal();
                    if (window.jQuery && typeof elementorFrontend !== 'undefined') {
                        setTimeout(() => {
                            jQuery(spaRoot).find('.elementor-element').each(function() {
                                elementorFrontend.elementsHandler.runReadyTrigger(jQuery(this));
                            });
                        }, 100);
                    }
                    setTimeout(() => window.dispatchEvent(new Event('resize')), 200);
                    window.scrollTo({ top: 0, behavior: 'instant' });
                } else {
                    window.location.href = href;
                }
            })
            .catch(err => {
                console.error('SPA Navigation Error:', err);
                window.location.href = href;
            })
            .finally(() => {
                spaRoot.style.opacity = '1';
                spaRoot.style.pointerEvents = 'auto';
                setTimeout(() => { if (loadingBar) loadingBar.remove(); }, 300);
            });
    };

    document.body.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!link) return;

        // Ignore clicks meant for the LearnDash lesson SPA or explicit exceptions
        if (link.closest('a.euno-curriculum-card') || link.closest('a.ld-button') || link.classList.contains('euno-no-spa')) {
            return;
        }

        const href = link.getAttribute('href');
        if (!href) return;

        // Exclusions
        if (
            link.target === '_blank' ||
            e.ctrlKey || e.metaKey || e.shiftKey || e.altKey ||
            href.startsWith('#') ||
            href.startsWith('javascript') ||
            href.startsWith('mailto:') ||
            href.startsWith('tel:') ||
            href.includes('/wp-admin/') ||
            href.includes('/wp-login.php') ||
            link.hasAttribute('download')
        ) {
            return;
        }

        const url = new URL(href, window.location.origin);
        if (url.origin !== window.location.origin) return;

        // Prevent default browser navigation
        e.preventDefault();

        // Add loading state (fade out slightly)
        spaRoot.style.transition = 'opacity 0.3s ease';
        spaRoot.style.opacity = '0.4';
        spaRoot.style.pointerEvents = 'none';

        // Add a global loading bar at the top of the window
        let loadingBar = document.getElementById('euno-spa-loader');
        if (!loadingBar) {
            loadingBar = document.createElement('div');
            loadingBar.id = 'euno-spa-loader';
            loadingBar.style.cssText = 'position:fixed;top:0;left:0;height:3px;background:var(--euno-primary);z-index:99999;width:0%;transition:width 0.3s ease;';
            document.body.appendChild(loadingBar);
        }
        setTimeout(() => loadingBar.style.width = '60%', 50);

        fetch(href)
            .then(res => {
                if (!res.ok) throw new Error('Network error');
                return res.text();
            })
            .then(html => {
                loadingBar.style.width = '100%';
                
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newSpaRoot = doc.getElementById('euno-spa-root');

                if (newSpaRoot) {
                    // Update content
                    spaRoot.innerHTML = newSpaRoot.innerHTML;
                    
                    // Update metadata
                    document.title = doc.title;
                    window.history.pushState(null, '', href);
                    document.body.className = doc.body.className;

                    // Update Active Menu States by copying the freshly generated menus from WordPress
                    const currentMainNav = document.querySelector('.euno-main-nav');
                    const newMainNav = doc.querySelector('.euno-main-nav');
                    if (currentMainNav && newMainNav) {
                        currentMainNav.innerHTML = newMainNav.innerHTML;
                    }

                    const currentFooterNav = document.querySelector('.euno-footer-nav');
                    const newFooterNav = doc.querySelector('.euno-footer-nav');
                    if (currentFooterNav && newFooterNav) {
                        currentFooterNav.innerHTML = newFooterNav.innerHTML;
                    }

                    // Sync CSS Stylesheets
                    syncStylesheets(doc);

                    // Execute JS
                    executeScripts(spaRoot);

                    // Re-init lazy reveal
                    if (typeof window.initEunoLazyReveal === 'function') {
                        window.initEunoLazyReveal();
                    }

                    // Re-init Elementor if present
                    if (window.jQuery && typeof elementorFrontend !== 'undefined') {
                        setTimeout(() => {
                            jQuery(spaRoot).find('.elementor-element').each(function() {
                                elementorFrontend.elementsHandler.runReadyTrigger(jQuery(this));
                            });
                        }, 100);
                    }

                    // Trigger resize to fix any iframe (YouTube/Vimeo) dimensions
                    setTimeout(() => {
                        window.dispatchEvent(new Event('resize'));
                    }, 200);

                    // Reset Scroll
                    window.scrollTo({ top: 0, behavior: 'instant' });
                } else {
                    window.location.href = href;
                }
            })
            .catch(err => {
                console.error('SPA Navigation Error:', err);
                window.location.href = href; // Fallback
            })
            .finally(() => {
                spaRoot.style.opacity = '1';
                spaRoot.style.pointerEvents = 'auto';
                setTimeout(() => {
                    if (loadingBar) loadingBar.remove();
                }, 300);
            });
    });

    window.addEventListener('popstate', function() {
        window.location.reload();
    });

    // Header Scroll Logic
    const header = document.querySelector('.euno-header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('euno-header-scrolled');
            } else {
                header.classList.remove('euno-header-scrolled');
            }
        });
    }
});
