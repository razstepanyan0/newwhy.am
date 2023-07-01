<div id="palleon-settings" class="palleon-icon-panel-content panel-hide">
    <h5><?php echo esc_html__('Preferences', 'agama'); ?></h5>
    <div id="palleon-preferences">
        <div class="palleon-control-wrap label-block">
            <?php $font_size = Palleon::get_user_option('custom-font-size', get_current_user_id(), 14); ?>
            <label class="palleon-control-label slider-label"><?php echo esc_html__('Font Size', 'agama'); ?><span><?php echo esc_html($font_size); ?></span></label>
            <div class="palleon-control">
                <input id="custom-font-size" type="range" min="10" max="18" value="<?php echo esc_html($font_size); ?>" step="1" class="palleon-slider preference" autocomplete="off">
            </div>
        </div>
        <div class="palleon-control-wrap">
        <?php 
        $default_theme = PalleonSettings::get_option('default_theme','dark');
        $custom_theme = Palleon::get_user_option('custom-theme', get_current_user_id(), $default_theme);
        ?>
            <label class="palleon-control-label"><?php echo esc_html__('Theme', 'agama'); ?></label>
            <div class="palleon-control">
                <select id="custom-theme" class="palleon-select preference" autocomplete="off">
                    <option value="dark" <?php if ($custom_theme == 'dark') { echo 'selected'; } ?>><?php echo esc_html__('Dark', 'agama'); ?></option>
                    <option value="light" <?php if ($custom_theme == 'light') { echo 'selected'; } ?>><?php echo esc_html__('Light', 'agama'); ?></option>
                </select>
            </div>
        </div>
        <div class="palleon-control-wrap control-text-color">
        <?php $bg_color = Palleon::get_user_option('custom-background', get_current_user_id(), ''); ?>
            <label class="palleon-control-label"><?php echo esc_html__('Background', 'agama'); ?></label>
            <div class="palleon-control">
                <input id="custom-background" type="text" class="palleon-colorpicker allow-empty preference" autocomplete="off" value="<?php echo esc_html($bg_color); ?>" />
            </div>
        </div>
    </div>
    <?php if (get_current_user_id()) { ?>
    <div class="palleon-control-wrap label-block">
        <div class="palleon-control">
            <button id="palleon-preferences-save" type="button" class="palleon-btn palleon-lg-btn btn-full primary"><?php echo esc_html__('Save Preferences', 'agama'); ?></button>
        </div>
    </div>
    <?php } ?>
</div>