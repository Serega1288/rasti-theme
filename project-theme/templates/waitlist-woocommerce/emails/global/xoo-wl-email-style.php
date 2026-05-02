<?php
/**
 * Theme override for Waitlist WooCommerce email styles.
 *
 * @version 2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<style type="text/css">
	body,
	td,
	input,
	textarea,
	select {
		font-family: <?php echo xoo_wl_helper()->get_email_style_option( 'c-font-family' ); ?>, Tahoma, sans-serif;
	}

	#outlook a {
		padding: 0;
	}

	.ReadMsgBody,
	.ExternalClass {
		width: 100%;
	}

	.ExternalClass,
	.ExternalClass p,
	.ExternalClass span,
	.ExternalClass font,
	.ExternalClass td,
	.ExternalClass div {
		line-height: 100%;
	}

	body,
	table,
	td,
	a {
		-webkit-text-size-adjust: 100%;
		-ms-text-size-adjust: 100%;
	}

	table,
	td {
		mso-table-lspace: 0pt;
		mso-table-rspace: 0pt;
	}

	img {
		-ms-interpolation-mode: bicubic;
		border: 0;
		/*height: auto;*/
		line-height: 100%;
		outline: none;
		text-decoration: none;
	}

	body {
		height: 100% !important;
		margin: 0;
		padding: 0;
		width: 100% !important;
	}

	table {
		border-collapse: collapse !important;
	}

	.apple-links a {
		color: #999999;
		text-decoration: none;
	}

	h1,
	h2,
	h3,
	h4,
	h5,
	h6 {
		margin: 5px 0 !important;
	}

	@media screen and (max-width: 600px) {
		table.xoo-wl-table-full {
			width: 100% !important;
		}

		table.xoo-wl-bist-content {
			margin-top: 20px;
			margin-bottom: 20px;
		}
	}

    a {
        border-radius: 0 !important;
        text-transform: uppercase !important;
        font-weight: bold !important;
        color: #BDFF7B;
    }
</style>
