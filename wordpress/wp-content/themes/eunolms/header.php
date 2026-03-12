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
        return $url ?: wp_upload_dir()['baseurl'] . '/2026/03/iso-euno.png';
    }, 10, 3 );
    ?>
    <?php wp_head(); ?>
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
                    $upload_url  = wp_upload_dir()['baseurl'];
                    $cid         = get_theme_mod( 'custom_logo' );
                    $logo_url    = $cid ? wp_get_attachment_image_url( $cid, 'full' ) : $upload_url . '/2026/03/euno2025.png';
                    $isotype_url = $upload_url . '/2026/03/iso-euno.png';
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
