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
            <div class="template-page default">
                <section class="section line-end">
                    <div class="container-fluid">
                        <div class="box pos">
                            <div class="lines lines-3">
                                <div class="line line-1"></div>
                                <div class="line line-2"></div>
                                <div class="line line-3"></div>
                            </div>
                            <div class="box-1 box-text">
                                <h1 class="title ts-43 ts-sm-24 ttu">
                                    <?php the_title(); ?>
                                </h1>
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
