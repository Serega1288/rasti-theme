<?php

add_action( 'after_setup_theme', 'project_theme_setup_woocommerce_support' );
function project_theme_setup_woocommerce_support(): void {
    add_theme_support( 'woocommerce' );
    remove_theme_support( 'wc-product-gallery-zoom' );
    remove_theme_support( 'wc-product-gallery-lightbox' );
    remove_theme_support( 'wc-product-gallery-slider' );
}

add_filter( 'woocommerce_email_classes', 'project_theme_register_payment_confirmation_email' );
function project_theme_register_payment_confirmation_email( array $email_classes ): array {
    require_once get_stylesheet_directory() . '/inc/class-payment-confirmation-email.php';
    $email_classes['Project_Theme_Payment_Confirmation_Email'] = new Project_Theme_Payment_Confirmation_Email();
    return $email_classes;
}

function project_theme_is_in_cart( int $product_id, int $variation_id = 0 ): bool {
    if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
        return false;
    }
    foreach ( WC()->cart->get_cart() as $item ) {
        if ( $variation_id > 0 ) {
            if ( (int) $item['variation_id'] === $variation_id ) {
                return true;
            }
        } else {
            if ( (int) $item['product_id'] === $product_id && empty( $item['variation_id'] ) ) {
                return true;
            }
        }
    }
    return false;
}

add_filter( 'woocommerce_add_to_cart_validation', 'project_theme_block_duplicate_cart_item', 10, 4 );
function project_theme_block_duplicate_cart_item( bool $passed, int $product_id, int $quantity, int $variation_id = 0 ): bool {
    if ( project_theme_is_in_cart( $product_id, $variation_id ) ) {
        wc_add_notice( __( 'Цей товар вже є в кошику.', 'project-theme' ), 'error' );
        return false;
    }
    return $passed;
}

add_filter( 'woocommerce_enqueue_styles', '__return_false' );
add_filter( 'thwvsf_enqueue_public_scripts', '__return_true' );
add_filter( 'woocommerce_checkout_redirect_empty_cart', '__return_false' );
add_filter( 'woocommerce_add_error', 'project_theme_cleanup_checkout_error_labels' );

function project_theme_add_fancybox_to_product_gallery_item( string $html, string $gallery_id ): string {
    if ( '' === trim( $html ) || false === strpos( $html, '<a ' ) ) {
        return $html;
    }

    if ( false !== strpos( $html, 'data-fancybox=' ) && false !== strpos( $html, 'data-no-swup' ) ) {
        return $html;
    }

    return preg_replace(
        '/<a\s+/i',
        sprintf( '<a data-fancybox="%s" data-no-swup ', esc_attr( $gallery_id ) ),
        $html,
        1
    ) ?? $html;
}

function project_theme_cleanup_checkout_error_labels( string $error ): string {
    $error = str_replace(
        array(
            'Оплата ',
            'Оплата&nbsp;',
            '<strong>Оплата</strong> ',
        ),
        '',
        $error
    );

    return $error;
}

function project_theme_add_swiper_class_to_product_gallery_item( string $html ): string {
    if ( '' === trim( $html ) || false === strpos( $html, 'woocommerce-product-gallery__image' ) ) {
        return $html;
    }

    return preg_replace_callback(
        '/class=(["\'])([^"\']*woocommerce-product-gallery__image[^"\']*)\1/i',
        static function ( array $matches ): string {
            $quote = $matches[1];
            $classes = preg_split( '/\s+/', trim( $matches[2] ) ) ?: array();

            if ( ! in_array( 'swiper-slide', $classes, true ) ) {
                $classes[] = 'swiper-slide';
            }

            return 'class=' . $quote . esc_attr( implode( ' ', array_unique( $classes ) ) ) . $quote;
        },
        $html,
        1
    ) ?? $html;
}

