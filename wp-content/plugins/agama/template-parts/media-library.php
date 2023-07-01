<?php
$perpage = PalleonSettings::get_option('ml_pagination',18);
$pexels = PalleonSettings::get_option('pexels','');
if (!empty($pexels)) {
?>
<div id="modal-media-library" class="palleon-modal">
    <div class="palleon-modal-close" data-target="#modal-media-library"><span class="material-icons">close</span></div>
    <div class="palleon-modal-wrap">
        <div class="palleon-modal-inner">
            <div class="palleon-tabs">
                <div id="pexels" class="palleon-tab active">
                    <div id="pexels-menu">
                        <div id="pexels-search-options">
                            <select id="pexels-orientation" class="palleon-select" autocomplete="off" disabled>
                                <option value="" selected><?php echo esc_html__('All Orientations', 'agama'); ?></option>
                                <option value="landscape"><?php echo esc_html__('Landscape', 'agama'); ?></option>
                                <option value="portrait"><?php echo esc_html__('Portrait', 'agama'); ?></option>
                                <option value="square"><?php echo esc_html__('Square', 'agama'); ?></option>
                            </select>
                            <select id="pexels-color" class="palleon-select" autocomplete="off" disabled>
                                <option value="" selected><?php echo esc_html__('All Colors', 'agama'); ?></option>
                                <option value="white"><?php echo esc_html__('White', 'agama'); ?></option>
                                <option value="black"><?php echo esc_html__('Black', 'agama'); ?></option>
                                <option value="gray"><?php echo esc_html__('Gray', 'agama'); ?></option>
                                <option value="brown"><?php echo esc_html__('Brown', 'agama'); ?></option>
                                <option value="blue"><?php echo esc_html__('Blue', 'agama'); ?></option>
                                <option value="turquoise"><?php echo esc_html__('Turquoise', 'agama'); ?></option>
                                <option value="red"><?php echo esc_html__('Red', 'agama'); ?></option>
                                <option value="violet"><?php echo esc_html__('Violet', 'agama'); ?></option>
                                <option value="pink"><?php echo esc_html__('Pink', 'agama'); ?></option>
                                <option value="orange"><?php echo esc_html__('Orange', 'agama'); ?></option>
                                <option value="yellow"><?php echo esc_html__('Yellow', 'agama'); ?></option>
                                <option value="green"><?php echo esc_html__('Green', 'agama'); ?></option>
                            </select>
                        </div>
                        <div class="palleon-search-box">
                            <input id="palleon-pexels-keyword" type="search" class="palleon-form-field" placeholder="<?php echo esc_html__('Enter a keyword...', 'agama'); ?>" autocomplete="off" />
                            <button id="palleon-pexels-search" type="button" class="palleon-btn primary"><span class="material-icons">search</span></button>
                        </div>
                    </div>
                    <div id="pexels-output">
                        <?php PalleonPexels::curated(); ?>
                    </div>
                    <a id="pexels-credit" href="https://www.pexels.com/" target="_blank"><?php echo esc_html__('Photos provided by Pexels', 'agama'); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>    
<?php } ?>