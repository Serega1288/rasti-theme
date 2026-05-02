<?php
/**
 * Theme override for Waitlist WooCommerce email header.
 *
 * @version 2.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$outContBGcolor = xoo_wl_helper()->get_email_style_option( 'c-outbgcolor' );
$inContBGcolor  = xoo_wl_helper()->get_email_style_option( 'c-inbgcolor' );
$txtColor       = xoo_wl_helper()->get_email_style_option( 'c-txtcolor' );
$borderColor    = xoo_wl_helper()->get_email_style_option( 'c-bdcolor' );
$fontSize       = xoo_wl_helper()->get_email_style_option( 'c-fsize' ) . 'px';
$contentPadding = xoo_wl_helper()->get_email_style_option( 'c-cont-padding' );
$bgImage        = get_stylesheet_directory_uri() . '/assets/img/email-img/fon-email.jpg';
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width">
	<?php do_action( 'xoo_wl_email_head', $emailObj ); ?>
</head>

<body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
	<table cellpadding="0" border="0" cellspacing="0" width="100%">
		<tr>
			<td align="center" bgcolor="<?php echo $outContBGcolor; ?>" style="color: <?php echo $txtColor; ?>;" valign="top">
				<table cellpadding="2" cellspacing="0" width="600" class="xoo-wl-table-full" bgcolor="<?php echo $inContBGcolor; ?>" background="<?php echo esc_url( $bgImage ); ?>" style="border: 1px solid <?php echo $borderColor; ?>; background-image: url('<?php echo esc_url( $bgImage ); ?>'); background-repeat: no-repeat; background-position: center top; background-size: cover;">
					<?php if ( xoo_wl_helper()->get_email_option( 'gl-logo' ) ) : ?>
						<tr>
							<td align="center" style="padding: 40px 0 0 0">
								<img height="23px" width="155px" border="0" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" src="<?php echo esc_url( xoo_wl_helper()->get_email_option( 'gl-logo' ) ); ?>" style="display: block" />
							</td>
						</tr>
					<?php endif; ?>

					<tr>
						<td style="font-size: <?php echo $fontSize; ?>; padding: <?php echo $contentPadding; ?>">
