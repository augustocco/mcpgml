<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    // Favicon dinámico: isotipo del cliente activo o EUNO por defecto
    add_filter( 'get_site_icon_url', function( $url, $size, $blog_id ) {
        if ( class_exists('LMSEU_Client_Branding_Manager') ) {
            $branding = LMSEU_Client_Branding_Manager::get_client_branding( get_current_user_id() );
            if ( ! empty( $branding['isotype_url'] ) ) {
                return $branding['isotype_url'];
            }
        }
        return $url ?: WP_CONTENT_URL . '/uploads/2026/03/iso-euno.png';
    }, 10, 3 );
    ?>
    <?php wp_head(); ?>
    <?php
    // Colores corporativos del cliente: sobreescribir variables CSS dinámicamente
    if ( is_user_logged_in() && class_exists( 'LMSEU_Client_Branding_Manager' ) ) {
        $branding = LMSEU_Client_Branding_Manager::get_client_branding( get_current_user_id() );
        $cp  = ! empty( $branding['color_primary'] )   ? esc_attr( $branding['color_primary'] )   : '';
        $cs  = ! empty( $branding['color_secondary'] ) ? esc_attr( $branding['color_secondary'] ) : '';
        $ct  = ! empty( $branding['color_tertiary'] )  ? esc_attr( $branding['color_tertiary'] )  : '';
        if ( $cp || $cs || $ct ) {
            echo '<style>:root{';
            if ( $cp ) {
                echo '--euno-primary:' . $cp . ';';
                echo '--euno-primary-hover:' . $cp . ';';
            }
            if ( $cs ) echo '--euno-text-muted:' . $cs . ';';
            if ( $ct ) echo '--euno-text-main:' . $ct . ';';
            echo '}</style>';
        }
    }
    ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="euno-site-wrapper">
<header class="euno-header">
    <div class="euno-header-container">
        <div class="euno-logo">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <?php
                if ( class_exists('LMSEU_Client_Branding_Manager') ) {
                    $branding    = LMSEU_Client_Branding_Manager::get_client_branding( get_current_user_id() );
                    $logo_url    = $branding['logo_url'];
                    $isotype_url = $branding['isotype_url'];
                } else {
                    $cid         = get_theme_mod( 'custom_logo' );
                    $logo_url    = $cid ? wp_get_attachment_image_url( $cid, 'full' ) : WP_CONTENT_URL . '/uploads/2026/03/euno2025.png';
                    $isotype_url = WP_CONTENT_URL . '/uploads/2026/03/iso-euno.png';
                }
                ?>
                <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php bloginfo( 'name' ); ?>" class="euno-logo-full">
            </a>
        </div>
        <nav class="euno-main-nav">
            <?php
            if ( has_nav_menu( 'primary' ) ) {
                wp_nav_menu( array(
                    'theme_location' => 'primary',
                    'container'      => false,
                ) );
            } else {
                echo '<ul><li><a href="' . admin_url('nav-menus.php') . '">Asignar Menú Principal</a></li></ul>';
            }
            ?>
        </nav>
    </div>
</header>
<div id="euno-spa-root">
