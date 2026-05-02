<?php
/**
 * Theme override for Waitlist WooCommerce email footer content.
 *
 * @version 2.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$footer_content = xoo_wl_helper()->get_email_option( 'gl-ft-content' );

if ( ! $footer_content ) {
	return;
}
return;
?>

<table cellpadding="0" border="0" cellspacing="0" width="100%">
	<tr>
		<td align="center" style="padding: 16px 20px 0; color: #6b6b6b; font-size: 13px; line-height: 1.5;">
			<?php echo $footer_content; ?>
		</td>
	</tr>
</table>
