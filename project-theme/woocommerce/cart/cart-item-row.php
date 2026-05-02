<?php
/**
 * Shared cart item row.
 *
 * Expected args:
 * - cart_item_key
 * - cart_item
 */

defined( 'ABSPATH' ) || exit;

$cart_item_key = $args['cart_item_key'] ?? '';
$cart_item     = $args['cart_item'] ?? array();

if ( empty( $cart_item_key ) || empty( $cart_item ) || empty( $cart_item['data'] ) ) {
    return;
}

$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
    return;
}

$product_display_name = $_product->is_type( 'variation' ) ? get_the_title( $product_id ) : $_product->get_name();
$product_name         = apply_filters( 'woocommerce_cart_item_name', $product_display_name, $cart_item, $cart_item_key );
$product_permalink    = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
$variation_parts      = array();

if ( ! empty( $cart_item['variation'] ) && is_array( $cart_item['variation'] ) ) {
    foreach ( $cart_item['variation'] as $attribute_name => $attribute_value ) {
        if ( '' === $attribute_value ) {
            continue;
        }

        $taxonomy = str_replace( 'attribute_', '', $attribute_name );

        if ( taxonomy_exists( $taxonomy ) ) {
            $term  = get_term_by( 'slug', $attribute_value, $taxonomy );
            $value = $term && ! is_wp_error( $term ) ? $term->name : $attribute_value;
        } else {
            $value = $attribute_value;
        }

        $variation_parts[] = '<span>' . esc_html( $value ) . '</span>';
    }
}

$variation_text = implode( ' | ', $variation_parts );
$thumbnail      = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

if ( $_product->is_sold_individually() ) {
    $product_quantity = sprintf(
        '<input type="hidden" name="cart[%1$s][qty]" value="1" />1',
        esc_attr( $cart_item_key )
    );
} else {
    $min_quantity = 0;
    $max_quantity = $_product->get_max_purchase_quantity();
    $select_min   = max( 0, $min_quantity );
    $select_max   = min( 10, $max_quantity > 0 ? $max_quantity : 10 );
    $current_qty  = max( $select_min, min( (int) $cart_item['quantity'], $select_max ) );
    $options_html = '';

    for ( $quantity = $select_min; $quantity <= $select_max; $quantity++ ) {
        $options_html .= sprintf(
            '<option value="%1$d" %2$s>%1$d</option>',
            $quantity,
            selected( $current_qty, $quantity, false )
        );
    }

    $product_quantity = sprintf(
        '<label class="screen-reader-text" for="cart-qty-%1$s">%2$s</label><select id="cart-qty-%1$s" name="cart[%1$s][qty]" class="cart-quantity-select" aria-label="%2$s">%3$s</select>',
        esc_attr( $cart_item_key ),
        esc_attr__( 'Quantity', 'woocommerce' ),
        $options_html
    );
}
?>
<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
    <td class="product-remove">
        <?php
        echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            'woocommerce_cart_item_remove_link',
            sprintf(
                '<a role="button" href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
                esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
                esc_attr( $product_id ),
                esc_attr( $_product->get_sku() )
            ),
            $cart_item_key
        );
        ?>
    </td>

    <td class="product-thumbnail">
        <?php if ( ! $product_permalink ) : ?>
            <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <?php else : ?>
            <a href="<?php echo esc_url( $product_permalink ); ?>">
                <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </a>
        <?php endif; ?>
    </td>

    <td scope="row" role="rowheader" class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
        <?php if ( ! $product_permalink ) : ?>
            <?php echo wp_kses_post( $product_name . '&nbsp;' ); ?>
        <?php else : ?>
            <?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $product_display_name ), $cart_item, $cart_item_key ) ); ?>
        <?php endif; ?>

        <?php do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key ); ?>

        <?php if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) : ?>
            <?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) ); ?>
        <?php endif; ?>

        <div class="price">
            <?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php if ( '' !== $variation_text ) : ?>
                <span class="product-variation-meta">
                    | <?php echo wp_kses_post( $variation_text ); ?>
                </span>
            <?php endif; ?>
        </div>
    </td>

    <td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
        <?php echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </td>
</tr>
