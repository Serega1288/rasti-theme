<?php
/**
 * Single Product Image
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-image.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.5.0
 */

use Automattic\WooCommerce\Enums\ProductType;

defined( 'ABSPATH' ) || exit;

// Note: `wc_get_gallery_image_html` was added in WC 3.3.2 and did not exist prior. This check protects against theme overrides being used on older versions of WC.
if ( ! function_exists( 'wc_get_gallery_image_html' ) ) {
    return;
}

global $product;

$columns             = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
$post_thumbnail_id   = $product->get_image_id();
$attachment_ids      = project_theme_get_product_gallery_attachment_ids( $product );
$gallery_slider_id   = 'product-gallery-' . $product->get_id();
$gallery_lightbox_id = 'product-gallery-lightbox-' . $product->get_id();
$wrapper_classes   = apply_filters(
    'woocommerce_single_product_image_gallery_classes',
    array(
        'woocommerce-product-gallery',
        'woocommerce-product-gallery--' . ( $post_thumbnail_id ? 'with-images' : 'without-images' ),
        'woocommerce-product-gallery--columns-' . absint( $columns ),
        'images',
    )
);

$gallery_items_html  = '';
$gallery_items_count = count( $attachment_ids );

if ( $gallery_items_count > 0 ) {
    $gallery_items_html = project_theme_render_product_gallery_items_html( $attachment_ids, $gallery_lightbox_id );
} else {
    // Keep the default placeholder markup so WooCommerce variation image swapping still targets the primary image node.
    $wrapper_classname = $product->is_type( ProductType::VARIABLE ) && ! empty( $product->get_visible_children() ) && '' !== $product->get_price() ?
        'woocommerce-product-gallery__image woocommerce-product-gallery__image--placeholder swiper-slide' :
        'woocommerce-product-gallery__image--placeholder swiper-slide';
    $gallery_items_html  = sprintf( '<div class="%s">', esc_attr( $wrapper_classname ) );
    $gallery_items_html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
    $gallery_items_html .= '</div>';
    $gallery_items_count = 1;
}
?>
<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>" data-columns="<?php echo esc_attr( $columns ); ?>" data-slide-count="<?php echo esc_attr( $gallery_items_count ); ?>" data-default-slide-count="<?php echo esc_attr( $gallery_items_count ); ?>" style="opacity: 0; transition: opacity .25s ease-in-out;">
    <div class="swiper js-product-gallery-slider" data-slider-id="<?php echo esc_attr( $gallery_slider_id ); ?>">
        <div class="woocommerce-product-gallery__wrapper swiper-wrapper">
            <?php echo $gallery_items_html; // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
    </div>
    <template class="js-default-product-gallery-template"><?php echo $gallery_items_html; // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped ?></template>

    <div class="slider-navigation" data-slider-nav="<?php echo esc_attr( $gallery_slider_id ); ?>"<?php echo $gallery_items_count > 1 ? '' : ' hidden'; ?>>
        <button class="slider-prev" type="button" data-slider-prev="<?php echo esc_attr( $gallery_slider_id ); ?>" aria-label="<?php esc_attr_e( 'Previous slide', 'project-theme' ); ?>">
            <span></span>
        </button>
        <div class="wrap-slider-dots">
            <div class="border border-1"></div>
            <div class="slider-dots" data-slider-pagination="<?php echo esc_attr( $gallery_slider_id ); ?>"></div>
            <div class="border border-2"></div>
        </div>
        <button class="slider-next" type="button" data-slider-next="<?php echo esc_attr( $gallery_slider_id ); ?>" aria-label="<?php esc_attr_e( 'Next slide', 'project-theme' ); ?>">
            <span></span>
        </button>
    </div>
</div>
