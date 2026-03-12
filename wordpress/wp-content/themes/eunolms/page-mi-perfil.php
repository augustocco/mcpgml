<?php
/**
 * Template Name: Perfil de Usuario
 */

get_header(); ?>

<main class="euno-main-content">
    <div class="euno-container euno-main-box">
        <div class="euno-main-box-inner">
            <?php echo do_shortcode( '[euno_student_profile]' ); ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
