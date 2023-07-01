(function($) {
    "use strict";
    $(document).ready(function () {
        var checked = $('#cmb2-metabox-agama_cmb2_custom_product_meta_box').find('input[name=agama_cmb2_additional_options]:checked').val();
        if (checked == 'disable') {
            $('#cmb2-metabox-agama_cmb2_custom_product_meta_box').find('.cmb2-id-agama-cmb2-print-areas').hide();
        } else {
            $('#cmb2-metabox-agama_cmb2_custom_product_meta_box').find('.cmb2-id-agama-cmb2-print-areas').show();
        }
    });
    $('#cmb2-metabox-agama_cmb2_custom_product_meta_box').find('input[name=agama_cmb2_additional_options]').change(function() {
        if (this.value == 'disable') {
            $('#cmb2-metabox-agama_cmb2_custom_product_meta_box').find('.cmb2-id-agama-cmb2-print-areas').hide();
        } else {
            $('#cmb2-metabox-agama_cmb2_custom_product_meta_box').find('.cmb2-id-agama-cmb2-print-areas').show();
        }
    });
})(jQuery);