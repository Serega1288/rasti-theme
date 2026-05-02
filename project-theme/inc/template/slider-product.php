<?php

$title       = (string) get_sub_field( 'title' );
$description = (string) get_sub_field( 'desc' );
$type        = (string) get_sub_field( 'type_product' );
$category    = get_sub_field( 'cat' );
$products    = get_sub_field( 'product' );
$custom_product   = $products;
$category_product = $category;

$section_id = get_sub_field('id_block') ? get_sub_field('id_block') : 'section-' . $args;
?>
<section
        <?php if( get_sub_field('id_block') ) : ?>
            id="<?php the_sub_field('id_block'); ?>"
        <?php else : ?>
            id="section-<?php echo esc_attr( $section_id ); ?>"
        <?php endif; ?>
        class="section slider-product">
    <div class="container-fluid">

        <?php if ( '' !== $title ) : ?>
            <h2 class="title ts-32 ts-sm-20 t-green ttu text-center"><?php echo esc_html( $title ); ?></h2>
        <?php endif; ?>

        <?php if ( '' !== $description ) : ?>
            <div class="desc text-center t-white ts-16 ts-sm-12"><?php echo wp_kses_post( wpautop( $description ) ); ?></div>
        <?php endif; ?>

        <div class="products-slider wrap-products-slider" id="slider-<?php echo esc_attr( $section_id ); ?>">
            <div class="swiper js-products-slider" data-slider-id="<?php echo esc_attr( $section_id ); ?>">
                <div class="products swiper-wrapper">
                    <?php
                    if ( $type == 2 && !empty($custom_product) ) :
                        foreach ( $custom_product as $post ) :
                            setup_postdata($post); ?>
                            <div class="swiper-slide">
                                <?php wc_get_template_part( 'content', 'product' ); ?>
                            </div>
                        <?php endforeach;
                        wp_reset_postdata();

                    elseif ( $type == 1 ) :

                        $cat_ids = [];

                        if ( !empty($category_product) ) {
                            if ( is_array($category_product) ) {
                                foreach ( $category_product as $cat ) {
                                    $cat_ids[] = is_object($cat) ? $cat->term_id : (int)$cat;
                                }
                            } else {
                                $cat_ids[] = is_object($category_product) ? $category_product->term_id : (int)$category_product;
                            }
                        }

                        $args_cat = [
                                'post_type'      => 'product',
                                'posts_per_page' => 999,
                                'post_status'    => 'publish',
                        ];

                        if ( !empty($cat_ids) ) {
                            $args_cat['tax_query'] = [
                                    [
                                            'taxonomy' => 'product_cat',
                                            'field'    => 'term_id',
                                            'terms'    => $cat_ids,
                                    ],
                            ];
                        }

                        $cat_query = new WP_Query($args_cat);

                        if ( $cat_query->have_posts() ) :
                            while ( $cat_query->have_posts() ) : $cat_query->the_post(); ?>
                                <div class="swiper-slide">
                                    <?php wc_get_template_part( 'content', 'product' ); ?>
                                </div>
                            <?php endwhile;
                            wp_reset_postdata();
                        else :
                            echo '<p class="no-products">У вибраних категоріях товарів не знайдено.</p>';
                        endif;

                    endif;
                    ?>
                </div>
            </div>

            <div class="slider-navigation" data-slider-nav="<?php echo esc_attr( $section_id ); ?>">
                <button class="slider-prev" type="button" data-slider-prev="<?php echo esc_attr( $section_id ); ?>" aria-label="<?php esc_attr_e( 'Previous slide', 'project-theme' ); ?>">
                    <span></span>
                </button>
                <div class="wrap-slider-dots">
                    <div class="border border-1"></div>
                    <div class="slider-dots" data-slider-pagination="<?php echo esc_attr( $section_id ); ?>"> </div>
                    <div class="border border-2"></div>
                </div>
                <button class="slider-next" type="button" data-slider-next="<?php echo esc_attr( $section_id ); ?>" aria-label="<?php esc_attr_e( 'Next slide', 'project-theme' ); ?>">
                    <span></span>
                </button>
            </div>
        </div>
    </div>
</section>
