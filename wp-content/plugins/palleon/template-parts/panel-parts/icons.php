<div id="palleon-icons" class="palleon-icon-panel-content panel-hide">
    <div class="palleon-tabs">
        <ul class="palleon-tabs-menu">
            <li class="active" data-target="#palleon-all-icons"><?php echo esc_html__('Icons', 'palleon'); ?></li>
            <li id="palleon-custom-svg-open" data-target="#palleon-customsvg-upload"><?php echo esc_html__('Custom SVG', 'palleon'); ?></li>
        </ul>
        <div id="palleon-all-icons" class="palleon-tab active">
            <div class="palleon-control-wrap" style="margin:0px;">
                <label class="palleon-control-label"><?php echo esc_html__('Icon Style', 'palleon'); ?></label>
                <div class="palleon-control">
                    <select id="palleon-icon-style" class="palleon-select" autocomplete="off">
                        <option selected value="materialicons"><?php echo esc_html__('Filled', 'palleon'); ?></option>
                        <option value="materialiconsoutlined"><?php echo esc_html__('Outlined', 'palleon'); ?></option>
                        <option value="materialiconsround"><?php echo esc_html__('Round', 'palleon'); ?></option>
                    </select>
                </div>
            </div>
            <hr/>
            <div class="palleon-search-wrap">
                <input id="palleon-icon-search" type="search" class="palleon-form-field" placeholder="<?php echo esc_html__('Enter a keyword...', 'palleon'); ?>" autocomplete="off" />
                <span id="palleon-icon-search-icon" class="material-icons">search</span>
            </div>
            <div id="palleon-icons-grid" class="palleon-grid palleon-elements-grid four-column">
            </div>
            <div id="palleon-noicons" class="notice notice-warning"><?php echo esc_html__('Nothing found.', 'palleon'); ?></div>
        </div>
        <div id="palleon-customsvg-upload" class="palleon-tab">
            <div class="palleon-file-field">
                <input type="file" name="palleon-element-upload" id="palleon-element-upload" class="palleon-hidden-file" accept="image/svg+xml" />
                <label for="palleon-element-upload" class="palleon-btn primary palleon-lg-btn btn-full"><span class="material-icons">upload</span><span><?php echo esc_html__('Upload SVG from computer', 'palleon'); ?></span></label>
            </div>
            <?php $allowSVG =  PalleonSettings::get_option('allow_svg', 'enable');
            if ($allowSVG == 'enable' && is_admin()) {
            ?>
            <button id="palleon-svg-media-library" type="button" class="palleon-btn primary palleon-lg-btn btn-full palleon-modal-open" data-target="#modal-svg-library"><span class="material-icons">photo_library</span><?php echo esc_html__('Select From Media Library', 'palleon'); ?></button>
            <?php } ?>
            <div id="palleon-custom-svg-options">
                <hr/>
                <div class="palleon-control-wrap label-block">
                    <div class="palleon-control">
                        <div class="palleon-btn-group icon-group">
                            <button id="customsvg-flip-horizontal" type="button" class="palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('Flip X', 'palleon'); ?>"><span class="material-icons">flip</span></button>
                            <button id="customsvg-flip-vertical" type="button" class="palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('Flip Y', 'palleon'); ?>"><span class="material-icons">flip</span></button>
                            <button type="button" class="palleon-horizontal-center palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('H-Align Center', 'palleon'); ?>"><span class="material-icons">align_horizontal_center</span></button>
                            <button type="button" class="palleon-vertical-center palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('V-Align Center', 'palleon'); ?>"><span class="material-icons">vertical_align_center</span></button>
                        </div>
                    </div>
                </div>
                <div class="palleon-control-wrap label-block">
                    <label class="palleon-control-label slider-label"><?php echo esc_html__('Opacity', 'palleon'); ?><span>1</span></label>
                    <div class="palleon-control">
                        <input id="customsvg-opacity" type="range" min="0" max="1" value="1" step="0.1" class="palleon-slider" autocomplete="off">
                    </div>
                </div>
                <div class="palleon-control-wrap label-block">
                    <label class="palleon-control-label slider-label"><?php echo esc_html__('Skew X', 'palleon'); ?><span>0</span></label>
                    <div class="palleon-control">
                        <input id="customsvg-skew-x" type="range" min="0" max="180" value="0" step="1" class="palleon-slider" autocomplete="off">
                    </div>
                </div>
                <div class="palleon-control-wrap label-block">
                    <label class="palleon-control-label slider-label"><?php echo esc_html__('Skew Y', 'palleon'); ?><span>0</span></label>
                    <div class="palleon-control">
                        <input id="customsvg-skew-y" type="range" min="0" max="180" value="0" step="1" class="palleon-slider" autocomplete="off">
                    </div>
                </div>
                <div class="palleon-control-wrap label-block">
                    <label class="palleon-control-label slider-label"><?php echo esc_html__('Rotate', 'palleon'); ?><span>0</span></label>
                    <div class="palleon-control">
                        <input id="customsvg-rotate" type="range" min="0" max="360" value="0" step="1" class="palleon-slider" autocomplete="off">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>