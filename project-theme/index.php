<?php
/**
 * Main theme template.
 *
 * @package ProjectTheme
 */

get_header();
?>

<main id="swup" class="transition-fade" role="main">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <section class="section section-stub section-first pos line-end">
                <div class="container-fluid">
                    <div class="box pos">
                        <div class="lines lines-1">
                            <div class="line line-1 circle"></div>
                            <div class="line line-2"></div>
                            <div class="line line-3"></div>
                        </div>
                       <div class="box-1">
                           <?php the_content(); ?>
                       </div>
                    </div>
                </div>
            </section>
        <?php endwhile; ?>
    <?php endif; ?>
</main>
<?php
get_footer();
