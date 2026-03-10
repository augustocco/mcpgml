<?php
/**
 * Genera el shortcode de la página de mis cursos [euno_enrolled_courses]
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LMSEU_Enrolled_Courses {
    public static function init() {
        add_action( 'init', function() {
            add_shortcode( 'euno_enrolled_courses', array( 'LMSEU_Enrolled_Courses', 'render_courses' ) );
        });
    }

    public static function render_courses( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<div style="padding: 40px; text-align: center; font-family: sans-serif;">Debes iniciar sesión para ver tus cursos.</div>';
        }

        $user_id = get_current_user_id();
        $enrolled_course_ids = function_exists('learndash_user_get_enrolled_courses') ? learndash_user_get_enrolled_courses( $user_id ) : [];

        $all_courses = [];
        $unique_categories = [];

        foreach ( $enrolled_course_ids as $course_id ) {
            $course_post = get_post( $course_id );
            if ( ! $course_post ) continue;

            $status_raw = function_exists('learndash_course_status') ? learndash_course_status( $course_id, $user_id ) : 'not started';
            $status_lower = strtolower( $status_raw );
            
            $steps_completed = function_exists('learndash_course_get_completed_steps') ? learndash_course_get_completed_steps( $user_id, $course_id ) : 0;
            $steps_total = function_exists('learndash_get_course_steps_count') ? learndash_get_course_steps_count( $course_id ) : 0;
            
            $is_completed = in_array($status_lower, ['completed', 'completado', 'terminado', 'finalizado', 'yes']);
            
            $status_key = 'not_started';
            if ( $is_completed ) {
                $status_key = 'completed';
            } elseif ( $steps_completed > 0 ) {
                $status_key = 'in_progress';
            }

            $default_image_id = get_option( 'euno_default_course_image_id' );
            $default_image_url = 'https://eks10.lmseunoconsulting.com/wp-content/uploads/2026/03/EnConstruccion.webp';

            $cert_link = '';
            if ( function_exists('learndash_get_course_certificate_link') ) {
                $cert_link = learndash_get_course_certificate_link( $course_id, $user_id );
            }

            // Categorias
            $course_cat_ids = [];
            $terms = wp_get_post_terms( $course_id, 'ld_course_category' );
            if ( is_wp_error($terms) || empty($terms) ) {
                $terms = wp_get_post_terms( $course_id, 'category' );
            }
            if ( !is_wp_error($terms) && !empty($terms) ) {
                foreach($terms as $t) {
                    $unique_categories[$t->term_id] = $t->name;
                    $course_cat_ids[] = $t->term_id;
                }
            }

            $all_courses[] = [
                'id' => $course_id,
                'title' => $course_post->post_title,
                'url' => get_permalink( $course_id ),
                'image' => get_the_post_thumbnail_url( $course_id, 'medium' ) ?: $default_image_url,
                'steps_completed' => $steps_completed,
                'steps_total' => $steps_total,
                'certificate' => $cert_link,
                'is_completed' => $is_completed,
                'status_key' => $status_key,
                'cat_ids' => implode(',', $course_cat_ids)
            ];
        }

        $count_all = count($all_courses);
        $count_completed = 0;
        $count_in_progress = 0;
        $count_not_started = 0;

        foreach($all_courses as $c) {
            if($c['status_key'] === 'completed') $count_completed++;
            elseif($c['status_key'] === 'in_progress') $count_in_progress++;
            elseif($c['status_key'] === 'not_started') $count_not_started++;
        }

        ob_start();
        ?>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
            .ms-courses-page { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fff; position: relative; }
            
            .ms-header-row {
                display: flex;
                flex-direction: column;
                md:flex-direction: row;
                justify-content: space-between;
                align-items: center;
                border-bottom: 1px solid #e2e8f0;
                margin-bottom: 1.5rem;
                padding-bottom: 0;
            }

            .ms-page-title {
                font-size: 28px;
                font-weight: 800;
                color: #0f172a;
                margin: 0 0 1rem 0;
            }

            @media (min-width: 768px) {
                .ms-header-row { flex-direction: row; }
                .ms-page-title { margin-bottom: 0; }
            }

            .ms-tabs-container { 
                display: flex; 
                gap: 24px; 
                border-radius: 5px;
            }
            
            .ms-tab-btn { 
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 8px 12px 16px 12px !important; 
                font-size: 15px; 
                font-weight: 700; 
                color: #475569 !important; 
                background: transparent !important;
                border: none !important;
                border-bottom: 3px solid transparent !important;
                outline: none !important;
                box-shadow: none !important;
                cursor: pointer;
                transition: all 0.2s ease;
                margin-bottom: -1px;
            }
            
            .ms-tab-btn:hover { 
                color: #3b82f6 !important; 
                background: transparent !important;
            }
            
            .ms-tab-btn.active { 
                color: #3b82f6 !important; 
                border-bottom-color: #3b82f6 !important;
            }

            #euno-search-toggle {
                border: none !important;
                outline: none !important;
                box-shadow: none !important;
                background: transparent !important;
                padding: 0 !important;
                width: 36px !important;
                height: 36px !important;
                color: #94a3b8 !important; /* slate-400 */
                opacity: 1 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                border-radius: 50% !important;
                transition: all 0.2s ease !important;
                cursor: pointer !important;
            }
            #euno-search-toggle i {
                color: inherit !important;
                opacity: 1 !important;
                font-size: 16px !important;
            }
            #euno-search-toggle:hover,
            #euno-search-toggle:focus,
            #euno-search-toggle.active {
                background: #f1f5f9 !important; /* slate-100 */
                color: #3b82f6 !important; /* blue-500 */
                opacity: 1 !important;
            }

            .ms-tab-count {
                background: #f1f5f9;
                color: #64748b;
                font-size: 11px;
                font-weight: 800;
                padding: 2px 8px;
                border-radius: 5px; /* Small pill shape for counts */
            }

            .ms-tab-btn.active .ms-tab-count {
                background: #3b82f6;
                color: #ffffff;
            }
            
            .cat-filter-btn {
                display: flex;
                align-items: center;
                justify-content: space-between;
                width: 100%;
                padding: 10px 15px;
                font-size: 13px;
                font-weight: 700;
                color: #64748b;
                background: transparent;
                border: 1px solid transparent;
                text-align: left;
                cursor: pointer;
                transition: all 0.2s;
                border-radius: 5px;
            }
            .cat-filter-btn:hover { color: #3b82f6; background: #f8fafc; }
            .cat-filter-btn.active { color: #3b82f6; background: #eff6ff; border-left: 3px solid #3b82f6; font-weight: 800; }

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

            @keyframes slideDownFade {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            </style>

            <div class="ms-courses-page max-w-[1250px] mx-auto p-4 md:p-6">

            <!-- Encabezado y Pestañas -->
            <div class="ms-header-row">
                <h2 class="ms-page-title">Cursos</h2>
                <div class="ms-tabs-container items-center">
                    <button class="ms-tab-btn active" data-filter-status="all">
                        Todos <span class="ms-tab-count"><?php echo $count_all; ?></span>
                    </button>
                    <button class="ms-tab-btn" data-filter-status="completed">
                        Completados <span class="ms-tab-count"><?php echo $count_completed; ?></span>
                    </button>
                    <button class="ms-tab-btn" data-filter-status="in_progress">
                        En progreso <span class="ms-tab-count"><?php echo $count_in_progress; ?></span>
                    </button>
                    <button class="ms-tab-btn" data-filter-status="not_started">
                        Sin comenzar <span class="ms-tab-count"><?php echo $count_not_started; ?></span>
                    </button>
                    <button id="euno-search-toggle" class="ml-2" title="Buscar cursos">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <!-- Buscador -->
            <div id="euno-search-container" class="justify-end w-full mb-8" style="display: none; animation: slideDownFade 0.3s ease-out forwards;">
                <div class="relative w-full md:w-96 group">
                    <input type="text" id="euno-course-search"
                           class="w-full px-5 py-3.5 bg-white rounded-[5px] text-sm focus:outline-none transition-all duration-500 text-slate-600 font-medium placeholder-slate-300"
                           style="border: 1px solid #f8fafc !important; box-shadow: 0 2px 15px rgba(0,0,0,0.02) !important;"
                           onfocus="this.style.borderColor='#e2e8f0'; this.style.boxShadow='0 4px 20px rgba(59,130,246,0.05)';"
                           onblur="this.style.borderColor='#f8fafc'; this.style.boxShadow='0 2px 15px rgba(0,0,0,0.02)';"
                           placeholder="Buscar curso por nombre...">
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-8">
                <!-- Sidebar Categorías -->
                <div class="w-full md:w-1/4 lg:w-1/5 shrink-0">
                    <div class="border border-slate-100 p-4 sticky top-24 bg-white rounded-[5px]">
                        <h4 class="font-extrabold text-slate-800 uppercase tracking-widest text-xs mb-4 pb-4 border-b border-slate-100">
                            <i class="fas fa-filter text-blue-500 mr-2"></i> Categorías
                        </h4>
                        <div class="flex flex-col gap-1">
                            <button class="cat-filter-btn active" data-filter-cat="all">Todas las categorías</button>
                            <?php foreach($unique_categories as $id => $name): ?>
                                <button class="cat-filter-btn" data-filter-cat="<?php echo esc_attr($id); ?>">
                                    <?php echo esc_html($name); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Grid de Cursos (3 columnas debido al sidebar) -->
                <div class="w-full md:w-3/4 lg:w-4/5">
                    <?php if (empty($all_courses)): ?>
                        <div class="py-16 flex flex-col md:flex-row items-center justify-center text-slate-400 font-bold bg-slate-50 border-2 border-dashed border-slate-200 rounded-[5px]">
                            <i class="fas fa-inbox text-3xl mb-4 md:mb-0 md:mr-4"></i>
                            <span>No tienes cursos inscritos.</span>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="euno-courses-grid">
                            <?php foreach ($all_courses as $c): ?>
                                <?php $progress = ($c['steps_total'] > 0) ? round(($c['steps_completed'] / $c['steps_total']) * 100) : 0; ?>
                                
                                <div class="course-card bg-white flex flex-col euno-course-item" 
                                     data-status="<?php echo esc_attr($c['status_key']); ?>" 
                                     data-cats="<?php echo esc_attr($c['cat_ids']); ?>"
                                     data-title="<?php echo esc_attr(strtolower($c['title'])); ?>">
                                     
                                    <div class="h-48 overflow-hidden relative">
                                        <img src="<?php echo esc_url($c['image']); ?>" class="w-full h-full object-cover transition-transform duration-500 hover:scale-110" alt="Course Image">
                                        <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 text-[10px] font-black text-blue-600 uppercase shadow-sm rounded-[5px]">
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
                            <?php endforeach; ?>
                        </div>
                        
                        <div id="euno-no-results" class="hidden py-16 flex-col md:flex-row items-center justify-center text-slate-400 font-bold bg-slate-50 border-2 border-dashed border-slate-200 rounded-[5px]">
                            <i class="fas fa-search text-3xl mb-4 md:mb-0 md:mr-4"></i>
                            <span>No se encontraron cursos con estos filtros.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const statusBtns = document.querySelectorAll('.ms-tab-btn[data-filter-status]');
                const catBtns = document.querySelectorAll('.cat-filter-btn');
                const courses = document.querySelectorAll('.euno-course-item');
                const noResults = document.getElementById('euno-no-results');
                const searchInput = document.getElementById('euno-course-search');
                const searchToggle = document.getElementById('euno-search-toggle');
                const searchContainer = document.getElementById('euno-search-container');
                
                let currentStatus = 'all';
                let currentCat = 'all';
                let currentSearch = '';

                if (searchToggle && searchContainer) {
                    searchToggle.addEventListener('click', () => {
                        if (searchContainer.style.display === 'none') {
                            searchContainer.style.display = 'flex';
                            searchToggle.classList.add('active');
                            if (searchInput) searchInput.focus();
                        } else {
                            searchContainer.style.display = 'none';
                            searchToggle.classList.remove('active');
                            if (searchInput) {
                                searchInput.value = '';
                                currentSearch = '';
                                filterCourses();
                            }
                        }
                    });
                }

                function filterCourses() {
                    let visibleCount = 0;
                    
                    courses.forEach(course => {
                        const statusMatch = currentStatus === 'all' || course.dataset.status === currentStatus;
                        const cats = course.dataset.cats.split(',');
                        const catMatch = currentCat === 'all' || cats.includes(currentCat);
                        const titleMatch = currentSearch === '' || course.dataset.title.includes(currentSearch);
                        
                        if (statusMatch && catMatch && titleMatch) {
                            course.style.display = 'flex';
                            visibleCount++;
                        } else {
                            course.style.display = 'none';
                        }
                    });

                    if (visibleCount === 0 && courses.length > 0) {
                        if (noResults) noResults.style.display = 'flex';
                    } else if (noResults) {
                        if (noResults) noResults.style.display = 'none';
                    }
                }

                if (searchInput) {
                    searchInput.addEventListener('input', (e) => {
                        currentSearch = e.target.value.toLowerCase().trim();
                        filterCourses();
                    });
                }

                statusBtns.forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        statusBtns.forEach(b => b.classList.remove('active'));
                        e.target.closest('.ms-tab-btn').classList.add('active');
                        currentStatus = e.target.closest('.ms-tab-btn').dataset.filterStatus;
                        filterCourses();
                    });
                });

                catBtns.forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        catBtns.forEach(b => b.classList.remove('active'));
                        e.target.classList.add('active');
                        currentCat = e.target.dataset.filterCat;
                        filterCourses();
                    });
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }
}
LMSEU_Enrolled_Courses::init();