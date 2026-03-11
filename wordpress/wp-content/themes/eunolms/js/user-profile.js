/**
 * User Profile Tab Handler
 * Handles dynamic loading of courses by status
 */
(function($) {
    'use strict';

    const UserProfileTabs = {
        init: function() {
            this.bindEvents();
            this.loadInitialTab();
        },

        bindEvents: function() {
            $('.tabs-nav li a').on('click', this.handleTabClick.bind(this));
        },

        handleTabClick: function(e) {
            e.preventDefault();
            
            const $target = $(e.currentTarget);
            const $li = $target.closest('li');
            const tabId = $target.attr('href').substring(1);
            
            // Don't reload if clicking active tab
            if ($li.hasClass('tab-active')) {
                return;
            }

            // Update active state
            $('.tabs-nav li').removeClass('tab-active');
            $li.addClass('tab-active');

            // Hide all tab panes and show selected one
            $('.tab-pane').removeClass('active');
            const $tabPane = $('#' + tabId);
            $tabPane.addClass('active');

            // Load courses for the selected tab
            this.loadCourses(tabId);
        },

        loadInitialTab: function() {
            // Load the first tab (en-progreso) on page load
            this.loadCourses('en-progreso');
        },

        loadCourses: function(status) {
            const $tabPane = $('#' + status);
            
            // Show loading spinner
            this.showLoading($tabPane);

            // Make AJAX request
            $.ajax({
                url: wpApiSettings.root + 'eunolms/v1/courses/' + status,
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
                },
                success: function(response) {
                    if (response.success && response.data) {
                        UserProfileTabs.renderCourses($tabPane, response.data.courses, response.data.status);
                    } else {
                        UserProfileTabs.showError($tabPane, response.message || 'Error al cargar cursos');
                    }
                },
                error: function(xhr) {
                    UserProfileTabs.showError($tabPane, 'Error de conexión. Por favor intenta nuevamente.');
                }
            });
        },

        showLoading: function($container) {
            const loadingHtml = `
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <span>Cargando cursos...</span>
                </div>
            `;
            $container.html(loadingHtml);
        },

        showError: function($container, message) {
            $container.html(`
                <div class="error-message" style="color: #e53e3e; padding: 40px;">
                    <i class="fas fa-exclamation-circle" style="font-size: 40px; margin-bottom: 15px; display: block;"></i>
                    ${message}
                </div>
            `);
        },

        renderCourses: function($container, courses, status) {
            if (!courses || courses.length === 0) {
                this.renderEmptyState($container, status);
                return;
            }

            let gridHtml = '<div class="course-grid">';
            
            courses.forEach(function(course) {
                let cardHtml = `
                    <div class="course-card">
                        <a href="${course.permalink}">
                            <img src="${course.image || 'https://via.placeholder.com/400x300?text=Curso'}" 
                                 alt="${course.title}" 
                                 onerror="this.src='https://via.placeholder.com/400x300?text=Curso'">
                            <h3>${course.title}</h3>
                `;

                // Add progress bar for in-progress courses
                if (status === 'en-progreso' && course.progress !== undefined) {
                    cardHtml += `
                            <div class="course-progress-bar">
                                <div class="progress" style="width: ${course.progress}%;"></div>
                            </div>
                    `;
                }

                cardHtml += `
                        </a>
                    </div>
                `;
                
                gridHtml += cardHtml;
            });
            
            gridHtml += '</div>';
            $container.html(gridHtml);
        },

        renderEmptyState: function($container, status) {
            const messages = {
                'en-progreso': 'No tienes cursos en progreso actualmente.',
                'sin-iniciar': 'No tienes cursos sin iniciar.',
                'completados': 'No tienes cursos completados.'
            };

            $container.html(`
                <div class="empty-state" style="padding: 40px;">
                    <i class="fas fa-book-open" style="font-size: 40px; color: #b8c2cc; margin-bottom: 15px; display: block;"></i>
                    <p>${messages[status] || 'No hay cursos disponibles.'}</p>
                </div>
            `);
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        UserProfileTabs.init();
    });

})(jQuery);