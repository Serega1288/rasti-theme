<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

$product_attributes = array();
$summary_classes    = array( 'summary', 'entry-summary' );

if ( $product instanceof WC_Product ) {
    $summary_classes[] = 'product-type-' . $product->get_type();
}

if ( $product instanceof WC_Product ) {
    foreach ( $product->get_attributes() as $attribute ) {
        if ( ! $attribute instanceof WC_Product_Attribute ) {
            continue;
        }

        if ( ! $attribute->get_visible() ) {
            continue;
        }

        $attribute_values = array();

        if ( $attribute->is_taxonomy() ) {
            $terms = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) );

            if ( ! is_wp_error( $terms ) ) {
                $attribute_values = $terms;
            }
        } else {
            $attribute_values = $attribute->get_options();
        }

        $attribute_values = array_filter( array_map( 'trim', $attribute_values ) );

        if ( empty( $attribute_values ) ) {
            continue;
        }

        $product_attributes[] = array(
            'label' => wc_attribute_label( $attribute->get_name() ),
            'value' => implode( ', ', $attribute_values ),
        );
    }
}

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
    echo get_the_password_form(); // WPCS: XSS ok.
    return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>

    <div class="woocommerce-products-header ts-43 ts-sm-24">
        <?php
        $custom_title = get_field('custom-title');
        if( $custom_title ) : ?>
            <h1 class="woocommerce-products-header__title ts-43 ts-sm-24 ttu">
                <?php echo $custom_title; ?>
            </h1>
        <?php else : ?>
            <h1 class="woocommerce-products-header__title ts-43 ts-sm-24 ttu">
                <?php echo the_title(); ?>
            </h1>
        <?php endif;?>
    </div>

    <div class="row">
        <div class="col-12 col-md-6 col-xl-7 pos">

            <?php
            /**
             * Hook: woocommerce_before_single_product_summary.
             *
             * @hooked woocommerce_show_product_sale_flash - 10
             * @hooked woocommerce_show_product_images - 20
             */
            do_action( 'woocommerce_before_single_product_summary' );
            ?>
        </div>
        <div class="col-12 col-md-6 col-xl-5">
            <div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $summary_classes ) ) ); ?>">
                <div class="wrap">
                    <?php
                    /**
                     * Hook: woocommerce_single_product_summary.
                     *
                     * @hooked woocommerce_template_single_title - 5
                     * @hooked woocommerce_template_single_rating - 10
                     * @hooked woocommerce_template_single_price - 10
                     * @hooked woocommerce_template_single_excerpt - 20
                     * @hooked woocommerce_template_single_add_to_cart - 30
                     * @hooked woocommerce_template_single_meta - 40
                     * @hooked woocommerce_template_single_sharing - 50
                     * @hooked WC_Structured_Data::generate_product_data() - 60
                     */
                    do_action( 'woocommerce_single_product_summary' );
                    ?>
                </div>
                <?php if (get_field('active-link-size-list') || !empty( $product_attributes )) : ?>
                <div class="wrap-colapps colapps-1">
                    <div class="colapps-title js-colapps-title ts-16 ts-sm-12 ttu">
                        опис товару
                        <span class="colapps-plus"></span>
                    </div>
                    <div style="display: none" class="colapps-result">
                        <div class="colapps-box">
                            <?php if ( ! empty( $product_attributes ) ) : ?>
                            <?php foreach ( $product_attributes as $product_attribute ) : ?>
                                <div class="attr-item">
                                    <div class="row">
                                        <div class="col-5 col-sm-6">
                                            <strong class="t-green ts-16 ts-sm-12"><?php echo esc_html( $product_attribute['label'] ); ?></strong>
                                        </div>
                                        <div class="col-7 col-sm-6 ts-16 ts-sm-12">
                                            <?php echo esc_html( $product_attribute['value'] ); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if (get_field('active-link-size-list')) : ?>
                            <div class="wrap-link">
                                <?php
                                $url = get_field('prodiuct-link-size-list','option');
                                if( $url ) : ?>
                                <a data-fancybox href="<?php echo $url['url']; ?>">Таблиця розмірів</a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ( get_field('tabs-list-product') ) :  ?>
                    <?php  $i=0; while( have_rows('tabs-list-product') ) : the_row();  $i++; ?>
                        <div class="wrap-colapps colapps-global-<?php echo $i; ?>">
                            <div class="colapps-title js-colapps-title ts-16 ts-sm-12 ttu">
                                <?php the_sub_field('name'); ?>
                                <span class="colapps-plus"></span>
                            </div>
                            <div style="display: none" class="colapps-result">
                                <div class="colapps-box text-description">
                                    <?php the_sub_field('text'); ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else :  ?>
                    <?php  $i=0; while( have_rows('tabs-list-product','option') ) : the_row();  $i++; ?>
                        <div class="wrap-colapps colapps-global-<?php echo $i; ?>">
                            <div class="colapps-title js-colapps-title ts-16 ts-sm-12 ttu">
                                <?php the_sub_field('name'); ?>
                                <span class="colapps-plus"></span>
                            </div>
                            <div style="display: none" class="colapps-result">
                                <div class="colapps-box text-description">
                                    <?php the_sub_field('text'); ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif;  ?>

                <div class="wrap-end">
                    <?php if ( $product instanceof WC_Product && $product->is_type( 'simple' ) && ! $product->is_in_stock() ) : ?>
                        <div class="variation_select_info_stock">
                            Нема в наявності
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<?php do_action( 'woocommerce_after_single_product' ); ?>
