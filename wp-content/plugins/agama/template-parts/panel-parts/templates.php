<div id="palleon-agama-templates" class="palleon-icon-panel-content panel-hide">
    <?php
    $terms = get_terms( 'agamatemplatetags', array(
        'hide_empty' => true,
    ));
    if (!empty($terms)) {
    ?>
    <div class="agama-templates-filters">
        <select id="agama-templates-tag" class="palleon-select" autocomplete="off">
            <option value="" selected><?php echo esc_html__('All Categories', 'agama'); ?></option>
            <?php
            foreach( $terms as $term ) {
                echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
            }
            ?>
        </select>
    </div>
    <?php } ?>
    <div id="agama-templates" class="agama-templates">
        <?php
        $args = array(
            'post_type' => 'agamatemplates',
            'posts_per_page'  => 9999
        );

        $the_query = new WP_Query( $args );

        if ( $the_query->have_posts() ) {
            while ( $the_query->have_posts() ) : $the_query->the_post();
            $id = get_the_ID();
            $title = get_the_title($id);
            $templateUrl = get_post_meta( $id, 'agama_custom_template', true );
            $imageurl = get_the_post_thumbnail_url($id,'medium');
            $terms = get_the_terms( $id, 'agamatemplatetags' );
            $customTags = array();
            if ($terms) {
                foreach( $terms as $term ) {
                    $customTags[] = $term->term_id;
                }
            }
        ?>
        <div class="agama-templates-item" data-keyword="<?php echo esc_attr($title); ?>" data-json="<?php echo esc_url($templateUrl); ?>" data-category="<?php echo json_encode($customTags); ?>">
            <div class="agama-templates-item-inner">
                <div class="palleon-img-wrap">
                    <div class="palleon-img-loader"></div>
                    <img class="lazy" data-src="<?php echo esc_url($imageurl); ?>" title="<?php echo esc_attr($title); ?>" />
                </div>
            </div>
        </div>
        <?php 
        endwhile;
    } ?>
    </div>
    <?php do_action('agama_templates_end'); ?>
</div>