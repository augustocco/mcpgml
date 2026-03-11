<?php
/**
 * Template Name: Perfil de Usuario
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <div class="user-profile-container">
            <div class="user-profile-header">
                <div class="user-avatar">
                    <?php echo get_avatar( get_current_user_id(), 120 ); ?>
                </div>
                <div class="user-info">
                    <h1 class="user-name"><?php echo esc_html( wp_get_current_user()->display_name ); ?></h1>
                    <p class="user-membership-date"><?php printf( __( 'MIEMBRO DESDE %s', 'eunolms' ), date_i18n( 'F Y', strtotime( wp_get_current_user()->user_registered ) ) ); ?></p>
                </div>
            </div>
            <div class="user-stats">
                <div class="stat-item">
                    <span class="stat-icon"><i class="fas fa-book-open"></i></span>
                    <span class="stat-label"><?php _e( 'Cursos', 'eunolms' ); ?></span>
                    <span class="stat-value"><?php echo eunolms_get_user_stat( 'courses' ); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-icon"><i class="fas fa-tasks"></i></span>
                    <span class="stat-label"><?php _e( 'Asignaciones', 'eunolms' ); ?></span>
                    <span class="stat-value"><?php echo eunolms_get_user_stat( 'assignments' ); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-icon"><i class="fas fa-question-circle"></i></span>
                    <span class="stat-label"><?php _e( 'Cuestionarios', 'eunolms' ); ?></span>
                    <span class="stat-value"><?php echo eunolms_get_user_stat( 'quizzes' ); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-icon"><i class="fas fa-users"></i></span>
                    <span class="stat-label"><?php _e( 'Grupos', 'eunolms' ); ?></span>
                    <span class="stat-value"><?php echo eunolms_get_user_stat( 'groups' ); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-icon"><i class="fas fa-certificate"></i></span>
                    <span class="stat-label"><?php _e( 'Certificados', 'eunolms' ); ?></span>
                    <span class="stat-value"><?php echo eunolms_get_user_stat( 'certificates' ); ?></span>
                </div>
            </div>
            <div class="user-courses-tabs">
                <ul class="tabs-nav">
                    <li class="tab-active"><a href="#en-progreso"><?php _e( 'EN PROGRESO', 'eunolms' ); ?></a></li>
                    <li><a href="#sin-iniciar"><?php _e( 'SIN INICIAR', 'eunolms' ); ?></a></li>
                    <li><a href="#completados"><?php _e( 'COMPLETADOS', 'eunolms' ); ?></a></li>
                </ul>
                <div class="tabs-content">
                    <div id="en-progreso" class="tab-pane active">
                        <!-- Content loaded dynamically via AJAX -->
                    </div>
                    <div id="sin-iniciar" class="tab-pane">
                        <!-- Content loaded dynamically via AJAX -->
                    </div>
                    <div id="completados" class="tab-pane">
                        <!-- Content loaded dynamically via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php get_footer(); ?>
