<?php get_header(); ?>

<main class="euno-main-content">
    <div class="euno-container euno-main-box">
        <div class="euno-main-box-inner">
            <?php
            if ( have_posts() ) :
                while ( have_posts() ) : the_post();
                    the_content();
                endwhile;
            else :
                echo '<p>No se encontró contenido.</p>';
            endif;
            ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
