</div><!-- #euno-spa-root -->
<footer class="euno-footer" id="colophon">
    <div class="euno-footer-container">
        <div class="euno-footer-main">
            <div class="euno-footer-brand">
                <?php 
                $logo_url = ( class_exists('LMSEU_Branding') ) 
                    ? LMSEU_Branding::get_company_logo_url() 
                    : 'https://eks10.lmseunoconsulting.com/wp-content/uploads/2026/03/euno2025-2048x614-1.png';
                ?>
                <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php bloginfo( 'name' ); ?>" class="euno-footer-logo">
                <p class="euno-footer-description">Plataforma de capacitación integral EUNO LMS. Potenciando el conocimiento de tu equipo.</p>
                <div class="euno-footer-socials">
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                </div>
            </div>

            <div class="euno-footer-links-group">
                <h4 class="euno-footer-title">Navegación</h4>
                <nav class="euno-footer-nav">
                    <?php
                    if ( has_nav_menu( 'footer' ) ) {
                        wp_nav_menu( array(
                            'theme_location' => 'footer',
                            'container'      => false,
                            'depth'          => 1,
                        ) );
                    } else {
                        echo '<ul><li><a href="' . esc_url( home_url( '/' ) ) . '">Inicio</a></li><li><a href="' . esc_url( home_url( '/cursos/' ) ) . '">Cursos</a></li><li><a href="' . esc_url( home_url( '/ayuda/' ) ) . '">Ayuda</a></li></ul>';
                    }
                    ?>
                </nav>
            </div>

            <div class="euno-footer-contact">
                <h4 class="euno-footer-title">Soporte</h4>
                <ul class="euno-footer-contact-list">
                    <li><i class="fas fa-envelope"></i> soporte@eunoconsulting.com</li>
                    <li><i class="fas fa-life-ring"></i> Centro de Ayuda</li>
                </ul>
            </div>
        </div>

        <div class="euno-footer-bottom">
            <p>&copy; <?php echo date( 'Y' ); ?> EUNO Consulting. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>
</div><!-- #euno-site-wrapper -->

<?php wp_footer(); ?>
</body>
</html>
