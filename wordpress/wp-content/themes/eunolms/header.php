<?php
/**
 * Obtener branding del cliente actual
 */
$current_user_id = get_current_user_id();
$client_branding = LMSEU_Client_Branding_Manager::get_client_branding( $current_user_id );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="icon" type="image/png" href="<?php echo esc_url( $client_branding['logo_url'] ?: 'https://eks12.lmseunoconsulting.com/wp-content/uploads/2026/03/iso-euno.png' ); ?>">
    <title><?php wp_title( '|', true, 'right' ); ?></title>
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
    
    <!-- Variables CSS dinámicas con colores del cliente -->
    <style>
        :root {
            --euno-primary: <?php echo esc_attr( $client_branding['color_primary'] ); ?>;
            --euno-primary-hover: <?php echo esc_attr( $client_branding['color_secondary'] ); ?>;
            --euno-text-main: <?php echo esc_attr( $client_branding['color_tertiary'] ); ?>;
            --euno-text-muted: #64748b;
        }
    </style>
    
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

    <div id="euno-site-wrapper">
        <header class="euno-header">
            <div class="euno-header-container">
                <div class="euno-logo">
                    <a href="<?php echo home_url( '/' ); ?>">
                        <img src="<?php echo esc_url( $client_branding['logo_url'] ); ?>" 
                             alt="<?php bloginfo( 'name' ); ?>"
                             style="max-height: 60px; object-fit: contain;">
                    </a>
                </div>
                <nav class="euno-main-nav">
                    <?php wp_nav_menu( array(
                        'theme_location' => 'primary',
                        'container'      => false,
                        'menu_class'     => '',
                        'fallback_cb'    => false,
                    ) ); ?>
                </nav>
            </div>
        </header>
        
        <div id="euno-spa-root">
            <main class="euno-main-content">
                <div class="euno-main-box">
                    <div class="euno-main-box-inner">