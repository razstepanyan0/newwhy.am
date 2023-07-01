<?php 
$pexels = PalleonSettings::get_option('pexels','');
$max_upload_size = PalleonSettings::get_option('agama_max_upload_size', 8);
$max_upload_size = $max_upload_size * 1048576;
?>
<div id="palleon-image" class="palleon-icon-panel-content panel-hide">
    <div class="palleon-tabs">
        <ul class="palleon-tabs-menu">
            <li id="palleon-img-mode" class="active" data-target="#palleon-image-mode"><?php echo esc_html__('Image', 'agama'); ?></li>
            <li data-target="#palleon-bg-image-mode"><?php echo esc_html__('Background Image', 'agama'); ?></li>
        </ul>
        <div id="palleon-image-mode" class="palleon-tab active">
            <div class="palleon-file-field">
            <input type="file" name="palleon-file" id="palleon-img-upload" class="palleon-hidden-file" accept="image/png, image/jpeg" data-max="<?php echo esc_attr($max_upload_size); ?>" />
            <label for="palleon-img-upload" class="palleon-btn primary palleon-lg-btn btn-full"><span class="material-icons">upload</span><span><?php echo esc_html__('Upload from computer', 'agama'); ?></span></label>
            </div>
            <?php if (!empty($pexels)) { ?>
            <button id="palleon-img-media-library" type="button" class="palleon-btn primary palleon-lg-btn btn-full palleon-modal-open" data-target="#modal-media-library"><span class="material-icons">photo_library</span><?php echo esc_html__('Select From Free Images', 'agama'); ?></button>
            <?php } ?>
            <div id="palleon-image-settings" class="palleon-sub-settings">
                <div class="palleon-control-wrap">
                    <label class="palleon-control-label"><?php echo esc_html__('Border Width', 'agama'); ?></label>
                    <div class="palleon-control">
                        <input id="img-border-width" class="palleon-form-field" type="number" value="0" data-min="0" data-max="1000" step="1" autocomplete="off">
                    </div>
                </div>
                <div class="palleon-control-wrap">
                    <label class="palleon-control-label"><?php echo esc_html__('Border Color', 'agama'); ?></label>
                    <div class="palleon-control">
                        <input id="img-border-color" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="#ffffff" />
                    </div>
                </div>
                <div class="palleon-control-wrap label-block">
                    <label class="palleon-control-label slider-label"><?php echo esc_html__('Rounded Corners', 'agama'); ?><span>0</span></label>
                    <div class="palleon-control">
                        <input id="img-border-radius" type="range" min="0" max="1000" value="0" step="1" class="palleon-slider" autocomplete="off">
                    </div>
                </div>
                <div class="palleon-control-wrap conditional">
                    <label class="palleon-control-label"><?php echo esc_html__('Shadow', 'agama'); ?></label>
                    <div class="palleon-control palleon-toggle-control">
                        <label class="palleon-toggle">
                            <input id="palleon-image-shadow" class="palleon-toggle-checkbox" data-conditional="#image-shadow-settings" type="checkbox" autocomplete="off" />
                            <div class="palleon-toggle-switch"></div>
                        </label>
                    </div>
                </div>
                <div id="image-shadow-settings" class="d-none conditional-settings">
                    <div class="palleon-control-wrap">
                        <label class="palleon-control-label"><?php echo esc_html__('Shadow Color', 'agama'); ?></label>
                        <div class="palleon-control">
                            <input id="image-shadow-color" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="#000" />
                        </div>
                    </div>
                    <div class="palleon-control-wrap">
                        <label class="palleon-control-label"><?php echo esc_html__('Shadow Blur', 'agama'); ?></label>
                        <div class="palleon-control">
                            <input id="image-shadow-blur" class="palleon-form-field" type="number" value="5" step="1" autocomplete="off">
                        </div>
                    </div>
                    <div class="palleon-control-wrap">
                        <label class="palleon-control-label"><?php echo esc_html__('Offset X', 'agama'); ?></label>
                        <div class="palleon-control">
                            <input id="image-shadow-offset-x" class="palleon-form-field" type="number" value="5" step="1" autocomplete="off">
                        </div>
                    </div>
                    <div class="palleon-control-wrap">
                        <label class="palleon-control-label"><?php echo esc_html__('Offset Y', 'agama'); ?></label>
                        <div class="palleon-control">
                            <input id="image-shadow-offset-y" class="palleon-form-field" type="number" value="5" step="1" autocomplete="off">
                        </div>
                    </div>
                </div>
                <hr/>
                <div class="palleon-control-wrap label-block">
                    <div class="palleon-control">
                        <div class="palleon-btn-group icon-group">
                            <button id="img-flip-horizontal" type="button" class="palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('Flip X', 'agama'); ?>"><span class="material-icons">flip</span></button>
                            <button id="img-flip-vertical" type="button" class="palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('Flip Y', 'agama'); ?>"><span class="material-icons">flip</span></button>
                            <button type="button" class="palleon-horizontal-center palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('H-Align Center', 'agama'); ?>"><span class="material-icons">align_horizontal_center</span></button>
                            <button type="button" class="palleon-vertical-center palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('V-Align Center', 'agama'); ?>"><span class="material-icons">vertical_align_center</span></button>
                        </div>
                    </div>
                </div>
                <div class="palleon-control-wrap label-block">
                    <label class="palleon-control-label slider-label"><?php echo esc_html__('Opacity', 'agama'); ?><span>1</span></label>
                    <div class="palleon-control">
                        <input id="img-opacity" type="range" min="0" max="1" value="1" step="0.1" class="palleon-slider" autocomplete="off">
                    </div>
                </div>
                <div class="palleon-control-wrap label-block">
                    <label class="palleon-control-label slider-label"><?php echo esc_html__('Skew X', 'agama'); ?><span>0</span></label>
                    <div class="palleon-control">
                        <input id="img-skew-x" type="range" min="0" max="180" value="0" step="1" class="palleon-slider" autocomplete="off">
                    </div>
                </div>
                <div class="palleon-control-wrap label-block">
                    <label class="palleon-control-label slider-label"><?php echo esc_html__('Skew Y', 'agama'); ?><span>0</span></label>
                    <div class="palleon-control">
                        <input id="img-skew-y" type="range" min="0" max="180" value="0" step="1" class="palleon-slider" autocomplete="off">
                    </div>
                </div>
                <div class="palleon-control-wrap label-block">
                    <label class="palleon-control-label slider-label"><?php echo esc_html__('Rotate', 'agama'); ?><span>0</span></label>
                    <div class="palleon-control">
                        <input id="img-rotate" type="range" min="0" max="360" value="0" step="1" class="palleon-slider" autocomplete="off">
                    </div>
                </div>
            </div>
        </div>
        <div id="palleon-bg-image-mode" class="palleon-tab">
            <div class="palleon-file-field">
            <input type="file" name="palleon-file" id="agama-bg-img-upload" class="palleon-hidden-file" accept="image/png, image/jpeg" data-max="<?php echo esc_attr($max_upload_size); ?>" />
            <label for="agama-bg-img-upload" class="palleon-btn primary palleon-lg-btn btn-full"><span class="material-icons">upload</span><span><?php echo esc_html__('Upload from computer', 'agama'); ?></span></label>
            </div>
            <?php if (!empty($pexels)) { ?>
            <button id="agama-bg-media-library" type="button" class="palleon-btn primary palleon-lg-btn btn-full palleon-modal-open" data-target="#modal-media-library"><span class="material-icons">photo_library</span><?php echo esc_html__('Select From Free Images', 'agama'); ?></button>
            <?php } ?>
            <button id="agama-bg-delete" type="button" class="palleon-btn palleon-lg-btn btn-full"><span class="material-icons">delete</span><?php echo esc_html__('Remove Background Image', 'agama'); ?></button>
            <div id="agama-bg-image-settings">
                <div class="palleon-control-wrap label-block">
                    <label class="palleon-control-label slider-label"><?php echo esc_html__('Image Width', 'agama'); ?><span>200</span></label>
                    <div class="palleon-control">
                        <input id="agama-bg-width" type="range" min="100" max="4000" value="200" step="1" class="palleon-slider" autocomplete="off">
                    </div>
                </div>
                <div class="palleon-control-wrap label-block">
                    <label class="palleon-control-label slider-label"><?php echo esc_html__('Offset X', 'agama'); ?><span>0</span></label>
                    <div class="palleon-control">
                        <input id="agama-bg-offset-x" type="range" min="0" max="4000" value="0" step="1" class="palleon-slider" autocomplete="off">
                    </div>
                </div>
                <div class="palleon-control-wrap label-block">
                    <label class="palleon-control-label slider-label"><?php echo esc_html__('Offset Y', 'agama'); ?><span>0</span></label>
                    <div class="palleon-control">
                        <input id="agama-bg-offset-y" type="range" min="0" max="4000" value="0" step="1" class="palleon-slider" autocomplete="off">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>