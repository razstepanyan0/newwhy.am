<?php
$logo = PalleonSettings::get_option('logo', PALLEON_PLUGIN_URL . 'assets/logo.png');
$logo_small = PalleonSettings::get_option('logo_small', PALLEON_PLUGIN_URL . 'assets/logo-small.png');
$uploadDownload = PalleonSettings::get_option('agama_upload_download', 'enable');
$history = PalleonSettings::get_option('agama_history', 'enable');
?>
<div id="palleon-top-bar">
    <div class="palleon-logo">
        <a href="<?php echo esc_url(home_url( '/' )); ?>">
            <img class="logo-desktop" src="<?php echo esc_url($logo); ?>" />
            <img class="logo-mobile" src="<?php echo esc_url($logo_small); ?>" />
        </a>
    </div>
    <div class="palleon-top-bar-menu">
        <?php if ($history == 'enable') { ?>
        <div class="palleon-undo">
            <button id="palleon-undo" type="button" class="palleon-btn-simple tooltip" data-title="<?php echo esc_attr__('Undo', 'palleon'); ?>" autocomplete="off" disabled><span class="material-icons">undo</span></button>
        </div>
        <div class="palleon-redo">
            <button id="palleon-redo" type="button" class="palleon-btn-simple tooltip" data-title="<?php echo esc_attr__('Redo', 'palleon'); ?>" autocomplete="off" disabled><span class="material-icons">redo</span></button>
        </div>
        <div class="palleon-history">
            <button id="palleon-history" type="button" class="palleon-btn-simple palleon-modal-open tooltip" data-title="<?php echo esc_attr__('History', 'palleon'); ?>" autocomplete="off" data-target="#modal-history" disabled><span class="material-icons">history</span></button>
        </div>
        <?php } ?>
        <?php if ($uploadDownload == 'enable') { ?>
        <div class="agama-upload-download">
            <div class="palleon-file-field">
                <input type="file" name="palleon-file" id="agama-upload-template" class="palleon-hidden-file" accept=".json,application/JSON" />
                <label for="agama-upload-template" class="palleon-btn tooltip" data-title="<?php echo esc_attr__('Upload Template', 'palleon'); ?>"><span class="material-icons">upload</span></label>
            </div>
            <button id="agama-download-template" type="button" class="palleon-btn tooltip" data-title="<?php echo esc_attr__('Download Template', 'palleon'); ?>" autocomplete="off"><span class="material-icons">file_download</span></button>
        </div>
        <?php } ?>
        <div class="agama-view-cart">
            <a id="agama-view-cart" href="<?php echo esc_url(wc_get_cart_url()); ?>" class="palleon-btn tooltip" data-title="<?php echo esc_attr__('View Cart', 'palleon'); ?>" target="_blank"><span class="material-icons">shopping_bag</span><span id="palleon-cart-count"><?php echo esc_html(count(WC()->cart->get_cart())); ?></span></a>
        </div>
        <div class="agama-add-to-cart">
            <?php 
            $product_id = '';
            if (isset($_GET['product_id'])) {
                $product_id = $_GET['product_id'];
                $product = wc_get_product( $product_id );
                $price = $product->get_price();
            }
            ?>
            <button id="agama-add-to-cart" type="button" data-product="<?php echo esc_attr($product_id); ?>" data-variation="" data-price="<?php echo esc_attr($price); ?>" class="palleon-btn primary" autocomplete="off"><span class="material-icons">shopping_cart</span><span class="palleon-btn-text"><?php echo esc_html__( 'Add To Cart', 'agama' ); ?></span></button>
        </div>
        <?php if (has_nav_menu( 'agama-menu' ) || get_current_user_id()) { ?>
        <div class="palleon-user-menu">
            <div id="palleon-user-menu" class="palleon-dropdown-wrap">
                <?php 
                if (get_current_user_id()) { 
                    echo get_avatar( get_current_user_id(), 64 ); 
                    echo '<span class="material-icons">arrow_drop_down</span>';
                } else {
                    echo '<span class="material-icons">menu</span>';
                }
                ?>
                <?php 
                if (has_nav_menu( 'agama-menu' )) {
                    wp_nav_menu( array(
                        'theme_location' => 'agama-menu',
                        'menu_id'        => 'agama-menu',
                        'menu_class'     => 'palleon-dropdown',
                        'depth'          => 1
                    ) ); 
                } else {
                    $account_menu = wc_get_account_menu_items();
                    if (!empty($account_menu)) {
                        $myaccount_permalink = wc_get_page_permalink( 'myaccount' );
                        echo '<div>';
                        echo '<ul class="palleon-dropdown">';
                        foreach( $account_menu as $item => $key ) {
                            if ($item == 'customer-logout') {
                                echo '<li><a href="' . esc_url(wc_logout_url()) . '">' . $key . '</a></li>';
                            } else {
                                echo '<li><a href="' . esc_url($myaccount_permalink . $item) . '" target="_blank">' . $key . '</a></li>';

                            }
                        }
                        echo '</ul>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>
        <?php } ?>
    </div>
</div>