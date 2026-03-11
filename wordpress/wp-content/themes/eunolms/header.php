<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="icon" type="image/png" href="https://eks12.lmseunoconsulting.com/wp-content/uploads/2026/03/iso-euno.png">
    <title><?php wp_title( '|', true, 'right' ); ?></title>
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

    <div id="euno-site-wrapper">
        <header class="euno-header">
            <div class="euno-header-container">
                <div class="euno-logo">
                    <a href="<?php echo home_url( '/' ); ?>">
                        <img src="https://eks12.lmseunoconsulting.com/wp-content/uploads/2026/03/euno2025.png" alt="<?php bloginfo( 'name' ); ?>">
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