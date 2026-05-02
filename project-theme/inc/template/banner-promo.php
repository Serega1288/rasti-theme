<section
    <?php if( get_sub_field('id_block') ) : ?>
        id="<?php the_sub_field('id_block'); ?>"
    <?php else : ?>
        id="section-<?php echo $args; ?>"
    <?php endif; ?>
    class="section section-stub banner-promo pos line-end">
    <?php
    $fon_block        = get_sub_field('banner');
    $fon_block_mobile = get_sub_field('banner_mobile');
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
                    loading="lazy"
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

            <div class="box-1 pos d-flex flex-column justify-content-end">
                <div class="wrap-decore wrap-decore-2">
                    <div class="decore decore-1 d-minus"></div>
                    <div class="decore decore-2 d-minus"></div>
                    <div class="decore decore-3 d-minus"></div>
                    <div class="decore decore-4 d-plus"></div>
                    <div class="decore decore-5 d-minus"></div>
                    <div class="decore decore-6 d-minus"></div>
                    <div class="decore decore-7 d-minus"></div>
                    <div class="decore decore-8 d-minus"></div>
                    <div class="decore decore-9 d-plus"></div>
                </div>
                <?php $desc = get_sub_field('desc');
                if ( $desc ) : ?>
                    <div class="decs ts-20 ts-sm-14">
                        <?php echo $desc; ?>
                        <?php
                        $link = get_sub_field('link');
                        if( $link ):
                            $link_url = $link['url'];
                            $link_title = $link['title'];
                            $link_target = $link['target'] ? $link['target'] : '_self';
                            ?>
                            <div class="wrap-btn">
                                <a
                                        target="<?php echo esc_attr( $link_target ); ?>"
                                        href="<?php echo esc_url( $link_url ); ?>"
                                        class="btn btn-1 ttu">
                                    <?php echo esc_html( $link_title ); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>