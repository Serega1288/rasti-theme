<?php
/**
 * Main theme template.
 * template name: Stub
 * @package ProjectTheme
 */

get_header('stub');
?>

    <main id="swup" class="transition-fade" role="main">
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <section class="section section-stub section-first pos line-end">
                    <?php
                    $fon_block        = get_field('fon_block');
                    $fon_block_mobile = get_field('fon_block_mobile');
                    $fon_desktop_url  = $fon_block['url'] ?? '';
                    $fon_mobile_url   = $fon_block_mobile['url'] ?? '';
                    $fon_alt          = $fon_block['alt'] ?? $fon_block_mobile['alt'] ?? get_the_title();

                    if ( $fon_desktop_url || $fon_mobile_url ) : ?>
                    <div class="fon" aria-hidden="true">
                        <picture>
                            <?php if ( $fon_mobile_url ) : ?>
                                <source media="(max-width: 575px)" srcset="<?php echo esc_url( $fon_mobile_url ); ?>">
                            <?php endif; ?>
                            <img
                                src="<?php echo esc_url( $fon_desktop_url ?: $fon_mobile_url ); ?>"
                                alt="<?php echo esc_attr( $fon_alt ); ?>"
                                loading="eager"
                                decoding="async"
                            >
                        </picture>
                    </div>
                    <?php endif; ?>
                    <div class="container-fluid">
                        <div class="box pos">
                            <div class="lines lines-1">
                                <div class="line line-1 circle"></div>
                                <div class="line line-2"></div>
                                <div class="line line-3"></div>
                            </div>
                            <div class="box-1">
                                <?php
                                $title = get_field('big_title');
                                if ( $title ) : ?>
                                    <div class="title ts-128 ts-sm-96 t-white">
                                        <?php echo $title; ?>
                                    </div>
                                <?php endif; ?>
                                <?php
                                $desc = get_field('desc');
                                if ( $desc ) : ?>
                                    <div class="decs ts-20 ts-sm-16 t-green">
                                        <?php echo $desc; ?>
                                    </div>
                                <?php endif; ?>

                                <?php
                                $form_is = get_field('form_is_product_on');
                                $product = get_field('product');
                                if ( $form_is  && $product ) : ?>
                                    <div class="decs ts-20 ts-sm-16 t-green">
                                        <div class="form form-custom-xoo-wl-form">
                                            <?php echo do_shortcode('[xoo_wl_form id="' . $product->ID . '" type="inline"]'); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </section>
            <?php endwhile; ?>
        <?php endif; ?>
    </main>
<?php
get_footer('stub');