function project_theme_normalize_attachment_ids( $raw_ids ): array {
    if ( empty( $raw_ids ) ) {
        return array();
    }

    if ( is_string( $raw_ids ) ) {
        $raw_ids = array_map( 'trim', explode( ',', $raw_ids ) );
    }

    if ( ! is_array( $raw_ids ) ) {
        $raw_ids = array( $raw_ids );
    }

    $attachment_ids = array();

    foreach ( $raw_ids as $raw_id ) {
        if ( is_numeric( $raw_id ) ) {
            $attachment_ids[] = (int) $raw_id;
            continue;
        }

        if ( is_array( $raw_id ) ) {
            if ( isset( $raw_id['ID'] ) && is_numeric( $raw_id['ID'] ) ) {
                $attachment_ids[] = (int) $raw_id['ID'];
                continue;
            }

            if ( isset( $raw_id['id'] ) && is_numeric( $raw_id['id'] ) ) {
                $attachment_ids[] = (int) $raw_id['id'];
            }
        }
    }

    return array_values(
        array_unique(
            array_filter(
                $attachment_ids,
                static fn( int $attachment_id ): bool => $attachment_id > 0
            )
        )
    );
}

function project_theme_get_product_gallery_attachment_ids( WC_Product $product ): array {
    $attachment_ids = array();
    $image_id       = $product->get_image_id();

    if ( $image_id ) {
        $attachment_ids[] = (int) $image_id;
    }

    $attachment_ids = array_merge(
        $attachment_ids,
        array_map( 'intval', $product->get_gallery_image_ids() )
    );

    return project_theme_normalize_attachment_ids( $attachment_ids );
}

function project_theme_get_variation_gallery_attachment_ids( WC_Product_Variation $variation ): array {
    $attachment_ids = array();
    $image_id       = $variation->get_image_id();

    if ( $image_id ) {
        $attachment_ids[] = (int) $image_id;
    }

    $gallery_ids = function_exists( 'get_field' ) ? get_field( 'variation_gallery', $variation->get_id(), false ) : array();

    if ( empty( $gallery_ids ) ) {
        $gallery_ids = get_post_meta( $variation->get_id(), 'variation_gallery', true );
    }

    if ( ! empty( $gallery_ids ) ) {
        $attachment_ids = array_merge( $attachment_ids, project_theme_normalize_attachment_ids( $gallery_ids ) );
    }

    return project_theme_normalize_attachment_ids( $attachment_ids );
}

function project_theme_render_product_gallery_items_html( array $attachment_ids, string $gallery_id ): string {
    if ( ! function_exists( 'wc_get_gallery_image_html' ) ) {
        return '';
    }

    $html = '';

    foreach ( $attachment_ids as $index => $attachment_id ) {
        $item_html = wc_get_gallery_image_html( $attachment_id, true, $index );
        $item_html = project_theme_add_fancybox_to_product_gallery_item( $item_html, $gallery_id );
        $item_html = project_theme_add_swiper_class_to_product_gallery_item( $item_html );
        $html     .= $item_html;
    }

    return $html;
}

add_filter( 'woocommerce_available_variation', 'project_theme_add_variation_gallery_data', 10, 3 );
function project_theme_add_variation_gallery_data( array $variation_data, WC_Product $product, WC_Product_Variation $variation ): array {
    $variation_attachment_ids = project_theme_get_variation_gallery_attachment_ids( $variation );
    $default_attachment_ids   = project_theme_get_product_gallery_attachment_ids( $product );
    $gallery_id               = 'product-gallery-lightbox-' . $product->get_id();
    $attachment_ids           = ! empty( $variation_attachment_ids ) ? $variation_attachment_ids : $default_attachment_ids;

    $variation_data['rasti_gallery_image_ids'] = $attachment_ids;
    $variation_data['rasti_gallery_html']      = project_theme_render_product_gallery_items_html( $attachment_ids, $gallery_id );
    $variation_data['rasti_has_gallery']       = ! empty( $variation_attachment_ids );
    $variation_data['rasti_uses_default_gallery'] = empty( $variation_attachment_ids );

    return $variation_data;
}

add_action( 'admin_enqueue_scripts', 'project_theme_enqueue_variation_gallery_admin_assets' );
function project_theme_enqueue_variation_gallery_admin_assets(): void {
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

    if ( ! $screen || 'product' !== $screen->id ) {
        return;
    }

    wp_enqueue_media();
}

