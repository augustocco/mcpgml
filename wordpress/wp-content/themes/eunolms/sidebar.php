<aside class="sidebar">
    <div class="container">
        <?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
            <?php dynamic_sidebar( 'sidebar-1' ); ?>
        <?php else : ?>
            <div class="widget">
                <h3><?php _e( 'Buscador', 'eunolms' ); ?></h3>
                <?php get_search_form(); ?>
            </div>
            
            <div class="widget">
                <h3><?php _e( 'Archivos', 'eunolms' ); ?></h3>
                <ul>
                    <?php wp_get_archives(); ?>
                </ul>
            </div>
            
            <div class="widget">
                <h3><?php _e( 'Categorías', 'eunolms' ); ?></h3>
                <ul>
                    <?php wp_list_categories(); ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</aside>
