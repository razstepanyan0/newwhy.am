<div id="palleon-adjust" class="palleon-icon-panel-content">
    <?php
    if (isset($_GET['product_id'])) {
        $product_id = $_GET['product_id'];
        $product = wc_get_product( $product_id );
        $price = $product->get_price_html();
        $title = get_the_title($product_id); 
        $slug = $product->get_slug();
        $attributes = $product->get_attributes();
        $stock = '';
        $notice = '';
        $color_field =  PalleonSettings::get_option('agama_color', '');
        if (!$product->is_in_stock()) {
            $stock = 'outofstock';
            $price = '<div class="notice notice-danger">' . esc_html__( 'Out of stock!', 'agama' ) . '</div>';
        } else if ($product->is_on_backorder() ) {
            $notice = '<p class="stock available-on-backorder">' . esc_html__( 'Available on backorder', 'agama' ) . '</p>';
        }
        if ($product->managing_stock()) {
            $notice = '<p class="stock">' . wc_get_stock_html($product) . '</p>';
        }
        ?>
        <h1 class="agama-product-title" data-slug="<?php echo esc_attr($slug); ?>"><?php echo esc_html($title); ?></h1>
        <?php 
        if (wc_review_ratings_enabled()) { 
            $rating_count = $product->get_rating_count();
            $review_count = $product->get_review_count();
            $average      = $product->get_average_rating();
            if ( $rating_count > 0 ) {
                echo '<div class="agama-product-review">' . wc_get_rating_html( $average, $rating_count ) . '</div>';
            }
            if ( comments_open($product_id) ) { ?>
                <a href="#reviews" class="woocommerce-review-link" rel="nofollow">(<?php printf( _n( '%s customer review', '%s customer reviews', $review_count, 'agama' ), '<span class="count">' . esc_html( $review_count ) . '</span>' ); ?>)</a>
            <?php }
        }
        ?>
        <div class="agama-product-price-wrapper">
            <span id="agama-product-price" class="<?php echo esc_attr($stock); ?>"><?php echo wp_kses_post($price); ?></span><span id="agama-additional-fee" data-fee="0"></span>
            <span id="agama-product-price-total"></span>
            <div id="agama-product-price-notice"><?php echo wp_kses_post($notice); ?></div>     
        </div>
        <?php
        $print_areas = get_post_meta( $product_id, 'agama_cmb2_additional_options', true );
        if ($print_areas == 'variants') {
            $name = get_post_meta( $product_id, 'agama_cmb2_template_name', true );
            $template = get_post_meta( $product_id, 'agama_cmb2_template', true );
        ?>
        <div class="palleon-control-wrap agama-control-wrap">
            <label class="palleon-control-label"><?php echo esc_html__('Variants', 'agama'); ?></label>
            <div class="palleon-control">
                <select id="agama-variants" class="palleon-select" name="agama-variants" autocomplete="off">
                    <option selected value="<?php echo esc_url($template); ?>" data-price="0"><?php echo esc_html($name); ?></option>
                    <?php 
                    $entries = get_post_meta( $product_id, 'agama_cmb2_print_areas', true );
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
                        echo '<option value="' . $area_template . '" data-price="' . $area_additional_price . '">' . $area . '</option>';
                    } ?>
                </select>
            </div>
        </div>
        <hr class="agama-hr">
        <?php } ?>
        <?php 
        if (!$product->is_sold_individually()) { 
            $quantity_max = '';
            if ($product->managing_stock()) {
                $quantity_max = $product->get_stock_quantity();
            }
        ?>
        <div id="agama-quantity-control" class="palleon-control-wrap">
            <label class="palleon-control-label"><?php echo esc_html__('Quantity', 'agama'); ?></label>
            <div class="palleon-control">
                <input id="agama-quantity" class="palleon-form-field" type="number" value="1" data-min="1" data-max="<?php echo esc_attr($quantity_max); ?>" autocomplete="off">
            </div>
        </div>
        <?php } ?>
        <?php if( $product->is_type( 'variable' ) ){
            $varAttributes = $product->get_variation_attributes();
            foreach( $attributes as $attribute ) {
                if (!empty($color_field) && $attribute["name"] === 'pa_' . $color_field) {
                $colors = wc_get_product_terms( $product->get_id(), $attribute["name"] ); ?>
                <div class="palleon-control-wrap agama-control-wrap">
                    <label class="palleon-control-label"><?php echo esc_html(wc_attribute_label( $attribute["name"], $product)); ?></label>
                    <div class="palleon-control">
                        <div id="<?php echo esc_attr($attribute["name"]); ?>" class="agama-swatches">
                        <?php foreach( $colors as $color ) {
                            $color_array = $varAttributes[$attribute["name"]];
                            if (in_array($color->slug, $color_array)) {
                                $hex_color = get_term_meta( $color->term_id, 'agama_color', true );
                                $selected = '';
                                if ($color->slug == $product->get_variation_default_attribute($attribute["name"])) {
                                    $selected = 'selected';
                                }
                                echo '<div class="agama-swatch ' . esc_attr($selected) . '" style="background-color:' . esc_attr($hex_color) . ';" title="' . esc_attr($color->name) . '" data-value="' . esc_attr($color->slug) . '" data-color="' . esc_attr($hex_color) . '"></div>';
                            }
                        } ?>
                        </div>
                    </div>
                </div>
                <?php } else { 
                    $values = wc_get_product_terms( $product->get_id(), $attribute["name"] ); 
                    ?>
                <div class="palleon-control-wrap agama-control-wrap">
                    <label class="palleon-control-label"><?php echo esc_html(wc_attribute_label( $attribute["name"], $product)); ?></label>
                    <div class="palleon-control">
                    <select id="<?php echo esc_attr($attribute["name"]); ?>" class="palleon-select agama-attribute" name="attribute_<?php echo esc_attr($attribute["name"]); ?>" data-attribute_name="attribute_<?php echo esc_attr($attribute["name"]); ?>" autocomplete="off">
                        <option value=""><?php echo esc_html__( 'Choose an option', 'agama'); ?></option>
                        <?php 
                        foreach( $values as $value ) {
                            $selected = '';
                            if ($value->slug == $product->get_variation_default_attribute($attribute["name"])) {
                                $selected = 'selected="selected"';
                            }
                            $value_array = $varAttributes[$attribute["name"]];
                            if (in_array($value->slug, $value_array)) {
                                echo '<option value="' . esc_attr($value->slug) . '" ' . esc_attr($selected) . '>' . esc_html($value->name) . '</option>';
                            }
                        } ?>
                    </select>
                    </div>
                </div>
                <?php }
            } ?>
            <hr class="agama-hr" />
        <?php } ?>
        <div class="product-content">
            <?php 
            echo '<h4>' . esc_html__( 'Description', 'agama' ) . '</h4>';
            echo wpautop($product->get_description()); 
            ?>
            <div class="product_meta notice">
            <?php do_action( 'woocommerce_product_meta_start' ); ?>

            <?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>

                <span class="sku_wrapper"><?php esc_html_e( 'SKU:', 'agama' ); ?> <span class="sku"><?php echo ( $sku = $product->get_sku() ) ? $sku : esc_html__( 'N/A', 'agama' ); ?></span></span>

            <?php endif; ?>

            <?php echo wc_get_product_category_list( $product->get_id(), ', ', '<span class="posted_in">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'agama' ) . ' ', '</span>' ); ?>

            <?php echo wc_get_product_tag_list( $product->get_id(), ', ', '<span class="tagged_as">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'agama' ) . ' ', '</span>' ); ?>

            <?php do_action( 'woocommerce_product_meta_end' ); ?>

            </div>

            <?php 
            $attachment_ids = $product->get_gallery_image_ids();
            if (!empty($attachment_ids)) {
                echo '<hr class="agama-hr" />';
                echo '<h4>' . esc_html__( 'Gallery', 'agama' ) . '</h4>';
                echo '<div id="agama-gallery" class="agama-templates">';
                foreach( $attachment_ids as $attachment_id ) {
                    $image_url = wp_get_attachment_url( $attachment_id );
                    $thumbnail_url = wp_get_attachment_image_src( $attachment_id, 'thumbnail' )[0]; ?>
                    <div class="agama-templates-item">
                        <div class="agama-templates-item-inner">
                            <a class="agama-lightbox" href="<?php echo esc_url($image_url); ?>">
                                <div class="palleon-img-wrap">
                                    <div class="palleon-img-loader"></div>
                                    <img class="lazy" data-src="<?php echo esc_url($thumbnail_url); ?>" />
                                </div>
                            </a>
                        </div>
                    </div>
                <?php }
                echo '</div>';
            }

            if (wc_review_ratings_enabled()) {
                $args = array ('post_id' => $product_id, 'post_type' => 'product');
                $comments = get_comments( $args );
                $reviews_pagination =  PalleonSettings::get_option('agama_reviews_pagination', 5);
                if (comments_open($product_id) && $comments) {
                    echo '<hr class="agama-hr" />';
                    echo '<h4 id="reviews">' . esc_html__( 'Reviews', 'agama' ) . '</h4>';
                    echo '<ol id="agama-reviews-list" class="commentlist paginated" data-perpage="' . $reviews_pagination . '">';
                    wp_list_comments( array( 'callback' => 'woocommerce_comments' ), $comments);
                    echo '</ol>';
                } else {
                    echo '<hr class="agama-hr" />';
                    echo '<div class="notice notice-warning">' . esc_html__( 'There are no reviews yet.', 'agama' ) . '</div>';
                }
                if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product_id ) ) {
                    echo '<h4 class="review_form_title">' . esc_html__( 'Add a review', 'agama' ) . '</h4>'; ?>
                    <div id="review_form_wrapper">
                        <div id="review_form">
                            <?php
                            $commenter    = wp_get_current_commenter();
                            $comment_form = array(
                                'title_reply'         => '',
                                'title_reply_to'      => esc_html__( 'Leave a Reply to %s', 'agama' ),
                                'title_reply_before'  => '<span id="reply-title" class="comment-reply-title">',
                                'title_reply_after'   => '</span>',
                                'comment_notes_after' => '',
                                'label_submit'        => esc_html__( 'Submit', 'agama' ),
                                'logged_in_as'        => '',
                                'comment_field'       => '',
                                'submit_button'       => '<input name="%1$s" type="submit" id="%2$s" class="palleon-btn primary %3$s" value="%4$s" />',
                            );
            
                            $name_email_required = (bool) get_option( 'require_name_email', 1 );
                            $fields              = array(
                                'author' => array(
                                    'label'    => esc_html__( 'Name', 'agama' ),
                                    'type'     => 'text',
                                    'value'    => $commenter['comment_author'],
                                    'required' => $name_email_required,
                                ),
                                'email'  => array(
                                    'label'    => esc_html__( 'Email', 'agama' ),
                                    'type'     => 'email',
                                    'value'    => $commenter['comment_author_email'],
                                    'required' => $name_email_required,
                                ),
                            );
            
                            $comment_form['fields'] = array();
            
                            foreach ( $fields as $key => $field ) {
                                $field_html  = '<p class="comment-form-' . esc_attr( $key ) . '">';
                                $field_html .= '<label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] );
            
                                if ( $field['required'] ) {
                                    $field_html .= '&nbsp;<span class="required">*</span>';
                                }
            
                                $field_html .= '</label><input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $field['type'] ) . '" value="' . esc_attr( $field['value'] ) . '" size="30" ' . ( $field['required'] ? 'required' : '' ) . ' /></p>';
            
                                $comment_form['fields'][ $key ] = $field_html;
                            }
            
                            $account_page_url = wc_get_page_permalink( 'myaccount' );
                            if ( $account_page_url ) {
                                $comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'agama' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
                            }
            
                            if ( wc_review_ratings_enabled() ) {
                                $comment_form['comment_field'] = '<div class="comment-form-rating"><label for="rating">' . esc_html__( 'Your rating', 'agama' ) . ( wc_review_ratings_required() ? '&nbsp;<span class="required">*</span>' : '' ) . '</label><select name="rating" id="rating" required>
                                    <option value="">' . esc_html__( 'Rate&hellip;', 'agama' ) . '</option>
                                    <option value="5">' . esc_html__( 'Perfect', 'agama' ) . '</option>
                                    <option value="4">' . esc_html__( 'Good', 'agama' ) . '</option>
                                    <option value="3">' . esc_html__( 'Average', 'agama' ) . '</option>
                                    <option value="2">' . esc_html__( 'Not that bad', 'agama' ) . '</option>
                                    <option value="1">' . esc_html__( 'Very poor', 'agama' ) . '</option>
                                </select></div>';
                            }
            
                            $comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Your review', 'agama' ) . '&nbsp;<span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" required></textarea></p>';
            
                            comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ), $product_id );
                            ?>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="notice notice-warning"><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'agama' ); ?></div>
                <?php }
            } ?>
        </div>
    <?php } ?>
</div>