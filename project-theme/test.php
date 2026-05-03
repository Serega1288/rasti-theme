<?php
/**
 * Main theme template.
 * template name: test page
 * @package ProjectTheme
 */
$borderColor = '';
$bgImage = get_stylesheet_directory_uri() . '/assets/img/email-img/fon-email.jpg';
$header_logo = get_stylesheet_directory_uri() . '/assets/img/email-img/header-logo.png';
$person = get_stylesheet_directory_uri() . '/assets/img/email-img/person.jpg';
$footer_logo = get_stylesheet_directory_uri() . '/assets/img/email-img/footer-logo.png';
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width" />
    <title><?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
</head>
<body style="background-color: #1E1E1E; width: 100%; height: 100%;">

<!-- =============================================
     СЮДИ ВСТАВЛЯЙ СВОЮ ВЕРСТКУ ЛИСТА
     ============================================= -->

<div style="background-color: #1E1E1E; width: 100%; height: 100%;">
    <div style="max-width: 600px; margin: 40px auto;">
        <table cellpadding="0" border="0" cellspacing="0" width="100%"
               background="<?php echo esc_url( $bgImage ); ?>"
               style="border: 1px solid <?php echo $borderColor; ?>; color: #E9E9E9; font-family: Arial; font-size: 14px; line-height: 18px;
                       border: 1px solid #BDFF7B;
                       background-image: url('<?php echo esc_url( $bgImage ); ?>'); background-repeat: no-repeat;
                       background-position: center top; background-size: cover;" cellpadding="0" border="0" cellspacing="0" width="100%">

            <tr>
                <td style="padding: 40px; text-align: center;">
                    <img style="margin: 0 auto 20px" src="<?php echo $header_logo; ?>" alt="">
                    <p>Привіт, пишу тобі подякувати за підтримку мого проєкту!</p>
                    <p>Те, що ти зараз робиш, має значення! І навіть більше, ніж може тобі здаватись.</p>
                    <p>Друг Расті</p>
                    <img style="margin: 0 auto 260px" src="<?php echo $person; ?>" alt="">
                    <img style="margin: 0 auto 50px" src="<?php echo $footer_logo; ?>" alt="">
                    <div style="padding: 0;">
                        <table style="margin: 0;" role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
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
                                    <td style="color:#BDFF7B; text-transform: uppercase; align-items: <?php echo $align; ?>;" align="<?php echo esc_attr( $align ); ?>" valign="middle" width="<?php echo esc_attr( $width ); ?>%">
                                        <a style="color:#BDFF7B; text-transform: uppercase;" target="_blank" href="<?php echo $item['url']; ?>">
                                            <?php echo $item['name']; ?>
                                        </a>
                                    </td>
                                <?php
                                endforeach;
                            endif;
                            ?>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>


<style>
    p {
        margin: 0 0 20px;
    }
</style>

<!-- =============================================
     КІНЕЦЬ ВЕРСТКИ
     ============================================= -->

</body>
</html>

<div style="background-color: #1E1E1E; width: 100%; height: 100%;">
    <div style="max-width: 600px; margin: 40px auto;">
        <table cellpadding="0" border="0" cellspacing="0" width="100%"
               background="<?php echo esc_url( $bgImage ); ?>"
               style="border: 1px solid <?php echo $borderColor; ?>; color: #E9E9E9; font-family: Arial; font-size: 14px; line-height: 18px;
                   border: 1px solid #BDFF7B;
                   background-image: url('<?php echo esc_url( $bgImage ); ?>'); background-repeat: no-repeat;
                   background-position: center top; background-size: cover;" cellpadding="0" border="0" cellspacing="0" width="100%">

            <tr>
                <td style="padding: 40px; text-align: center;">
                    <img style="margin: 0 auto 20px" src="<?php echo $header_logo; ?>" alt="">
                    <p>Привіт, пишу тобі подякувати за підтримку мого проєкту!</p>
                    <p>Те, що ти зараз робиш, має значення! І навіть більше, ніж може тобі здаватись.</p>
                    <p>Друг Расті</p>
                    <img style="margin: 0 auto 260px" src="<?php echo $person; ?>" alt="">
                    <img style="margin: 0 auto 50px" src="<?php echo $footer_logo; ?>" alt="">
                    <div style="padding: 0;">
                        <table style="margin: 0;" role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
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
                                    <td style="color:#BDFF7B; text-transform: uppercase; align-items: <?php echo $align; ?>;" align="<?php echo esc_attr( $align ); ?>" valign="middle" width="<?php echo esc_attr( $width ); ?>%">
                                        <a style="color:#BDFF7B; text-transform: uppercase;" target="_blank" href="<?php echo $item['url']; ?>">
                                            <?php echo $item['name']; ?>
                                        </a>
                                    </td>
                                <?php
                                endforeach;
                            endif;
                            ?>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>


<style>
    p {
        margin: 0 0 20px;
    }
    body {
        background-color: #1E1E1E; width: 100%; height: 100%;
    }
</style>