add_action( 'woocommerce_variation_options_pricing', 'project_theme_render_variation_gallery_field', 5, 3 );
function project_theme_render_variation_gallery_field( int $loop, array $variation_data, WP_Post $variation_post ): void {
    $attachment_ids = project_theme_normalize_attachment_ids( get_post_meta( $variation_post->ID, 'variation_gallery', true ) );
    ?>
    <div class="form-row form-row-full project-theme-variation-gallery-field">
        <label><?php esc_html_e( 'Variation gallery', 'project-theme' ); ?></label>
        <input
            type="hidden"
            class="js-variation-gallery-input"
            name="variation_gallery[<?php echo esc_attr( $variation_post->ID ); ?>]"
            value="<?php echo esc_attr( implode( ',', $attachment_ids ) ); ?>"
        />
        <div class="project-theme-variation-gallery-preview js-variation-gallery-preview">
            <?php foreach ( $attachment_ids as $attachment_id ) : ?>
                <span class="project-theme-variation-gallery-thumb">
                    <?php echo wp_get_attachment_image( $attachment_id, array( 80, 80 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </span>
            <?php endforeach; ?>
        </div>
        <p class="project-theme-variation-gallery-actions">
            <button type="button" class="button js-variation-gallery-open">
                <?php esc_html_e( 'Select gallery images', 'project-theme' ); ?>
            </button>
            <button type="button" class="button-link-delete js-variation-gallery-clear"<?php echo empty( $attachment_ids ) ? ' hidden' : ''; ?>>
                <?php esc_html_e( 'Clear gallery', 'project-theme' ); ?>
            </button>
        </p>
    </div>
    <?php
}

add_action( 'woocommerce_save_product_variation', 'project_theme_save_variation_gallery_field', 10, 2 );
function project_theme_save_variation_gallery_field( int $variation_id, int $loop ): void {
    $raw_value = $_POST['variation_gallery'][ $variation_id ] ?? ( $_POST['variation_gallery'][ $loop ] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $raw_value = is_string( $raw_value ) ? wp_unslash( $raw_value ) : '';
    $raw_ids   = '' === $raw_value ? array() : array_map( 'trim', explode( ',', $raw_value ) );
    $meta_value = project_theme_normalize_attachment_ids( $raw_ids );

    if ( empty( $meta_value ) ) {
        delete_post_meta( $variation_id, 'variation_gallery' );
        return;
    }

    update_post_meta( $variation_id, 'variation_gallery', implode( ',', $meta_value ) );
}

add_action( 'admin_footer', 'project_theme_render_variation_gallery_admin_script' );
function project_theme_render_variation_gallery_admin_script(): void {
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

    if ( ! $screen || 'product' !== $screen->id ) {
        return;
    }
    ?>
    <script>
        jQuery(function ($) {
            const placeVariationGalleryFields = () => {
                $('.project-theme-variation-gallery-field').each(function () {
                    const $field = $(this);
                    const $variation = $field.closest('.woocommerce_variation');

                    if (!$variation.length) {
                        return;
                    }

                    const $imageAnchor = $variation.find('.upload_image').first().closest('p, div');
                    const $target = $imageAnchor.length
                        ? $imageAnchor
                        : $variation.find('.variable_pricing').first();

                    if (!$target.length) {
                        return;
                    }

                    if ($target.next('.project-theme-variation-gallery-field').get(0) === $field.get(0)) {
                        return;
                    }

                    $field.detach().insertAfter($target);
                });
            };

            const renderPreview = ($field, attachmentIds) => {
                const $preview = $field.find('.js-variation-gallery-preview');
                const $clearButton = $field.find('.js-variation-gallery-clear');

                $preview.empty();

                attachmentIds.forEach((attachmentId) => {
                    const imageUrl = wp?.media?.attachment(attachmentId)?.get('sizes')?.thumbnail?.url
                        || wp?.media?.attachment(attachmentId)?.get('url')
                        || '';

                    if (!imageUrl) {
                        return;
                    }

                    $preview.append(
                        $('<span />', { class: 'project-theme-variation-gallery-thumb' }).append(
                            $('<img />', { src: imageUrl, alt: '', loading: 'lazy' })
                        )
                    );
                });

                $clearButton.prop('hidden', attachmentIds.length === 0);
            };

            const markVariationAsChanged = ($field) => {
                const $variation = $field.closest('.woocommerce_variation');

                $field.find('.js-variation-gallery-input').trigger('input').trigger('change');
                $variation.addClass('variation-needs-update');
                $('button.cancel-variation-changes, button.save-variation-changes').prop('disabled', false);
                $('#variable_product_options').trigger('woocommerce_variations_input_changed');
            };

            const parseIds = (value) => value
                .split(',')
                .map((id) => Number.parseInt(id, 10))
                .filter((id) => Number.isInteger(id) && id > 0);

            $(document).on('click', '.js-variation-gallery-open', function (event) {
                event.preventDefault();

                const $field = $(this).closest('.project-theme-variation-gallery-field');
                const $input = $field.find('.js-variation-gallery-input');
                const currentIds = parseIds($input.val() || '');

                const frame = wp.media({
                    title: 'Variation gallery',
                    button: {
                        text: 'Use these images'
                    },
                    library: {
                        type: 'image'
                    },
                    multiple: true
                });

                frame.on('open', () => {
                    const selection = frame.state().get('selection');

                    currentIds.forEach((attachmentId) => {
                        const attachment = wp.media.attachment(attachmentId);
                        attachment.fetch();
                        selection.add(attachment ? [attachment] : []);
                    });
                });

                frame.on('select', () => {
                    const attachmentIds = frame.state().get('selection').map((attachment) => attachment.get('id'));
                    $input.val(attachmentIds.join(','));
                    renderPreview($field, attachmentIds);
                    markVariationAsChanged($field);
                });

                frame.open();
            });

            $(document).on('click', '.js-variation-gallery-clear', function (event) {
                event.preventDefault();

                const $field = $(this).closest('.project-theme-variation-gallery-field');
                $field.find('.js-variation-gallery-input').val('');
                renderPreview($field, []);
                markVariationAsChanged($field);
            });

            $(document).on('woocommerce_variations_loaded', () => {
                placeVariationGalleryFields();
            });

            $(document).ajaxComplete(() => {
                placeVariationGalleryFields();
            });

            placeVariationGalleryFields();
        });
    </script>
    <style>
        .project-theme-variation-gallery-field {
            max-width: 180px;
            clear: both;
            margin-top: 12px !important;
        }

        .project-theme-variation-gallery-field label {
            display: block;
            margin-bottom: 6px;
        }

        .project-theme-variation-gallery-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 8px 0 10px;
        }

        .project-theme-variation-gallery-thumb {
            width: 56px;
            height: 56px;
            border: 1px solid #dcdcde;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .project-theme-variation-gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .project-theme-variation-gallery-actions {
            display: flex;
            align-items: flex-start;
            flex-direction: column;
            gap: 12px;
            margin: 0;
        }
    </style>
    <?php
}

function project_theme_render_header_cart_count(): void {
    $cart_count = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    $cart_url   = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' );
    $cart_label = $cart_count > 0 ? sprintf( '%02d', $cart_count ) : '';
    ?>
    <li class="cart-count">
        <a count="<?php echo $cart_count; ?>" class="brackets" href="<?php echo esc_url( $cart_url ); ?>">
            Cart
            <?php if ( $cart_count > 0 ) : ?>
                <span><?php echo esc_html( $cart_label ); ?></span>
            <?php endif; ?>
        </a>
    </li>
    <?php
}

add_filter( 'woocommerce_add_to_cart_fragments', 'project_theme_header_cart_count_fragment' );
function project_theme_header_cart_count_fragment( array $fragments ): array {
    ob_start();
    project_theme_render_header_cart_count();
    $fragments['li.cart-count'] = ob_get_clean();

    $fragments['script#rasti-cart-ids'] = project_theme_render_cart_ids_script();

    return $fragments;
}

function project_theme_get_cart_ids(): array {
    $product_ids   = [];
    $variation_ids = [];
    if ( function_exists( 'WC' ) && WC()->cart ) {
        foreach ( WC()->cart->get_cart() as $item ) {
            $product_ids[] = (int) $item['product_id'];
            if ( ! empty( $item['variation_id'] ) ) {
                $variation_ids[] = (int) $item['variation_id'];
            }
        }
    }
    return [
        'productIds'   => array_values( array_unique( $product_ids ) ),
        'variationIds' => array_values( array_unique( $variation_ids ) ),
    ];
}

function project_theme_render_cart_ids_script(): string {
    return '<script id="rasti-cart-ids" type="application/json">'
        . wp_json_encode( project_theme_get_cart_ids() )
        . '</script>';
}

add_action( 'wp_footer', function (): void {
    echo project_theme_render_cart_ids_script(); // phpcs:ignore WordPress.Security.EscapeOutput
} );

add_filter( 'woocommerce_currency_symbol', 'project_theme_currency_symbol', 10, 2 );
function project_theme_currency_symbol( string $currency_symbol, string $currency ): string {
    if ( 'UAH' === $currency ) {
        return 'грн';
    }

    return $currency_symbol;
}

add_action( 'template_redirect', 'project_theme_handle_clear_cart_request' );
function project_theme_handle_clear_cart_request(): void {
    if ( ! isset( $_GET['clear-cart'] ) || '1' !== wp_unslash( $_GET['clear-cart'] ) ) {
        return;
    }

    if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
        return;
    }

    WC()->cart->empty_cart();

    wp_safe_redirect( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ) );
    exit;
}


