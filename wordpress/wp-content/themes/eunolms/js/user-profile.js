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
                // Determine status badge class and text
                let statusClass = 'not-started';
                let statusText = 'Sin iniciar';
                
                if (status === 'en-progreso' || (course.progress && course.progress > 0)) {
                    statusClass = 'in-progress';
                    statusText = 'En progreso';
                } else if (status === 'completados' || (course.progress && course.progress >= 100)) {
                    statusClass = 'completed';
                    statusText = 'Completado';
                }

                // Determine card class for completed styling
                let cardClass = '';
                if (statusClass === 'completed') {
                    cardClass = 'completed';
                }

                // Format progress percentage
                let progressPercentage = course.progress || 0;
                if (status === 'completados') {
                    progressPercentage = 100;
                }

                // Determine button based on status
                let buttonText = '';
                let buttonClass = 'course-action-btn';
                let buttonIcon = '';

                if (status === 'sin-iniciar') {
                    buttonText = 'Iniciar';
                    buttonClass += ' btn-start';
                    buttonIcon = '<i class="fas fa-play"></i>';
                } else if (status === 'en-progreso') {
                    buttonText = 'Continuar';
                    buttonClass += ' btn-continue';
                    buttonIcon = '<i class="fas fa-arrow-right"></i>';
                } else if (status === 'completados') {
                    buttonText = 'Ver Contenido';
                    buttonClass += ' btn-view';
                    buttonIcon = '<i class="fas fa-eye"></i>';
                }

                // Generate metadata HTML
                let metadataHtml = '';
                if (course.lessons_count !== undefined) {
                    metadataHtml += `
                        <div class="metadata-item">
                            <i class="fas fa-book"></i>
                            <span>${course.lessons_count} lecciones</span>
                        </div>
                    `;
                }
                if (course.duration) {
                    metadataHtml += `
                        <div class="metadata-item">
                            <i class="fas fa-clock"></i>
                            <span>${course.duration}</span>
                        </div>
                    `;
                }
                if (course.students_count !== undefined) {
                    metadataHtml += `
                        <div class="metadata-item">
                            <i class="fas fa-users"></i>
                            <span>${course.students_count} estudiantes</span>
                        </div>
                    `;
                }

                let cardHtml = `
                    <div class="course-card ${cardClass}">
                        <a href="${course.permalink}">
                            <div class="image-container">
                                <span class="status-badge ${statusClass}">${statusText}</span>
                                <img src="${course.image}" 
                                     alt="${course.title}" 
                                     onerror="this.style.display='none'; this.parentElement.classList.add('no-image');">
                                <div class="image-overlay"></div>
                            </div>
                            <div class="course-content">
                                <h3>${course.title}</h3>
                `;

                // Add metadata if available
                if (metadataHtml) {
                    cardHtml += `
                                <div class="course-metadata">
                                    ${metadataHtml}
                                </div>
                    `;
                }

                // Add progress section
                cardHtml += `
                                <div class="progress-section">
                                    <div class="progress-header">
                                        <span class="progress-label">Progreso</span>
                                        <span class="progress-percentage">${progressPercentage}%</span>
                                    </div>
                                    <div class="course-progress-bar">
                                        <div class="progress" style="width: ${progressPercentage}%;"></div>
                                    </div>
                                </div>
                            </div>
                            <a href="${course.permalink}" class="${buttonClass}">
                                ${buttonIcon}
                                <span>${buttonText}</span>
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