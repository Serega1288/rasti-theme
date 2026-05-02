<?php
$constructor_name = '';

if ( is_array( $args ) ) {
    $constructor_name = isset( $args['constructor_name'] ) ? (string) $args['constructor_name'] : '';
} elseif ( is_scalar( $args ) || null === $args ) {
    $constructor_name = (string) $args;
}
 
$step_section = 0;
while ( have_rows( 'constructor' ) ) :
    the_row();
    $section_index = $step_section++;
    $section_suffix = '' !== $constructor_name ? $section_index . '-' . $constructor_name : (string) $section_index;
    $disable = get_sub_field('disable_block');
    ?>


    <?php  //if( get_row_layout() == 'banner-slider' ): ?>

    <?php //get_template_part( 'inc/template/banner', 'slider'); ?>

    <?php if( get_row_layout() == 'template-banner-promo' ): ?>

        <?php if( $disable == true ) {} else {
            get_template_part( 'inc/template/banner', 'promo', $section_suffix );
        } ?>

    <?php elseif( get_row_layout() == 'template-book-buy' ): ?>
        <?php if( $disable == true ) {} else {
            get_template_part( 'inc/template/book', 'buy', $section_suffix );
        } ?>

    <?php elseif( get_row_layout() == 'template-slider-product' ): ?>
        <?php if( $disable == true ) {} else {
            get_template_part( 'inc/template/slider', 'product', $section_suffix );
        } ?>

    <?php endif; endwhile; ?>
