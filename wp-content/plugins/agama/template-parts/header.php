<!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <?php
        $product_id = $_GET['product_id'];
        ?>
        <title><?php echo esc_html(get_the_title($product_id)); ?></title>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta name="description" content="<?php echo esc_attr(get_the_excerpt($product_id)); ?>">
        <?php do_action('palleon_head'); ?>
    </head>
    <?php
    $bodyClasses = '';
    $default_theme = PalleonSettings::get_option('default_theme','dark');
    $custom_theme = Palleon::get_user_option('custom-theme', get_current_user_id(), $default_theme);
    if (is_admin()) {
        $bodyClasses .= 'backend ';
    } else {
        $bodyClasses .= 'frontend ';
    }
    if (isset($_GET['attachment_id']) && !empty($_GET['attachment_id'])) {
        $bodyClasses .= 'edit_attachment ';
    }
    $bodyClasses .=  $custom_theme . '-theme ';
    ?>
    <body id="palleon" class="<?php echo esc_attr($bodyClasses); ?>">
    <?php do_action('palleon_body_start'); ?>