add_action( 'wp_ajax_project_theme_get_cart_ids', 'project_theme_ajax_get_cart_ids' );
add_action( 'wp_ajax_nopriv_project_theme_get_cart_ids', 'project_theme_ajax_get_cart_ids' );
function project_theme_ajax_get_cart_ids(): void {
    check_ajax_referer( 'rasti-theme-ajax', 'nonce' );
    wp_send_json_success( project_theme_get_cart_ids() );
}

add_action( 'wp_ajax_project_theme_get_cart_count', 'project_theme_ajax_get_cart_count' );
add_action( 'wp_ajax_nopriv_project_theme_get_cart_count', 'project_theme_ajax_get_cart_count' );
function project_theme_ajax_get_cart_count(): void {
    check_ajax_referer( 'rasti-theme-ajax', 'nonce' );

    wp_send_json_success(
        array(
            'cartCountHtml' => project_theme_get_cart_count_fragment_html(),
        )
    );
}

function project_theme_render_checkout_cart_items_html(): string {
    ob_start();
    wc_get_template( 'cart/checkout-cart-items.php' );

    return (string) ob_get_clean();
}

function project_theme_get_cart_count_fragment_html(): string {
    ob_start();
    project_theme_render_header_cart_count();

    return (string) ob_get_clean();
}

