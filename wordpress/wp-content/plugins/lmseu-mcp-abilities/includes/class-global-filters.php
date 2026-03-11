<?php
/**
 * Filtros globales para el sistema EUNO.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LMSEU_Global_Filters {
    public static function init() {
        add_filter( 'post_thumbnail_html', array( __CLASS__, 'fallback_thumbnail_html' ), 10, 5 );
        
        // Inyectar estilos con prioridad máxima
        add_action( 'wp_head', array( __CLASS__, 'inject_global_styles' ), 999 );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'fix_elementor_scripts' ), 999 );

        // Cargar recursos necesarios
        add_action( 'wp_enqueue_scripts', function() {
            wp_enqueue_style( 'font-awesome-global', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' );
            wp_enqueue_style( 'plus-jakarta-sans', 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap' );
        });

        add_filter( 'wp_nav_menu_items', array( __CLASS__, 'add_admin_menu_to_nav' ), 99, 2 );
        add_action( 'template_redirect', array( __CLASS__, 'redirect_course_to_first_lesson' ) );
        
        // Hooks de Focus Mode
        add_action( 'learndash-focus-sidebar-heading-after', array( __CLASS__, 'inject_sidebar_progress' ), 10, 2 );
        
        // IntegraciÃ³n de Header y Footer sin romper etiquetas HTML
        add_action( 'learndash-focus-template-start', array( __CLASS__, 'show_site_header' ), 1 );
        add_action( 'learndash-focus-template-end', array( __CLASS__, 'show_site_footer' ), 99 );

        // Tracking de tiempo efectivo
        add_action( 'wp_footer', array( __CLASS__, 'inject_time_tracker_script' ) );
        }

        public static function inject_time_tracker_script() {
        if ( ! is_user_logged_in() ) return;

        $post_type = get_post_type();
        $is_learndash = in_array( $post_type, array( 'sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz' ) );

        if ( ! $is_learndash ) return;

        $user_id   = get_current_user_id();
        $course_id = learndash_get_course_id();
        $step_id   = get_the_ID();

        if ( ! $course_id ) return;

        ?>
        <script>
        (function() {
            if (window.EUNO_TRACKER) {
                window.EUNO_TRACKER.updateStep(<?php echo $step_id; ?>);
                return;
            }

            window.EUNO_TRACKER = {
                user_id: <?php echo $user_id; ?>,
                course_id: <?php echo $course_id; ?>,
                step_id: <?php echo $step_id; ?>,
                nonce: '<?php echo wp_create_nonce( "wp_rest" ); ?>',
                lastInteraction: Date.now(),
                isTabActive: true,
                storageKey: `euno_tracker_pending_${<?php echo $user_id; ?>}_${<?php echo $course_id; ?>}`,
                
                init: function() {
                    console.log('[EUNO Tracker] Global Init - Step:', this.step_id);
                    
                    document.addEventListener('visibilitychange', () => {
                        this.isTabActive = !document.hidden;
                        if (!this.isTabActive) this.sendPings();
                    });

                    const resetIdle = () => { this.lastInteraction = Date.now(); };
                    ['mousemove', 'keydown', 'scroll', 'click', 'touchstart'].forEach(ev => {
                        window.addEventListener(ev, resetIdle, { passive: true });
                    });

                    // Recuperar tiempo pendiente del almacenamiento local
                    const pending = localStorage.getItem(this.storageKey);
                    if (pending) {
                        console.log('[EUNO Tracker] Recuperando tiempo pendiente:', pending);
                        this.sendPings(parseInt(pending));
                    }

                    setInterval(() => {
                        if (this.isTabActive && (Date.now() - this.lastInteraction < 60000)) {
                            // Incrementar tiempo activo tanto en memoria como en storage
                            let current = parseInt(localStorage.getItem(this.storageKey) || 0);
                            localStorage.setItem(this.storageKey, current + 1);
                        }
                    }, 1000);

                    setInterval(() => this.sendPings(), 30000);
                    window.addEventListener('beforeunload', () => this.sendPings());
                },

                updateStep: function(newStepId) {
                    if (this.step_id === newStepId) return;
                    this.sendPings();
                    this.step_id = newStepId;
                    console.log('[EUNO Tracker] Step Updated:', newStepId);
                },

                getFullCourseTime: function(serverBase = 0) {
                    // Retorna la base del servidor + lo que hayamos acumulado localmente en esta sesión
                    const localSeconds = parseInt(localStorage.getItem(this.storageKey) || 0);
                    return serverBase + localSeconds;
                },

                sendPings: async function(forcedSeconds = null) {
                    const pending = forcedSeconds !== null ? forcedSeconds : parseInt(localStorage.getItem(this.storageKey) || 0);
                    if (pending <= 0) return;

                    console.log(`[EUNO Tracker] Sincronizando ${pending}s para paso ${this.step_id}`);
                    
                    try {
                        const response = await fetch('/wp-json/wp-abilities/v1/abilities/learndash/track-time/run', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': this.nonce },
                            body: JSON.stringify({
                                input: {
                                    user_id: this.user_id,
                                    course_id: this.course_id,
                                    step_id: this.step_id,
                                    seconds: pending
                                }
                            })
                        });
                        
                        const text = await response.text();
                        let resData;
                        try { resData = JSON.parse(text); } catch(e) { 
                            console.error('[EUNO Tracker] Servidor no devolvió JSON:', text);
                            throw new Error('Invalid server response');
                        }

                        if (response.ok && (resData.success || resData.value)) {
                            const current = parseInt(localStorage.getItem(this.storageKey) || 0);
                            localStorage.setItem(this.storageKey, Math.max(0, current - pending));
                            console.log('[EUNO Tracker] Sync OK');
                        } else {
                            throw new Error(resData.message || 'Server error');
                        }
                    } catch (e) {
                        console.warn('[EUNO Tracker] Sync diferido:', e.message);
                    }
                },
                
                getActiveTime: function() {
                    return parseInt(localStorage.getItem(this.storageKey) || 0);
                }
            };
            window.EUNO_TRACKER.init();
        })();
        </script>
<?php
        }

        public static function show_site_header() {

        if ( function_exists( 'elementor_theme_do_location' ) ) {
            echo '<div class="euno-injected-header">';
            elementor_theme_do_location( 'header' );
            echo '</div>';
        }
    }

    public static function show_site_footer() {
        if ( function_exists( 'elementor_theme_do_location' ) ) {
            echo '<div class="euno-injected-footer">';
            elementor_theme_do_location( 'footer' );
            echo '</div>';
        }
    }

    public static function inject_sidebar_progress( $course_id, $user_id ) {
        if ( ! is_user_logged_in() ) return;
        $progress = learndash_course_progress( array( 'course_id' => $course_id, 'user_id' => $user_id, 'array' => true ) );
        if ( $progress ) {
            $percentage = (int)$progress['percentage'];
            echo '<div class="euno-sidebar-progress">';
            echo '  <div class="euno-progress-text"><span>Tu avance</span><strong>' . $percentage . '%</strong></div>';
            echo '  <div class="euno-progress-track"><div style="width:' . $percentage . '%"></div></div>';
            echo '</div>';
        }
    }

    public static function redirect_course_to_first_lesson() {
        if ( is_singular( 'sfwd-courses' ) && is_user_logged_in() ) {
            $course_id = get_the_ID();
            $user_id = get_current_user_id();
            if ( sfwd_lms_has_access( $course_id, $user_id ) ) {
                $redirect_url = '';
                global $wpdb;
                $last_step_id = (int) $wpdb->get_var($wpdb->prepare("SELECT activity_meta_value FROM {$wpdb->prefix}learndash_user_activity as user_activity INNER JOIN {$wpdb->prefix}learndash_user_activity_meta as user_activity_meta ON user_activity.activity_id = user_activity_meta.activity_id WHERE user_id=%d AND post_id=%d AND activity_type='course' AND activity_meta_key= 'steps_last_id' ORDER BY activity_updated DESC LIMIT 1", $user_id, $course_id));
                if ( empty( $last_step_id ) && function_exists('learndash_user_progress_get_first_incomplete_step') ) {
                    $last_step_id = learndash_user_progress_get_first_incomplete_step( $user_id, $course_id );
                }
                if ( ! empty( $last_step_id ) ) {
                    $redirect_url = learndash_get_step_permalink( $last_step_id, $course_id );
                }
                if ( empty( $redirect_url ) ) {
                    $lessons = learndash_get_course_lessons_list( $course_id );
                    if ( ! empty( $lessons ) ) {
                        $first_lesson_item = reset( $lessons );
                        if ( isset( $first_lesson_item['post'] ) ) {
                            $redirect_url = learndash_get_step_permalink( $first_lesson_item['post']->ID, $course_id );
                        }
                    }
                }
                if ( ! empty( $redirect_url ) && $redirect_url !== get_permalink( $course_id ) ) {
                    wp_safe_redirect( $redirect_url );
                    exit;
                }
            }
        }
    }

    public static function fix_elementor_scripts() {
        if ( is_singular( array( 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz' ) ) ) {
            echo "<script>if (typeof elementorFrontendConfig === 'undefined') { var elementorFrontendConfig = { environmentMode: { edit: false }, version: '3.35.5', urls: { assets: '" . site_url('/wp-content/plugins/elementor/assets/') . "' }, kit: { active_breakpoints: ['viewport_mobile', 'viewport_tablet'] } }; }</script>";
        }
    }

    public static function add_admin_menu_to_nav( $items, $args ) {
        if ( ! is_user_logged_in() ) return $items;

        // Asegurarnos de que esto se agregue solo al menú principal para no romper footers
        if ( isset($args->theme_location) && $args->theme_location !== 'primary' ) {
            return $items;
        }

        $user = wp_get_current_user();
        if ( array_intersect( array( 'administrator', 'group_leader' ), (array) $user->roles ) ) {
            $items .= '<li class="menu-item menu-item-has-children admin-nav-item">
                <a href="#">Administración</a>
                <ul class="sub-menu">
                    <li><a href="' . home_url( '/informes/' ) . '">Informes</a></li>
                    <li><a href="' . admin_url() . '">WP Admin</a></li>
                </ul>
            </li>';
        }
        return $items;
    }

    public static function inject_global_styles() {
        $is_editor = false;
        if ( isset( $_GET['elementor-preview'] ) || ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) ) $is_editor = true;
        ?>
        <style id="euno-focus-redesign">
            /* --- RESET BASE (SOLO PARA LECCIONES) --- */
            .ld-focus { font-family: 'Plus Jakarta Sans', sans-serif !important; background-color: #f1f5f9 !important; }

            <?php if ( ! $is_editor ) : ?>
            /* --- LAYOUT ESTRUCTURAL --- */
            .learndash-wrapper .ld-focus {
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: wrap !important;
                width: 100% !important;
                height: auto !important;
                min-height: 100vh !important;
                position: relative !important;
                overflow: visible !important;
                background: #f8fafc !important;
            }

            /* Header y Footer: Ocupan el 100% ancho */
            .euno-injected-header { flex: 0 0 100% !important; width: 100% !important; order: 1 !important; z-index: 10002 !important; background: #ffffff !important; border-bottom: 1px solid rgba(0,0,0,0.05); }
            .euno-injected-footer { flex: 0 0 100% !important; width: 100% !important; order: 4 !important; background: #ffffff !important; }

            /* Sidebar: Columna Izquierda */
            .ld-focus .ld-focus-sidebar {
                flex: 0 0 360px !important;
                width: 360px !important;
                background-color: #0f172a !important; 
                order: 2 !important;
                position: relative !important;
                height: auto !important;
                min-height: 600px !important;
                z-index: 10 !important;
                border: none !important;
            }

            /* Main Content: La zona de estudio */
            .ld-focus .ld-focus-main { 
                flex: 1 1 0 !important;
                order: 3 !important;
                padding: 60px 40px !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                background-color: transparent !important;
                overflow: visible !important;
            }

            /* --- COMPONENTES SIDEBAR --- */
            .ld-focus .ld-course-navigation-heading { padding: 40px 30px 20px !important; border-bottom: 1px solid #1e293b !important; }
            .ld-focus #ld-focus-mode-course-heading { font-size: 18px !important; font-weight: 800 !important; color: #ffffff !important; text-transform: uppercase !important; }

            .euno-sidebar-progress { padding: 25px 30px !important; border-bottom: 1px solid #1e293b; }
            .euno-progress-text { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 10px; }
            .euno-progress-text span { color: #94a3b8; font-size: 11px; font-weight: 700; text-transform: uppercase; }
            .euno-progress-text strong { color: #3b82f6; font-size: 16px; font-weight: 800; }
            .euno-sidebar-progress .euno-progress-track { background: #1e293b; height: 6px; border-radius: 3px; }
            .euno-sidebar-progress .euno-progress-track div { background: #3b82f6; height: 100%; border-radius: 3px; box-shadow: 0 0 10px rgba(59, 130, 246, 0.4); }

            /* Curriculum List */
            .ld-focus .ld-lesson-item-section-heading { background-color: #1e293b !important; padding: 15px 30px !important; color: #ffffff !important; font-size: 12px !important; font-weight: 800 !important; border-bottom: 1px solid #0f172a; }
            .ld-focus .ld-lesson-item-preview-heading { padding: 18px 30px !important; transition: all 0.2s; border-left: 4px solid transparent !important; text-decoration: none !important; }
            .ld-focus .ld-lesson-item-preview-heading:hover { background: rgba(255,255,255,0.03) !important; }
            .ld-focus .ld-is-current-lesson .ld-lesson-item-preview-heading { background: #1e293b !important; border-left-color: #3b82f6 !important; }
            
            .ld-focus .ld-lesson-title { color: #cbd5e1 !important; font-size: 14px !important; font-weight: 600 !important; display: flex; align-items: center; gap: 12px; }
            .ld-focus .ld-lesson-title::before { content: '\f144'; font-family: 'Font Awesome 6 Free'; font-weight: 900; color: #475569; }
            .ld-focus .ld-is-current-lesson .ld-lesson-title { color: #ffffff !important; }
            .ld-focus .ld-is-current-lesson .ld-lesson-title::before { color: #3b82f6 !important; }

            /* --- ÁREA DE ESTUDIO (TARJETA MODERNA) --- */
            .ld-focus .ld-focus-content {
                width: 100% !important;
                max-width: 1000px !important;
                background: #ffffff !important;
                padding: 60px 80px !important;
                border-radius: 24px;
                box-shadow: 0 30px 60px rgba(15,23,42,0.04);
                border: 1px solid #f1f5f9;
                margin-bottom: 40px;
            }
            .ld-focus .ld-focus-content h1 { font-size: 40px !important; font-weight: 900 !important; color: #0f172a !important; letter-spacing: -2px; margin-bottom: 40px !important; line-height: 1.1 !important; }

            /* Botones de Navegación */
            .ld-focus .ld-content-actions { width: 100%; max-width: 1000px; padding: 40px 0 !important; display: flex !important; justify-content: space-between !important; align-items: center !important; }
            .ld-focus .ld-content-actions .ld-button, .ld-focus .ld-content-actions .ld-content-action-prev, .ld-focus .ld-content-actions .ld-content-action-next {
                background: #ffffff !important; color: #475569 !important; font-weight: 700 !important; font-size: 14px !important; padding: 14px 28px !important; border-radius: 12px !important; border: 1px solid #e2e8f0 !important; text-decoration: none !important; transition: all 0.2s;
            }
            .ld-focus .ld-content-actions .ld-course-check-btn { background: #3b82f6 !important; color: #ffffff !important; border: none !important; font-weight: 800 !important; padding: 18px 40px !important; border-radius: 14px !important; box-shadow: 0 15px 30px rgba(59,130,246,0.3) !important; }

            /* Limpieza Total */
            .ld-focus-header, .ld-masthead, .ld-focus-sidebar-trigger, .ld-breadcrumbs, .ld-status-bubble { display: none !important; }
            <?php endif; ?>
        </style>
        <?php
    }

    public static function fallback_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
        if ( empty( $html ) && 'sfwd-courses' === get_post_type( $post_id ) ) {
            $default_image_url = 'https://eks10.lmseunoconsulting.com/wp-content/uploads/2026/03/EnConstruccion.webp';
            return '<img src="' . esc_url($default_image_url) . '" alt="En Construcción" />';
        }
        return $html;
    }
}
LMSEU_Global_Filters::init();