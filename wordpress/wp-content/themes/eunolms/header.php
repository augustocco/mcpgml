<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                $logo_url = ( class_exists('LMSEU_Branding') ) 
                    ? LMSEU_Branding::get_company_logo_url() 
                    : ( ( $cid = get_theme_mod( 'custom_logo' ) ) ? wp_get_attachment_image_url( $cid, 'full' ) : '' );
                ?>
                <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php bloginfo( 'name' ); ?>">
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
