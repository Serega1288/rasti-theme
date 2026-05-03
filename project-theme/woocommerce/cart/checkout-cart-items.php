<?php
/**
 * Checkout cart items list.
 *
 * Uses checkout-specific markup with cart item controls.
 *
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="checkout-order-products">
    <form class="woocommerce-cart-form checkout-cart-items-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
        <table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
            <tbody>
            <?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) : ?>
                <?php
                $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                    continue;
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
                    $select_max   = $max_quantity > 0 ? $max_quantity : 100;
                    $current_qty  = (int) $cart_item['quantity'];
                    $qty_steps    = array_merge(
                        $select_min === 0 ? [ 0 ] : [],
                        range( max( 1, $select_min ), min( 20, $select_max ) ),
                        array_filter( [ 30, 40, 50, 60, 70, 80, 90, 100 ], fn( $v ) => $v >= $select_min && $v <= $select_max )
                    );
                    $options_html = '';

                    foreach ( $qty_steps as $quantity ) {
                        $options_html .= sprintf(
                            '<option value="%1$d" %2$s>%1$d</option>',
                            $quantity,
                            selected( $current_qty, $quantity, false )
                        );
                    }

                    $product_quantity = sprintf(
                        '<label class="screen-reader-text" for="checkout-cart-qty-%1$s">%2$s</label><select id="checkout-cart-qty-%1$s" name="cart[%1$s][qty]" class="cart-quantity-select" aria-label="%2$s">%3$s</select>',
                        esc_attr( $cart_item_key ),
                        esc_attr__( 'Quantity', 'woocommerce' ),
                        $options_html
                    );
                }
                ?>
                <tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
                    <td data-id="<?php echo $product_id; ?>" class="product-thumbnail">
                        <?php if ( ! $product_permalink ) : ?>
                            <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php else : ?>
                            <a href="<?php echo esc_url( $product_permalink ); ?>">
                                <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </a>
                        <?php endif; ?>
                    </td>

                    <td scope="row" role="rowheader" class="product-name ttu" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
                        <?php if ( ! $product_permalink ) : ?>
                            <?php echo wp_kses_post( $product_name . '&nbsp;' ); ?>
                        <?php else : ?>
                            <?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $product_display_name ), $cart_item, $cart_item_key ) ); ?>
                        <?php endif; ?>

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

                        <div class="product-remove">
                            <?php
                            echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                'woocommerce_cart_item_remove_link',
                                sprintf(
                                    '<a role="button" href="#" class="remove remove-text" data-no-swup data-cart-item-key="%s" aria-label="%s" data-product_id="%s" data-product_sku="%s">видалити</a>',
                                    esc_attr( $cart_item_key ),
                                    esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
                                    esc_attr( $product_id ),
                                    esc_attr( $_product->get_sku() )
                                ),
                                $cart_item_key
                            );
                            ?>
                        </div>
                    </td>

                    <td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
                        <?php echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <button type="submit" class="button" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>" hidden>
            <?php esc_html_e( 'Update cart', 'woocommerce' ); ?>
        </button>
        <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
    </form>

    <div class="wrap-sum">
        <div class="row">
            <div class="col">
                СУМА ЗАМОВЛЕННЯ
            </div>
            <div class="col-auto">
                <?php wc_cart_totals_subtotal_html(); ?>
            </div>
        </div>
    </div>
</div>