function project_theme_render_checkout_order_review_html(): string {
    ob_start();
    do_action( 'woocommerce_checkout_order_review' );

    return (string) ob_get_clean();
}

add_action( 'wp_ajax_project_theme_update_checkout_cart_item', 'project_theme_ajax_update_checkout_cart_item' );
add_action( 'wp_ajax_nopriv_project_theme_update_checkout_cart_item', 'project_theme_ajax_update_checkout_cart_item' );
function project_theme_ajax_update_checkout_cart_item(): void {
    check_ajax_referer( 'rasti-theme-ajax', 'nonce' );

    if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
        wp_send_json_error();
    }

    $cart_item_key = isset( $_POST['cart_item_key'] ) ? wc_clean( wp_unslash( $_POST['cart_item_key'] ) ) : '';
    $quantity      = isset( $_POST['quantity'] ) ? (int) wp_unslash( $_POST['quantity'] ) : 0;

    if ( '' === $cart_item_key ) {
        wp_send_json_error();
    }

    WC()->cart->set_quantity( $cart_item_key, $quantity, true );
    WC()->cart->calculate_totals();

    wp_send_json_success(
        array(
            'isCartEmpty'           => WC()->cart->is_empty(),
            'cartUrl'               => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ),
            'checkoutCartItemsHtml' => project_theme_render_checkout_cart_items_html(),
            'checkoutOrderReviewHtml' => project_theme_render_checkout_order_review_html(),
            'cartCountHtml'         => project_theme_get_cart_count_fragment_html(),
        )
    );
}

add_action( 'wp_ajax_project_theme_remove_checkout_cart_item', 'project_theme_ajax_remove_checkout_cart_item' );
add_action( 'wp_ajax_nopriv_project_theme_remove_checkout_cart_item', 'project_theme_ajax_remove_checkout_cart_item' );
function project_theme_ajax_remove_checkout_cart_item(): void {
    check_ajax_referer( 'rasti-theme-ajax', 'nonce' );

    if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
        wp_send_json_error();
    }

    $cart_item_key = isset( $_POST['cart_item_key'] ) ? wc_clean( wp_unslash( $_POST['cart_item_key'] ) ) : '';

    if ( '' === $cart_item_key ) {
        wp_send_json_error();
    }

    WC()->cart->remove_cart_item( $cart_item_key );
    WC()->cart->calculate_totals();

    wp_send_json_success(
        array(
            'isCartEmpty'           => WC()->cart->is_empty(),
            'cartUrl'               => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ),
            'checkoutCartItemsHtml' => project_theme_render_checkout_cart_items_html(),
            'checkoutOrderReviewHtml' => project_theme_render_checkout_order_review_html(),
            'cartCountHtml'         => project_theme_get_cart_count_fragment_html(),
        )
    );
}



