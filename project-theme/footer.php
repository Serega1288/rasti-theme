<?php
/**
 * Theme footer.
 *
 * @package ProjectTheme
 */
?>
    <footer class="footer">
        <div class="footer-fon">
            <picture>
                <source media="(max-width: 575px)" data-srcset="<?php echo get_template_directory_uri(); ?>/assets/img/footer-fon-mobile.png">
                <img
                        class="lazy-img"
                        src="data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%201%201%22%3E%3C%2Fsvg%3E"
                        data-src="<?php echo get_template_directory_uri(); ?>/assets/img/footer-fon.png" alt="">
            </picture>
        </div>
        <div class="container-fluid">
            <div class="box">
                <div class="wrap-logo d-flex justify-content-center">
                    <svg width="543" height="88" viewBox="0 0 543 88" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M543 2.0459V85.8409H510.346V2.0459H543Z" fill="#BDFF7B"/>
                        <path d="M80.6887 2.12164C97.9629 1.97012 112.585 16.441 112.358 33.791C112.358 40.0794 110.691 45.8375 107.282 50.9137C103.948 55.9899 99.554 59.8539 94.099 62.3541L112.358 85.8409H77.5824L61.8235 65.3846H32.6543V85.8409H0V2.0459H80.6887V2.12164ZM32.5785 26.5934V40.8371H72.4304C74.4003 40.8371 76.1429 40.1552 77.5066 38.7915C78.9462 37.352 79.628 35.6851 79.628 33.7153C79.628 29.6998 76.4459 26.5177 72.4304 26.5177H32.5785V26.5934Z" fill="#BDFF7B"/>
                        <path d="M167.742 2.12109H206.609L251.537 85.9162H214.791L208.2 71.5967H166.227L159.635 85.9162H122.89L167.818 2.12109H167.742ZM175.621 51.1404H198.654L187.213 26.3656L175.697 51.1404H175.621Z" fill="#BDFF7B"/>
                        <path d="M313.891 34.4726C337.226 35.6849 370.108 37.2759 370.032 58.2626C370.032 82.204 342.605 87.8863 313.209 87.8863C283.888 87.8863 259.492 84.2496 256.386 58.2626H290.631C294.344 64.7783 303.056 66.4451 313.285 66.4451C323.513 66.4451 335.938 64.7783 335.938 58.2626C335.938 51.7469 325.786 54.0198 312.603 53.4137C289.192 52.2014 256.386 50.6104 256.462 29.6995C256.462 5.75808 283.888 0 313.285 0C342.605 0.151528 367.001 3.48516 370.108 29.6995H335.862C332.15 23.108 323.437 21.517 313.209 21.517C302.981 21.517 290.555 23.0323 290.555 29.6995C290.555 36.3668 300.708 33.8665 313.891 34.4726Z" fill="#BDFF7B"/>
                        <path d="M422.234 85.9165V28.7147H383.367V2.19727H493.68V28.7147H454.813V85.9165H422.158H422.234Z" fill="#BDFF7B"/>
                    </svg>
                </div>
                <div class="wrap-social-media">
                    <?php
                    $social_media = get_field('social-media', 'option');
                    if( $social_media ) : ?>
                        <div class="menu menu-2">
                            <ul class="d-flex justify-content-between align-items-center">
                                <?php while( have_rows('social-media', 'option') ) : the_row(); ?>
                                    <li>
                                        <a href="<?php the_sub_field('url'); ?>" target="_blank">
                                            <?php the_sub_field('name'); ?>
                                        </a>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="menu menu-3 remove-ml d-md-none d-block">
                    <ul class="d-flex flex-column">
                        <?php
                        wp_nav_menu([
                                'theme_location' => 'footer-menu',
                                'menu' => '',
                                'container' => '',
                                'container_class' => '',
                                'container_id' => '',
                                'menu_class' => 'menu',
                                'menu_id' => '',
                                'echo' => true,
                                'fallback_cb' => 'wp_page_menu',
                                'before' => '',
                                'after' => '',
                                'link_before' => '',
                                'link_after' => '',
                                'items_wrap' => '%3$s',
                                'depth' => 0,
                                'walker' => '',
                        ]);
                        ?>
                        <li><a href="mailto:support@rasti.com.ua">support@rasti.com.ua</a></li>
                    </ul>
                </div>
                <div class="row">
                    <div class="col col-sm-6 col-md d-flex align-items-end">
                        <p class="copy-right t-green ttu ts-sm-8 ts-12">
                            Copyright © 2026, All Rights Reserved <br>
                            ДИЗАЙН-ПАРТНЕР — <a target="_blank" href="https://www.instagram.com/oda.design.agency/">
                                ODA DESIGN AGENCY
                            </a>
                        </p>
                    </div>
                    <div class="col-auto col-sm-6 col-md-auto d-flex d-md-block">
                        <?php
                        $social_media = get_field('list-payment', 'option');
                        if( $social_media ) : ?>
                            <div class="menu wrap-pay-img mt-auto w100 d-flex justify-content-end">
                                <ul class="flex-wrap d-flex align-items-center justify-content-between">
                                    <?php while( have_rows('list-payment', 'option') ) : the_row(); ?>
                                        <li>
                                            <a href="<?php the_sub_field('link'); ?>" target="_blank">
                                                <img class="lazy-img"
                                                     src="data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%201%201%22%3E%3C%2Fsvg%3E"
                                                     data-src="<?php echo get_sub_field('logo')['url']; ?>">
                                            </a>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="menu menu-3 d-none d-md-block">
                            <ul class="d-flex align-items-center">
                                <li><a href="mailto:support@rasti.com.ua">support@rasti.com.ua</a></li>
                                <?php
                                wp_nav_menu([
                                        'theme_location' => 'footer-menu',
                                        'menu' => '',
                                        'container' => '',
                                        'container_class' => '',
                                        'container_id' => '',
                                        'menu_class' => 'menu',
                                        'menu_id' => '',
                                        'echo' => true,
                                        'fallback_cb' => 'wp_page_menu',
                                        'before' => '',
                                        'after' => '',
                                        'link_before' => '',
                                        'link_after' => '',
                                        'items_wrap' => '%3$s',
                                        'depth' => 0,
                                        'walker' => '',
                                ]);
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <button type="button" class="scroll-top-button anim-025" aria-label="Повернутися вгору">
        <span>UP</span>
    </button>
</div>
<div class="cart-notification" id="cart-notification" aria-live="polite">
    <span class="cart-notification__text"></span>
    <a class="cart-notification__link brackets" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
        <?php esc_html_e( 'Перейти до кошику', 'woocommerce' ); ?>
    </a>
    <button class="cart-notification__close remove" type="button" aria-label="Закрити"></button>
</div>
<?php wp_footer(); ?>
</body>
</html>
