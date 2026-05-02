<?php
/**
 * Main theme template.
 * template name: Constructor
 * @package ProjectTheme
 */

get_header();
?>

    <main id="swup" class="transition-fade" role="main">
        <div class="template-page constructor">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <?php get_template_part( 'inc/box', 'constructor'); ?>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </main>
<?php
get_footer();
