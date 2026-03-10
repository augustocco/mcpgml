<?php
/**
 * Genera el shortcode del perfil de estudiante público (Fidelidad Máxima MasterStudy - Refinado)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LMSEU_Student_Profile {
    public static function init() {
        add_action( 'init', function() {
            add_shortcode( 'euno_student_profile', array( 'LMSEU_Student_Profile', 'render_profile' ) );
        });
    }

    public static function render_profile( $atts ) {
        if ( ! is_user_logged_in() && empty( $_GET['user_id'] ) ) {
            return '<div style="padding: 40px; text-align: center; font-family: sans-serif;">Debes iniciar sesión para ver tu perfil.</div>';
        }

        $user_id = is_user_logged_in() ? get_current_user_id() : intval( $_GET['user_id'] );
        if ( current_user_can( 'manage_options' ) && ! empty( $_GET['user_id'] ) ) {
            $user_id = intval( $_GET['user_id'] );
        }

        $user = get_userdata( $user_id );
        if ( ! $user ) return '<div style="padding: 40px; text-align: center; font-family: sans-serif;">Usuario no encontrado.</div>';

        // --- FETCH AVATAR ---
        $custom_avatar_id = get_user_meta( $user_id, '_euno_custom_avatar', true );
        $avatar = $custom_avatar_id ? wp_get_attachment_url( $custom_avatar_id ) : get_avatar_url( $user_id, array( 'size' => 300 ) );

        $first_name = get_user_meta( $user_id, 'first_name', true );
        $last_name = get_user_meta( $user_id, 'last_name', true );
        $name = trim( $first_name . ' ' . $last_name );
        if ( empty( $name ) ) {
            $name = $user->display_name;
        }

        $registered_date = strtotime( $user->user_registered );
        $registered_formatted = date_i18n( 'F Y', $registered_date );

        // --- FETCH LEARNDASH DATA ---
        $enrolled_course_ids = function_exists('learndash_user_get_enrolled_courses') ? learndash_user_get_enrolled_courses( $user_id ) : [];
        
        $completed_courses = [];
        $in_progress_courses = [];
        $not_started_courses = [];
        
        foreach ( $enrolled_course_ids as $course_id ) {
            $status_raw = function_exists('learndash_course_status') ? learndash_course_status( $course_id, $user_id ) : 'not started';
            $course_post = get_post( $course_id );
            if ( ! $course_post ) continue;
            
            $default_image_id = get_option( 'euno_default_course_image_id' );
            $default_image_url = 'https://eks10.lmseunoconsulting.com/wp-content/uploads/2026/03/EnConstruccion.webp';

            if (!$default_image_url) {
                $default_image_url = 'https://eks10.lmseunoconsulting.com/wp-content/uploads/2026/03/EnConstruccion.webp';
            }

            $cert_link = '';
            if ( function_exists('learndash_get_course_certificate_link') ) {
                $cert_link = learndash_get_course_certificate_link( $course_id, $user_id );
            }

            $status_lower = strtolower( $status_raw );
            $is_completed = in_array($status_lower, ['completed', 'completado', 'terminado', 'finalizado', 'yes']);

            $course_data = [
                'id' => $course_id,
                'title' => $course_post->post_title,
                'url' => get_permalink( $course_id ),
                'image' => get_the_post_thumbnail_url( $course_id, 'medium' ) ?: $default_image_url,
                'steps_completed' => function_exists('learndash_course_get_completed_steps') ? learndash_course_get_completed_steps( $user_id, $course_id ) : 0,
                'steps_total' => function_exists('learndash_get_course_steps_count') ? learndash_get_course_steps_count( $course_id ) : 0,
                'certificate' => $cert_link,
                'is_completed' => $is_completed,
            ];

            if ( $is_completed ) {
                $completed_courses[] = $course_data;
            } elseif ( $course_data['steps_completed'] > 0 ) {
                $in_progress_courses[] = $course_data;
            } else {
                $not_started_courses[] = $course_data;
            }
        }

        // Stats
        $total_enrolled = count($enrolled_course_ids);
        $total_completed = count($completed_courses);
        
        $group_count = 0;
        if ( function_exists( 'learndash_get_users_group_ids' ) ) {
            $group_count = count( learndash_get_users_group_ids( $user_id ) );
        }

        $quizzes_done = 0;
        $user_quizzes = get_user_meta( $user_id, '_sfwd-quizzes', true );
        if ( is_array( $user_quizzes ) ) {
            $quizzes_done = count( array_unique( array_column( $user_quizzes, 'quiz' ) ) );
        }

        $assignments_count = 0;
        $assignments_query = new WP_Query(array(
            'post_type' => 'sfwd-assignment',
            'author' => $user_id,
            'posts_per_page' => -1
        ));
        $assignments_count = $assignments_query->found_posts;

        $user_points = function_exists('learndash_get_user_course_points') ? learndash_get_user_course_points( $user_id ) : 0;

        ob_start();
        ?>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
            .ms-profile { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fff; position: relative; }
            .ms-header-card { border: 1px solid #e2e8f0; border-radius: 5px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
            .ms-stat-item { border: 1px solid #f1f5f9; border-radius: 5px; }

            .ms-tabs-container { display: flex; flex-wrap: wrap; justify-content: center; gap: 4px; background: #f1f5f9; padding: 4px; border-radius: 5px; margin-bottom: 2rem; }
            .ms-tab-btn { 
                flex: 1 1 auto; 
                min-width: 140px;
                padding: 12px 16px; 
                font-size: 12px; 
                font-weight: 800; 
                text-transform: uppercase; 
                letter-spacing: 0.5px; 
                color: #64748b; 
                border-radius: 5px; 
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                border: none;
                cursor: pointer;
            }
            .ms-tab-btn:hover { color: #3b82f6; background: rgba(255,255,255,0.5); }
            .ms-tab-btn.active { 
                background: #fff; 
                color: #3b82f6;
                box-shadow: 0 4px 12px rgba(0,0,0,0.05);
                border-radius: 5px;
            }

            .ms-tab-content { display: none; }
            .ms-tab-content.active { display: block; animation: slideUp 0.4s ease-out; }
            @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

            .course-card { border-radius: 5px; border: 1px solid #f1f5f9; overflow: hidden; transition: all 0.3s ease; }
            .course-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); border-color: #3b82f6; }

            .btn-action {
                background: #3b82f6;
                color: #fff;
                font-weight: 800;
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: 1px;
                padding: 12px 24px;
                border-radius: 5px;
                transition: all 0.3s;
            }
            .btn-action:hover { 
                background: #2563eb !important; 
                color: #fff !important;
                transform: scale(1.02); 
            }

            .btn-secondary {
                display: block;
                width: 100%;
                text-align: center;
                font-size: 11px;
                font-weight: 700;
                color: #94a3b8; /* slate-400 */
                text-transform: uppercase;
                letter-spacing: 1px;
                padding: 10px 20px;
                border: 1px solid #f1f5f9;
                border-radius: 5px;
                transition: all 0.3s ease;
            }
            .btn-secondary:hover {
                background: #f8fafc;
                border-color: #e2e8f0;
                color: #3b82f6;
            }

            /* TOAST SYSTEM */
            #euno-toast-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 10px;
                pointer-events: none;
            }
            .euno-toast {
                pointer-events: auto;
                min-width: 300px;
                padding: 16px 20px;
                border-radius: 5px;
                background: white;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                display: flex;
                align-items: center;
                gap: 12px;
                animation: toastIn 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
                border-left: 4px solid #3b82f6;
            }            .euno-toast.success { border-left-color: #10b981; }
            .euno-toast.error { border-left-color: #ef4444; }
            .euno-toast.fadeOut { animation: toastOut 0.3s ease forwards; }

            @keyframes toastIn {
                from { opacity: 0; transform: translateX(100px); }
                to { opacity: 1; transform: translateX(0); }
            }
            @keyframes toastOut {
                from { opacity: 1; transform: translateX(0); }
                to { opacity: 0; transform: translateX(100px); }
            }

            /* Forzar avatar redondo en caso de conflicto con temas */
            #euno_profile_img {
                width: 112px !important;
                height: 112px !important;
                min-width: 112px !important;
                min-height: 112px !important;
                max-width: 112px !important;
                max-height: 112px !important;
                object-fit: cover !important;
                border-radius: 50% !important;
                aspect-ratio: 1 / 1 !important;
            }
            .euno-camera-overlay {
                border-radius: 50% !important;
            }
        </style>

        <div class="ms-profile max-w-[1250px] mx-auto p-4 md:p-6">
            
            <!-- Toast Container -->
            <div id="euno-toast-container"></div>

            <!-- Perfil Header (Sin botones) -->
            <div class="ms-header-card bg-white mb-10">
                <div class="p-10 flex flex-col md:flex-row items-center gap-8 border-b border-slate-50">
                    <div class="relative group cursor-pointer" onclick="document.getElementById('euno_avatar_input').click()">
                        <img id="euno_profile_img" src="<?php echo esc_url( $avatar ); ?>" class="w-28 h-28 rounded-full border-4 border-white shadow-xl object-cover transition-opacity group-hover:opacity-75" alt="Avatar">
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <div class="bg-black/40 rounded-full p-3 backdrop-blur-sm">
                                <i class="fas fa-camera text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="absolute bottom-1 right-1 w-6 h-6 bg-emerald-500 border-4 border-white rounded-full"></div>
                        <input type="file" id="euno_avatar_input" class="hidden" accept="image/*" onchange="eunoUploadAvatar(this)">
                    </div>
                    <div class="text-center md:text-left">
                        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight"><?php echo esc_html( $name ); ?></h1>
                        <p class="text-sm text-slate-400 font-bold uppercase tracking-widest mt-2 flex items-center justify-center md:justify-start">
                            <i class="fas fa-calendar-check mr-2 text-blue-500"></i> Miembro desde <?php echo esc_html( $registered_formatted ); ?>
                        </p>
                    </div>
                </div>

                <script>
                    function showEunoToast(message, type = 'success') {
                        const container = document.getElementById('euno-toast-container');
                        const toast = document.createElement('div');
                        toast.className = `euno-toast ${type}`;
                        
                        const icon = type === 'success' ? 'fa-check-circle text-emerald-500' : 'fa-exclamation-circle text-red-500';
                        
                        toast.innerHTML = `
                            <i class="fas ${icon} text-xl"></i>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-slate-800">${message}</p>
                            </div>
                        `;
                        
                        container.appendChild(toast);
                        
                        setTimeout(() => {
                            toast.classList.add('fadeOut');
                            setTimeout(() => toast.remove(), 300);
                        }, 4000);
                    }

                    async function eunoUploadAvatar(input) {
                        if (!input.files || !input.files[0]) return;
                        
                        const file = input.files[0];
                        if (file.size > 2 * 1024 * 1024) {
                            showEunoToast('La imagen es demasiado grande. Máximo 2MB.', 'error');
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = async function(e) {
                            const base64 = e.target.result;
                            const userId = <?php echo $user_id; ?>;
                            
                            // Preview
                            const originalSrc = document.getElementById('euno_profile_img').src;
                            document.getElementById('euno_profile_img').src = base64;
                            
                            showEunoToast('Subiendo imagen...', 'success');
                            
                            try {
                                const response = await fetch('/wp-json/wp-abilities/v1/abilities/support/upload-user-avatar/run', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-WP-Nonce': '<?php echo wp_create_nonce( 'wp_rest' ); ?>'
                                    },
                                    body: JSON.stringify({
                                        input: {
                                            user_id: userId,
                                            filename: file.name,
                                            base64: base64
                                        }
                                    })
                                });
                                
                                const result = await response.json();
                                if (result.success || response.ok) {
                                    showEunoToast('¡Perfil actualizado con éxito!', 'success');
                                } else {
                                    document.getElementById('euno_profile_img').src = originalSrc;
                                    showEunoToast('Error: ' + (result.message || 'No se pudo subir.'), 'error');
                                }
                            } catch (err) {
                                document.getElementById('euno_profile_img').src = originalSrc;
                                showEunoToast('Error de conexión al servidor.', 'error');
                            }
                        };
                        
                        reader.readAsDataURL(file);
                    }
                </script>

                <!-- Stats Grid (Compact & Subtle) -->
                <div class="flex flex-wrap items-center justify-center border-t border-slate-100 bg-white">
                    <div class="flex-1 min-w-[120px] py-4 flex flex-col items-center border-r border-slate-100 last:border-r-0">
                        <i class="fas fa-book-open text-blue-500 mb-2 text-lg"></i>
                        <p class="text-[12px] text-slate-500 font-medium">Cursos <b class="text-slate-900 ml-1"><?php echo $total_enrolled; ?></b></p>
                    </div>
                    <div class="flex-1 min-w-[120px] py-4 flex flex-col items-center border-r border-slate-100 last:border-r-0">
                        <i class="fas fa-question-circle text-blue-500 mb-2 text-lg"></i>
                        <p class="text-[12px] text-slate-500 font-medium">Cuestionarios <b class="text-slate-900 ml-1"><?php echo $quizzes_done; ?></b></p>
                    </div>
                    <div class="flex-1 min-w-[120px] py-4 flex flex-col items-center border-r border-slate-100 last:border-r-0">
                        <i class="fas fa-users text-blue-500 mb-2 text-lg"></i>
                        <p class="text-[12px] text-slate-500 font-medium">Grupos <b class="text-slate-900 ml-1"><?php echo $group_count; ?></b></p>
                    </div>
                    <div class="flex-1 min-w-[120px] py-4 flex flex-col items-center border-r border-slate-100 last:border-r-0">
                        <i class="fas fa-award text-blue-500 mb-2 text-lg"></i>
                        <p class="text-[12px] text-slate-500 font-medium">Certificados <b class="text-slate-900 ml-1"><?php echo $total_completed; ?></b></p>
                    </div>
                </div>
            </div>

            <!-- Navegación por Pestañas (Diseño Mejorado) -->
            <div class="ms-tabs-container">
                <button class="ms-tab-btn active" data-target="ms-inprogress">
                    <i class="fas fa-spinner mr-2"></i> En Progreso
                </button>
                <button class="ms-tab-btn" data-target="ms-notstarted">
                    <i class="fas fa-lock mr-2"></i> Sin Iniciar
                </button>
                <button class="ms-tab-btn" data-target="ms-completed">
                    <i class="fas fa-check-circle mr-2"></i> Completados
                </button>
            </div>

            <!-- Contenido de Pestañas -->
            <div id="ms-inprogress" class="ms-tab-content active">
                <?php self::render_course_list($in_progress_courses, 'No tienes cursos en progreso actualmente.'); ?>
            </div>
            <div id="ms-notstarted" class="ms-tab-content">
                <?php self::render_course_list($not_started_courses, 'No tienes cursos pendientes por iniciar.'); ?>
            </div>
            <div id="ms-completed" class="ms-tab-content">
                <?php self::render_course_list($completed_courses, 'Aún no has completado ningún curso.'); ?>
            </div>

        </div>

        <script>
            document.querySelectorAll('.ms-tab-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.ms-tab-btn').forEach(b => b.classList.remove('active'));
                    document.querySelectorAll('.ms-tab-content').forEach(c => c.classList.remove('active'));
                    
                    btn.classList.add('active');
                    document.getElementById(btn.getAttribute('data-target')).classList.add('active');
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }

    private static function render_course_list($courses, $empty_msg) {
        if (empty($courses)) {
            echo '<div class="py-16 flex flex-col md:flex-row items-center justify-center text-slate-400 font-bold bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                    <i class="fas fa-inbox text-3xl mb-4 md:mb-0 md:mr-4"></i>
                    <span>' . esc_html($empty_msg) . '</span>
                  </div>';
            return;
        }

        echo '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">';
        foreach ($courses as $c) {
            $progress = ($c['steps_total'] > 0) ? round(($c['steps_completed'] / $c['steps_total']) * 100) : 0;
            ?>
            <div class="course-card bg-white flex flex-col">
                <div class="h-48 overflow-hidden relative">
                    <img src="<?php echo esc_url($c['image']); ?>" class="w-full h-full object-cover transition-transform duration-500 hover:scale-110" alt="Course Image">
                    <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-[10px] font-black text-blue-600 uppercase shadow-sm">
                        <?php echo $progress; ?>% Completado
                    </div>
                </div>
                <div class="p-6 flex-1 flex flex-col">
                    <h3 class="text-lg font-extrabold text-slate-800 mb-4 line-clamp-2 h-14 leading-tight"><?php echo esc_html($c['title']); ?></h3>
                    
                    <div class="flex items-center gap-4 text-[11px] text-slate-400 font-black uppercase mb-6">
                        <span><i class="fas fa-book-open mr-1 text-blue-400"></i> <?php echo $c['steps_total']; ?> Lecciones</span>
                        <span><i class="fas fa-check-double mr-1 text-emerald-400"></i> <?php echo $c['steps_completed']; ?> Listas</span>
                    </div>

                    <div class="mt-auto pt-4 border-t border-slate-50">
                        <?php if ( $c['is_completed'] ) : ?>
                            <?php $cert_href = ! empty( $c['certificate'] ) ? $c['certificate'] : add_query_arg( ['ld-profile-action' => 'get-certificate', 'course_id' => $c['id']], $c['url'] ); ?>
                            <div class="flex flex-col gap-2">
                                <a href="<?php echo esc_url($cert_href); ?>" target="_blank" class="btn-action w-full text-center bg-emerald-500 hover:bg-emerald-600 shadow-sm"><i class="fas fa-award mr-2"></i>Descargar Certificado</a>
                                <a href="<?php echo esc_url($c['url']); ?>" class="btn-secondary">Volver a ver lecciones</a>
                            </div>
                        <?php else : ?>
                            <?php 
                                $btn_text = ($c['steps_completed'] > 0) ? 'Continuar aprendiendo' : 'Iniciar Curso'; 
                            ?>
                            <a href="<?php echo esc_url($c['url']); ?>" class="btn-action block w-full text-center shadow-sm">
                                <?php echo $btn_text; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
        }
        echo '</div>';
    }
}
LMSEU_Student_Profile::init();