//product cart

remove_action('woocommerce_shop_loop_item_title','woocommerce_template_loop_product_title', 10);
remove_action('woocommerce_after_shop_loop_item_title','woocommerce_template_loop_price', 10);
add_action('woocommerce_after_shop_loop_item_title', function () {
    ?>
    <div class="row wrap-product-title">
        <div class="col">
            <div class="product-title ts-14 ts-sm-12 ttu">
                <?php echo get_the_title(); ?>
            </div>
        </div>
        <div class="col-auto">
            <div class="product-price ts-14 ts-sm-12 brackets">
                <?php woocommerce_template_loop_price(); ?>
            </div>
        </div>
    </div>
<?php
}, 10);

add_action('woocommerce_before_shop_loop_item_title', function () {
    ?>
    <div class="wrap-img">
    <div class="img">
<?php
}, 9);
add_action('woocommerce_before_shop_loop_item_title', function () {
    ?>
    </div><!-- .img -->
    </div><!-- .wrap-img -->
    <?php
}, 11);

//змінюю кнопку на кастумну
remove_action('woocommerce_after_shop_loop_item','woocommerce_template_loop_add_to_cart', 10);
add_action('woocommerce_after_shop_loop_item',function () {
    ?>
    <div class="wrap-btn d-flex align-items-center justify-content-center">
        <a href="<?php echo get_the_permalink(); ?>" class="btn buy">
            <span class="border border-1"></span>
            <span class="border border-2"></span>
            <span class="plus"></span>
        </a>
    </div>
    <?php
}, 10);

add_action( 'woocommerce_before_shop_loop_item_title', 'project_theme_enable_lazy_loop_img', 9 );
add_action( 'woocommerce_before_shop_loop_item_title', 'project_theme_disable_lazy_loop_img', 11 );

function project_theme_enable_lazy_loop_img(): void {
    add_filter( 'wp_get_attachment_image_attributes', 'project_theme_lazy_loop_img_attrs', 20 );
}

function project_theme_disable_lazy_loop_img(): void {
    remove_filter( 'wp_get_attachment_image_attributes', 'project_theme_lazy_loop_img_attrs', 20 );
}

function project_theme_lazy_loop_img_attrs( array $attr ): array {
    if ( empty( $attr['src'] ) || 0 !== strpos( $attr['src'], 'http' ) ) {
        return $attr;
    }

    $attr['data-src'] = $attr['src'];
    $attr['src']      = 'data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%201%201%22%3E%3C%2Fsvg%3E';
    $attr['class']    = trim( ( $attr['class'] ?? '' ) . ' lazy-img' );
    unset( $attr['loading'], $attr['fetchpriority'] );

    return $attr;
}

//каталог
remove_action('woocommerce_sidebar','woocommerce_get_sidebar', 10);
remove_action('woocommerce_before_shop_loop','woocommerce_catalog_ordering', 30);
remove_action('woocommerce_before_main_content','woocommerce_breadcrumb', 20);



//картка товару
remove_action('woocommerce_sidebar','woocommerce_get_sidebar', 10);
//remove_action('woocommerce_after_single_product_summary','woocommerce_output_related_products', 20);
//remove_action('woocommerce_after_single_product_summary','woocommerce_upsell_display', 15);
//remove_action('woocommerce_after_single_product_summary','woocommerce_output_product_data_tabs', 10);

remove_action('woocommerce_single_product_summary','woocommerce_template_single_rating', 10);
remove_action('woocommerce_single_product_summary','woocommerce_template_single_title', 5);
remove_action('woocommerce_single_product_summary','woocommerce_template_single_excerpt', 20);


add_action('woocommerce_single_product_summary', function () {
    ?>
    <div class="row">
    <div class="col">
        <div class="product-title ts-20 ts-sm-14 ttu">
            <?php the_title(); ?>
        </div>
    </div><!-- .col -->
    <div class="col-auto">
        <div class="wrap-price">
<?php
}, 0);


