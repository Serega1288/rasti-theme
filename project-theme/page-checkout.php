<?php
/**
 * Main theme template.
 * template name: Checkout
 * @package ProjectTheme
 */

get_header();

$back_url = wp_get_referer();

if ( ! $back_url ) {
    $back_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
}
?>

    <main id="swup" class="transition-fade" role="main">
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <div class="template-page checkout">
                    <section class="section ">
                        <div class="container-fluid">
                            <div class="box pos">
                                <div class="lines lines-3">
                                    <div class="line line-1"></div>
                                    <div class="line line-2"></div>
                                    <div class="line line-3"></div>
                                </div>
                                <div class="box-1 box-text pos">
                                    <?php the_content(); ?>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </main>
<?php
get_footer();
