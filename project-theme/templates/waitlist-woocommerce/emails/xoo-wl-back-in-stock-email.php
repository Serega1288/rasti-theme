<?php
/**
 * Theme override for Waitlist WooCommerce back in stock email.
 *
 * @version 2.8.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php do_action( 'xoo_wl_email_header', $emailObj ); ?>

<table cellpadding="0" border="0" cellspacing="0" width="100%">
	<?php if ( $heading ) : ?>
		<tr>
			<td style="color: <?php echo $headingColor; ?>; font-weight: normal; letter-spacing: 0.3px; font-size: <?php echo $headingFsize . 'px'; ?>;" align="center"><?php echo $heading; ?></td>
		</tr>
	<?php endif; ?>

	<tr>
		<td>
			<table cellpadding="0" cellspacing="0" width="100%" align="center">
				<tr>
					<td width="100%" align="center">
						<table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <?php if ( $show_pimage ) : ?>
                            <tr>
                                <td width="<?php echo $pimgWidth; ?>" align="center" valign="middle">
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td align="center">
                                                <img src="<?php echo $product_image; ?>" alt="<?php echo $product_name; ?>" width="auto"
                                                     height="<?php echo $pimgHeight == 0 ? 'auto' : $pimgHeight; ?>"
                                                     style="display:block;" />
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <?php endif; ?> 
							<tr>
								<td width="<?php echo 525 - $pimgWidth; ?>" align="<?php echo $show_pimage ? 'left' : 'center'; ?>" valign="middle" style="<?php echo $show_pimage ? '' : ''; ?>">
									<table width="100%" cellpadding="0" cellspacing="0" border="0">
										<tr>
											<td style="letter-spacing: 0.3px;">
												<?php echo $body_text; ?>
											</td>
										</tr>

										<?php if ( $enBuyBtn === 'yes' ) : ?>
											<tr>
												<td align="center" style="padding-bottom: 100px; padding-top: 50px;">
													<?php echo $emailObj->button_markup( $buy_now_text, $product_link ); ?>
												</td>
											</tr>
										<?php endif; ?>

                                        <?php $footer_content = xoo_wl_helper()->get_email_option( 'gl-ft-content' ); ?>

                                        <?php if ( $footer_content) : ?>
                                            <tr>
                                                <td style="letter-spacing: 0.3px;">
                                                    <?php echo $footer_content; ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>

                                        <?php if ( xoo_wl_helper()->get_email_option( 'gl-logo' ) ) : ?>
                                            <tr>
                                                <td align="center" style="padding: 40px 0 0 0">
                                                    <img height="100%" width="auto" border="0" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" src="<?php echo esc_url( xoo_wl_helper()->get_email_option( 'gl-logo' ) ); ?>" style="display: block" />
                                                </td>
                                            </tr>
                                        <?php endif; ?>

                                        <tr>
                                            <td>
                                                <table style="margin: 9px 0 0;" role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                                    <?php
                                                    $social_items = get_field( 'social-media', 'option' );

                                                    if ( $social_items ) :
                                                        $total = count( $social_items );
                                                        $width = 100 / $total;

                                                        foreach ( $social_items as $index => $item ) :
                                                            $is_first = 0 === $index;
                                                            $is_last  = $index === ( $total - 1 );

                                                            if ( $is_first ) {
                                                                $align = 'left';
                                                            } elseif ( $is_last ) {
                                                                $align = 'right';
                                                            } else {
                                                                $align = 'center';
                                                            }
                                                            ?>
                                                            <td style="color:#BDFF7B; text-transform: uppercase;" align="<?php echo esc_attr( $align ); ?>" valign="middle" width="<?php echo esc_attr( $width ); ?>%">
                                                                <a style="color:#BDFF7B; text-transform: uppercase;" target="_blank" href="<?php echo $item['url']; ?>">
                                                                    <?php echo $item['name']; ?>
                                                                </a>
                                                            </td>
                                                        <?php
                                                        endforeach;
                                                    endif;
                                                    ?>
                                                </table>
                                            </td>
                                        </tr>



									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<?php do_action( 'xoo_wl_email_footer', $emailObj ); ?>
