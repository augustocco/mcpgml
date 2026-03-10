<?php get_header(); ?>

<div class="container">
    <main>
        <?php if ( have_posts() ) : ?>
            <h1><?php _e( 'Últimas Entradas', 'eunolms' ); ?></h1>
            
            <?php while ( have_posts() ) : the_post(); ?>
                <article>
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="post-thumbnail">
                            <?php the_post_thumbnail( 'large' ); ?>
                        </div>
                    <?php endif; ?>
                    
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    
                    <div class="entry-meta">
                        <?php _e( 'Publicado el ', 'eunolms' ); ?>
                        <?php echo get_the_date(); ?>
                        <?php _e( ' por ', 'eunolms' ); ?>
                        <?php the_author(); ?>
                    </div>
                    
                    <div class="entry-content">
                        <?php the_excerpt(); ?>
                    </div>
                    
                    <a href="<?php the_permalink(); ?>" class="btn"><?php _e( 'Leer más', 'eunolms' ); ?></a>
                </article>
            <?php endwhile; ?>
            
            <div class="pagination">
                <?php the_posts_pagination(); ?>
            </div>
        <?php else : ?>
            <p><?php _e( 'No se encontraron entradas.', 'eunolms' ); ?></p>
        <?php endif; ?>
    </main>
    
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
