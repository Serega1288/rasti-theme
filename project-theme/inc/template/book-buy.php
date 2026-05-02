<?php
$section_id   = get_sub_field( 'id_block' ) ? get_sub_field( 'id_block' ) : 'section-' . $args;
$product_post = get_sub_field( 'product' );
$product_id   = is_object( $product_post ) && isset( $product_post->ID ) ? (int) $product_post->ID : (int) $product_post;
$product      = $product_id ? wc_get_product( $product_id ) : null;
?>

<section
        <?php if ( get_sub_field( 'id_block' ) ) : ?>
            id="<?php the_sub_field( 'id_block' ); ?>"
        <?php else : ?>
            id="section-<?php echo esc_attr( $args ); ?>"
        <?php endif; ?>
        class="section block-buy">
    <div class="container-fluid">
        <div class="box pos">
            <div class="lines lines-2">
                <div class="line line-1 circle"></div>
                <div class="line line-2 circle"></div>
                <div class="line line-3 circle"></div>
                <div class="line line-4"></div>
                <div class="line line-5"></div>
            </div>
            <div class="wrap-decore wrap-decore-3">
                <div class="decore decore-1 d-minus"></div>
                <div class="decore decore-2 d-minus"></div>
                <div class="decore decore-3 d-plus"></div>
                <div class="decore decore-4 d-minus"></div>
                <div class="decore decore-5 d-minus"></div>
                <div class="decore decore-6 d-minus"></div>
                <div class="decore decore-7 d-plus"></div>
                <div class="decore decore-8 d-plus"></div>
                <div class="decore decore-9 d-minus"></div>
                <div class="decore decore-10 d-minus"></div>
                <div class="decore decore-11 d-minus"></div>
            </div>
            <div class="box-1 d-sm-none">
                <div class="text-1">
                    <?php echo get_sub_field( 'desc_banner' ); ?>
                </div>
            </div>
            <div class="box-2">
                <div class="row">
                    <div class="col-12 col-sm-4 d-flex align-items-center ">
                        <div class="text-2 t-green ts-20 ts-sm-16">
                            <?php echo get_sub_field( 'desc' ); ?>
                        </div>
                    </div>
                    <div class="col-12 col-sm-4 d-flex align-items-center">
                        <div class="wrap">
                            <div class="wrap-img">
                                <?php /* if ( $product ) : ?>
                                    <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="wrap-img-link d-block">
                                        <?php echo $product->get_image( 'woocommerce_thumbnail' ); ?>
                                    </a>
                                <?php endif; */ ?>
                                <?php  if ( $product ) : ?>
                                <?php
                                $image_id = $product->get_image_id();

                                if ( $image_id ) {
                                    $image_url = wp_get_attachment_image_url( $image_id, 'woocommerce_thumbnail' );
                                    $image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );

                                    if ( empty( $image_alt ) ) {
                                        $image_alt = $product->get_name();
                                    }
                                } else {
                                    $image_url = wc_placeholder_img_src( 'woocommerce_thumbnail' );
                                    $image_alt = $product->get_name();
                                }
                                ?>

                                <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="wrap-img-link d-block">
                                    <img
                                            class="product-card-img lazy-img"
                                            src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='1' height='1' viewBox='0 0 1 1'%3E%3C/svg%3E"
                                            data-src="<?php echo esc_url( $image_url ); ?>"
                                            alt="<?php echo esc_attr( $image_alt ); ?>"
                                            loading="lazy"
                                            decoding="async"
                                    >
                                </a>
                            <?php endif;  ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-4 d-flex align-items-center justify-content-end">
                        <?php if ( $product ) : ?>
                            <?php if ( $product->is_in_stock() ) : ?>
                                <?php /*
                                <a
                                    href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
                                    data-quantity="1"
                                    data-product_id="<?php echo esc_attr( $product_id ); ?>"
                                    data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
                                    class="btn btn-1 button add_to_cart_button ajax_add_to_cart ttu"
                                    aria-label="<?php echo esc_attr( $product->add_to_cart_description() ); ?>">
                                    <?php // echo esc_html( $product->add_to_cart_text() ); ?>
                                    передзамовлення
                                </a>
                                */ ?>
                                <a
                                    href="<?php echo esc_url( $product->get_permalink() ); ?>"
                                    class="btn btn-1 ttu">
                                    передзамовлення
                                </a>
                            <?php else : ?>
                                <span class="stock out-of-stock btn btn-1 disabled">
                                    <?php esc_html_e( 'Нема в наявності', 'project-theme' ); ?>
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