add_action('woocommerce_single_product_summary', function () {
    ?>
            </div><!-- .wrap-price -->
        </div><!-- .col -->
    </div><!-- .row -->
    <?php
}, 15);

add_action( 'woocommerce_single_product_summary', 'project_theme_render_single_product_excerpt', 20 );
function project_theme_render_single_product_excerpt(): void {
    global $post;

    if ( ! $post instanceof WP_Post ) {
        return;
    }

    $excerpt = has_excerpt( $post ) ? $post->post_excerpt : '';

    if ( '' === trim( $excerpt ) ) {
        return;
    }

    $excerpt_text = trim( wp_strip_all_tags( $excerpt ) );
    $preview_text = function_exists( 'mb_substr' ) ? mb_substr( $excerpt_text, 0, 198 ) : substr( $excerpt_text, 0, 198 );
    $is_truncated = $preview_text !== $excerpt_text;

    if ( $is_truncated ) {
        $preview_text = rtrim( $preview_text );
    }
    ?>
    <div
        class="woocommerce-product-details__short-description js-read-more-excerpt<?php echo $is_truncated ? ' is-collapsed' : ''; ?>"
        <?php if ( $is_truncated ) : ?>
            data-read-more-excerpt
            role="button"
            tabindex="0"
            aria-expanded="false"
        <?php endif; ?>
    >
        <p>
            <span class="js-read-more-preview"<?php echo $is_truncated ? '' : ' hidden'; ?>>
                <?php echo esc_html( $preview_text ); ?>
                <?php if ( $is_truncated ) : ?>
                    <span class="excerpt-more-dots">...</span>
                <?php endif; ?>
            </span>
            <span class="js-read-more-full"<?php echo $is_truncated ? ' hidden' : ''; ?>>
                <?php echo wp_kses_post( $excerpt ); ?>
            </span>
        </p>
    </div>
    <?php
}



//чекаут checkout

/**
 * === Основна логіка зміни полів Checkout ===
 */
add_filter('woocommerce_checkout_fields', 'custom_override_checkout_fields');
function custom_override_checkout_fields($fields)
{

    $fields['billing']['billing_first_name']['label'] = '';
    $fields['billing']['billing_first_name']['placeholder'] = 'Ім’я *';
    $fields['billing']['billing_first_name']['priority'] = 1;
    $fields['billing']['billing_first_name']['class'][] = 'col-12 col-sm-6';

    $fields['billing']['billing_last_name']['label'] = '';
    $fields['billing']['billing_last_name']['placeholder'] = 'Прізвище *';
    $fields['billing']['billing_last_name']['priority'] = 2;
    $fields['billing']['billing_last_name']['class'][] = 'col-12 col-sm-6';

    $fields['billing']['billing_phone']['label'] = '';
    $fields['billing']['billing_phone']['placeholder'] = 'Номер телефону *';
    $fields['billing']['billing_phone']['priority'] = 3;
//    $fields['billing']['billing_phone']['required'] = true;
    $fields['billing']['billing_phone']['class'][] = 'col-12 col-sm-6';

    $fields['billing']['billing_email']['label'] = '';
    $fields['billing']['billing_email']['placeholder'] = 'Email *';
    $fields['billing']['billing_email']['priority'] = 4;
    $fields['billing']['billing_email']['class'][] = 'col-12 col-sm-6';

    $fields['billing']['billing_country']['label'] = '';
    $fields['billing']['billing_country']['placeholder'] = 'Країна / Регіон';
    $fields['billing']['billing_country']['priority'] = 5;
    $fields['billing']['billing_country']['class'][] = 'col-12';




    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_postcode']);


    return $fields;
}


/**
 * === WooCommerce Default Address Fields ===
 * (щоб також прибрати "(optional)" із city/postcode/address_1)
 */
add_filter('woocommerce_default_address_fields', function ($fields) {

//    if (isset($fields['first_name'])) {
//        $fields['first_name']['class'][] = 'col-12 col-sm-6';
//        $fields['first_name']['placeholder'] = __('Unesite svoje ime','themehortiqa');
//    }

    // ====== Повторюємо Bootstrap-логіку з checkout ======

    unset($fields['address_1']);
    unset($fields['city']);
    unset($fields['postcode']); 
    unset($fields['address_2']);
    unset($fields['state']);

    return $fields;
}, 10, 1);
