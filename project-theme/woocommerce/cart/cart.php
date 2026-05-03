<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.1.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' ); ?>

<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
    <?php do_action( 'woocommerce_before_cart_table' ); ?>

    <table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
<!--        <thead>-->
<!--        <tr>-->
<!--            <th class="product-remove"><span class="screen-reader-text">--><?php //esc_html_e( 'Remove item', 'woocommerce' ); ?><!--</span></th>-->
<!--            <th class="product-thumbnail"><span class="screen-reader-text">--><?php //esc_html_e( 'Thumbnail image', 'woocommerce' ); ?><!--</span></th>-->
<!--            <th scope="col" class="product-name">--><?php //esc_html_e( 'Product', 'woocommerce' ); ?><!--</th>-->
<!--            <th scope="col" class="product-price">--><?php //esc_html_e( 'Price', 'woocommerce' ); ?><!--</th>-->
<!--            <th scope="col" class="product-quantity">--><?php //esc_html_e( 'Quantity', 'woocommerce' ); ?><!--</th>-->
<!--            <th scope="col" class="product-subtotal">--><?php //esc_html_e( 'Subtotal', 'woocommerce' ); ?><!--</th>-->
<!--        </tr>-->
<!--        </thead>-->
        <tbody>
        <?php do_action( 'woocommerce_before_cart_contents' ); ?>

        <?php
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
            $product_display_name = $_product && $_product->is_type( 'variation' )
                ? get_the_title( $product_id )
                : $_product->get_name();
            /**
             * Filter the product name.
             *
             * @since 2.1.0
             * @param string $product_name Name of the product in the cart.
             * @param array $cart_item The product in the cart.
             * @param string $cart_item_key Key for the product in the cart.
             */
            $product_name = apply_filters( 'woocommerce_cart_item_name', $product_display_name, $cart_item, $cart_item_key );

            if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                $variation_parts  = array();

                if ( ! empty( $cart_item['variation'] ) && is_array( $cart_item['variation'] ) ) {
                    foreach ( $cart_item['variation'] as $attribute_name => $attribute_value ) {
                        if ( '' === $attribute_value ) {
                            continue;
                        }

                        $taxonomy = str_replace( 'attribute_', '', $attribute_name );

                        if ( taxonomy_exists( $taxonomy ) ) {
                            $term = get_term_by( 'slug', $attribute_value, $taxonomy );
                            $value = $term && ! is_wp_error( $term ) ? $term->name : $attribute_value;
                        } else {
                            $value = $attribute_value;
                        }

                        $variation_parts[] = '<span>' . esc_html( $value ) . '</span>';
                    }
                }

                $variation_text = implode( ' | ', $variation_parts );
                ?>
                <tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

                    <td data-id="<?php echo $product_id; ?>" class="product-thumbnail">
                        <?php
                        /**
                         * Filter the product thumbnail displayed in the WooCommerce cart.
                         *
                         * This filter allows developers to customize the HTML output of the product
                         * thumbnail. It passes the product image along with cart item data
                         * for potential modifications before being displayed in the cart.
                         *
                         * @param string $thumbnail     The HTML for the product image.
                         * @param array  $cart_item     The cart item data.
                         * @param string $cart_item_key Unique key for the cart item.
                         *
                         * @since 2.1.0
                         */
                        $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

                        if ( ! $product_permalink ) {
                            echo $thumbnail; // PHPCS: XSS ok.
                        } else {
                            printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // PHPCS: XSS ok.
                        }
                        ?>
                    </td>

                    <td scope="row" role="rowheader" class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
                        <?php
                        if ( ! $product_permalink ) {
                            echo wp_kses_post( $product_name . '&nbsp;' );
                        } else {
                            /**
                             * This filter is documented above.
                             *
                             * @since 2.1.0
                             */
                            echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $product_display_name ), $cart_item, $cart_item_key ) );
                        }

                        do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

                        // Backorder notification.
                        if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
                            echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
                        }
                        ?>
                        <div class="price">
                            <?php
                            echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
                            ?>
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
                                            '<a role="button" href="%s" class="remove remove-text" data-no-swup aria-label="%s" data-product_id="%s" data-product_sku="%s">видалити</a>',
                                            esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                            /* translators: %s is the product name */
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
                        <?php
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
                                '<label class="screen-reader-text" for="cart-qty-%1$s">%2$s</label><select id="cart-qty-%1$s" name="cart[%1$s][qty]" class="cart-quantity-select" aria-label="%2$s">%3$s</select>',
                                esc_attr( $cart_item_key ),
                                esc_attr__( 'Quantity', 'woocommerce' ),
                                $options_html
                            );
                        }

                        echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.
                        ?>
                    </td>

<!--                    <td class="product-subtotal" data-title="--><?php //esc_attr_e( 'Subtotal', 'woocommerce' ); ?><!--">-->
<!--                        --><?php
//                        echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
//                        ?>
<!--                    </td>-->
                </tr>
                <?php
            }
        }
        ?>

        <?php do_action( 'woocommerce_cart_contents' ); ?>

        <tr>
            <td colspan="6" class="actions">

                <?php if ( wc_coupons_enabled() ) { ?>
                    <div class="coupon">
                        <label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label> <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" /> <button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
                        <?php do_action( 'woocommerce_cart_coupon' ); ?>
                    </div>
                <?php } ?>

                <button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>

                <?php do_action( 'woocommerce_cart_actions' ); ?>

                <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
            </td>
        </tr>

        <?php do_action( 'woocommerce_after_cart_contents' ); ?>
        </tbody>
    </table>
    <?php do_action( 'woocommerce_after_cart_table' ); ?>
</form>

<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

<div class="cart-collaterals">
    <?php
    /**
     * Cart collaterals hook.
     *
     * @hooked woocommerce_cross_sell_display
     * @hooked woocommerce_cart_totals - 10
     */
    do_action( 'woocommerce_cart_collaterals' );
    ?>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
