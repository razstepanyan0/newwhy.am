<div id="palleon-body">
    <div class="palleon-wrap">
        <div class="palleon-inner-wrap">
            <div id="palleon-content">
                <?php
                    $product_id = $_GET['product_id'];
                    $product = wc_get_product( $product_id );
                    $name = get_post_meta( $product_id, 'agama_cmb2_template_name', true );
                    $template = get_post_meta( $product_id, 'agama_cmb2_template', true );
                    $print_areas = get_post_meta( $product_id, 'agama_cmb2_additional_options', true );
                    $class = '';
                    if ($print_areas != 'printareas') {
                        $class = 'd-none';
                    }

                    echo '<div id="agama-print-areas" class="' . $class . '">';

                    echo '<button id="area-btn-1" type="button" class="palleon-btn primary load-template selected" data-price="" data-val="0" data-json="' . $template . '" data-img="" data-area="' . $name . '">' . $name . '<span></span></button>';

                    if ($print_areas == 'printareas') {
                        $entries = get_post_meta( $product_id, 'agama_cmb2_print_areas', true );
                        $area_selected = 2;
                        foreach ( (array) $entries as $key => $entry ) {      
                            $area = $area_template = $area_additional_price = '';
                            if ( isset( $entry['name'] ) ) {
                                $area = esc_html( $entry['name'] );
                            } 
                            if ( isset( $entry['template'] ) ) {
                                $area_template = esc_url( $entry['template'] );
                            } 
                            if ( isset( $entry['additional_price'] ) ) {
                                $area_additional_price = esc_html( $entry['additional_price'] );
                            }
                            echo '<button id="area-btn-' . $area_selected .'" type="button" class="palleon-btn primary load-template" data-price="' . $area_additional_price . '" data-val="0" data-json="' . $area_template . '" data-img="" data-area="' . $area . '">' . $area . '<span></span></button>';
                            $area_selected++;
                        }
                    }
                    echo '</div>';
                ?>
                <div id="palleon-canvas-img-wrap">
                    <img id="palleon-canvas-img" src="" data-filename="" data-id="" data-template="" />
                </div>
                <div id="palleon-canvas-wrap">
                    <div id="palleon-canvas-overlay"></div>
                    <div id="palleon-canvas-loader"><div class="palleon-loader"></div></div>
                    <div id="agama-canvas-loader"><div class="palleon-loader"></div></div>
                    <canvas id="palleon-canvas"></canvas>
                </div>
                <div class="palleon-content-bar">
                    <div class="palleon-img-size"><span id="palleon-img-width">0</span>px<span class="material-icons">clear</span><span id="palleon-img-height">0</span>px</div>
                    <button id="palleon-img-drag" class="palleon-btn"><span class="material-icons">pan_tool</span></button>
                    <div id="palleon-img-zoom-counter" class="palleon-counter">
                        <button id="palleon-img-zoom-out" class="counter-minus palleon-btn"><span class="material-icons">remove</span></button>
                        <input id="palleon-img-zoom" class="palleon-form-field numeric-field" type="text" value="100" autocomplete="off" data-min="10" data-max="200" data-step="5">
                        <button id="palleon-img-zoom-in" class="counter-plus palleon-btn"><span class="material-icons">add</span></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>