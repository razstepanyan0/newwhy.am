(function($) {
    "use strict";
    $(document).ready(function () {
        $('#palleon').palleon({
            enableGLFiltering:(palleonParams.enableGLFiltering === "true"),
            textureSize: palleonParams.textureSize,
            version: palleonParams.version,
            watermark: 'none',
            customFunctions: function(selector, canvas, lazyLoadInstance, toastr) {
                // Dataurl to blob
                function agamaDataURLtoBlob(dataurl) {
                    var arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
                        bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
                    while(n--){
                        u8arr[n] = bstr.charCodeAt(n);
                    }
                    return new Blob([u8arr], {type:mime});
                }
                // Convert to data url
                function agamaConvertToDataURL(url, callback) {
                    var xhr = new XMLHttpRequest();
                    xhr.onload = function() {
                    var reader = new FileReader();
                    reader.onloadend = function() {
                        callback(reader.result);
                    };
                    reader.readAsDataURL(xhr.response);
                    };
                    xhr.open('GET', url);
                    xhr.responseType = 'blob';
                    xhr.send();
                }

                // Check if all objects are inside print area
                function isEverythingInside() {
                    var validCoords = true;
                    var print_objects = canvas.getObjects().filter(element => element.objectType != 'printarea');
                    if (print_objects.length !== 0) {
                        var print_a = canvas.getObjects().filter(element => element.objectType == 'printarea')[0];
                        if (print_a) {
                            $.each(print_objects, function( index, val ) {
                                if(!val.isContainedWithinObject(print_a) && val.objectType != 'clipPath') {
                                    validCoords = false;
                                    return false;
                                }
                            });
                        }
                    }
                    return validCoords;
                }

                // Check if the object is inside print area
                canvas.on('object:modified', function (e) {
                    var obj = e.target;
                    var print_a = canvas.getObjects().filter(element => element.objectType == 'printarea')[0];
                    if (print_a) {
                        if (obj.isContainedWithinObject(print_a)) {
                            $("#palleon-layers").find('#' + obj.id + ' > .material-icons:first-child').css('visibility', 'visible');
                            $("#palleon-layers").find('#' + obj.id).attr('title', '');
                            $("#palleon-layers").find('#' + obj.id).removeClass('not-inside');
                            $("#palleon-layers").find('#' + obj.id + ' > .warning').remove();
                        } else {
                            $("#palleon-layers").find('#' + obj.id + ' > .material-icons:first-child').css('visibility', 'hidden');
                            $("#palleon-layers").find('#' + obj.id).attr('title', agamaParams.outside);
                            $("#palleon-layers").find('#' + obj.id).addClass('not-inside');
                            $("#palleon-layers").find('#' + obj.id + ' > .warning').remove();
                            $("#palleon-layers").find('#' + obj.id).append('<span class="material-icons warning">warning</span>');
                        }
                    }
                });

                canvas.on('selection:cleared', function () {
                    var print_objects = canvas.getObjects().filter(element => element.objectType != 'printarea');
                    var print_a = canvas.getObjects().filter(element => element.objectType == 'printarea')[0];
                    if (print_objects.length !== 0) {
                        $.each(print_objects, function( index, val ) {
                            if (val.isContainedWithinObject(print_a)) {
                                $("#palleon-layers").find('#' + val.id + ' > .material-icons:first-child').css('visibility', 'visible');
                                $("#palleon-layers").find('#' + val.id).attr('title', '');
                                $("#palleon-layers").find('#' + val.id).removeClass('not-inside');
                                $("#palleon-layers").find('#' + val.id + ' > .warning').remove();
                            } else {
                                $("#palleon-layers").find('#' + val.id + ' > .material-icons:first-child').css('visibility', 'hidden');
                                $("#palleon-layers").find('#' + val.id).attr('title', agamaParams.outside);
                                $("#palleon-layers").find('#' + val.id).addClass('not-inside');
                                $("#palleon-layers").find('#' + val.id + ' > .warning').remove();
                                $("#palleon-layers").find('#' + val.id).append('<span class="material-icons warning">warning</span>');
                            }
                        });
                    }
                });

                /* Print area background color */
                selector.find('#agama-printarea-color').bind('change', function() {
                    var val = $(this).val();
                    var print_a = canvas.getObjects().filter(element => element.objectType == 'printarea')[0];
                    if (print_a) {
                        print_a.set("fill", val);
                        canvas.requestRenderAll();
                    }
                });

                /* Upload BG Image */
                selector.find('#agama-bg-img-upload').on('change', function (e) {
                    if ($(this).val() == '') {
                        return;
                    }
                    if($(this).data('max') && $(this).data('max') != '') {
                        if(this.files[0].size > parseInt($(this).data('max'))){
                            toastr.error(palleonParams.maxUploadSize + ' ' + parseInt($(this).data('max')) / 1048576 + 'MB.', palleonParams.error);
                            this.value = "";
                            return;
                        }
                    }
                    selector.find('#agama-bg-delete').trigger('click');
                    var reader = new FileReader();
                    reader.onload = function (event) {
                        var imgObj = new Image();
                        agamaConvertToDataURL(event.target.result, function(dataUrl) {
                            imgObj.src = dataUrl;
                            var print_a = canvas.getObjects().filter(element => element.objectType == 'printarea')[0];
                            if (print_a) {
                                imgObj.onload = function () {
                                    var img = new fabric.Image(imgObj);
                                    img.scaleToWidth(print_a.width / 2);
                                    var patternSourceCanvas = new fabric.StaticCanvas();
                                    patternSourceCanvas.setDimensions({
                                        width: img.getScaledWidth(),
                                        height: img.getScaledHeight(),
                                    });
                                    patternSourceCanvas.add(img);
                                    patternSourceCanvas.renderAll();
                                    var pattern = new fabric.Pattern({
                                        source: patternSourceCanvas.getElement(),
                                        repeat: 'repeat',
                                        offsetX: 0,
                                        offsetY: 0,
                                        angle: 0
                                    });
                                    print_a.clone(function(cloned) {
                                        cloned.set({
                                            id: new Date().getTime(),
                                            objectType: 'clipPath',
                                            objectCaching: false,
                                            fill: pattern,
                                            selectable: false,
                                            lockMovementX: true,
                                            lockMovementY: true,
                                            lockRotation: true,
                                            erasable: false,
                                            stroke: 'transparent',
                                            strokeWidth: 0
                                        });
                                        canvas.add(cloned);
                                        canvas.sendToBack(cloned);
                                        canvas.sendToBack(print_a);
                                    });
                                    selector.find('#agama-bg-width').val(Math.round(parseInt(img.getScaledWidth())));
                                    selector.find('#agama-bg-width').attr('min', Math.round(parseInt(img.getScaledWidth()) / 4));
                                    selector.find('#agama-bg-width').attr('max', Math.round(parseInt(img.getScaledWidth()) * 4));
                                    selector.find('#agama-bg-offset-x').val(0);
                                    selector.find('#agama-bg-offset-x').attr('max', Math.round(parseInt(img.getScaledWidth()) * 4));
                                    selector.find('#agama-bg-offset-y').val(0);
                                    selector.find('#agama-bg-offset-y').attr('max', Math.round(parseInt(img.getScaledHeight()) * 4));
                                    selector.find('#agama-bg-width').trigger('input');
                                    selector.find('#agama-bg-offset-x').trigger('input');
                                    selector.find('#agama-bg-offset-y').trigger('input');
                                    selector.find('#agama-bg-image-settings').show();
                                    selector.find('#agama-bg-delete').show();

                                    document.getElementById('agama-bg-width').oninput = function () {
                                        img.scaleToWidth(parseInt(this.value, 10));
                                        patternSourceCanvas.setDimensions({
                                          width: img.getScaledWidth(),
                                          height: img.getScaledHeight(),
                                        });
                                        canvas.requestRenderAll();
                                    };
                                    document.getElementById('agama-bg-offset-x').oninput = function () {
                                        pattern.offsetX = parseInt(this.value, 10);
                                        canvas.requestRenderAll();
                                    };
                                    document.getElementById('agama-bg-offset-y').oninput = function () {
                                        pattern.offsetY = parseInt(this.value, 10);
                                        canvas.requestRenderAll();
                                    };
                                    setTimeout(function(){ 
                                        canvas.requestRenderAll();
                                        selector.find('#palleon-canvas-loader').hide();
                                    }, 500);
                                };
                            }
                        });
                    };
                    reader.readAsDataURL(e.target.files[0]);
                    canvas.fire('palleon:history', { type: 'image', text: palleonParams.added });
                });

                /* Delete BG Image */
                selector.find('#agama-bg-delete').on('click', function() {
                    var bg_img = canvas.getObjects().filter(element => element.objectType == 'clipPath')[0];
                    if (bg_img) {
                        $(this).hide();
                        selector.find('#agama-bg-image-settings').hide();
                        canvas.remove(bg_img);
                        canvas.requestRenderAll();
                    }
                });

                /* Lightbox */
                selector.find('.agama-lightbox').featherlightGallery({
                    previousIcon: 'arrow_back_ios_new',
                    nextIcon: 'arrow_forward_ios',
                    closeIcon:"close",
                    galleryFadeIn: 100,
                    galleryFadeOut: 300
                });

                /* Color swatches */
                selector.on('click', '.agama-swatch', function ( e ) {
                    var el = $(this);
                    var select = el.closest('.value').find('select');
                    var value = el.data('value');
                    var color = el.data('color');
                    el.addClass('selected').siblings('.selected').removeClass('selected');
                    select.val(value);
                    select.change();
                    canvas.backgroundColor = color;
                    canvas.requestRenderAll();
                    setPrice();
                } );

                /* Other attributes */
                selector.find('#palleon-adjust select.agama-attribute').change(function(){
                    setPrice();
                });

                /* Filter templates */
                selector.find('#agama-templates-tag').on('change', function () {
                    var searchTerm = $(this).val();
                    if (searchTerm == '') {
                        selector.find('#agama-templates .agama-templates-item').show();
                    } else {
                        selector.find('#agama-templates .agama-templates-item').hide();
                        selector.find('#agama-templates .agama-templates-item').each(function(index, value) {
                            if($.inArray(parseInt(searchTerm), $(this).data('category')) !== -1) {
                                $(this).show();
                            }
                        });
                    }
                });

                /* Add Template */
                selector.find('#agama-templates').on('click','.agama-templates-item',function(){
                    var json = $(this).attr('data-json');
                    selector.find('#palleon-canvas-loader').css('display', 'flex');
                    canvas.getObjects().filter(element => element.objectType != 'printarea').forEach(element => canvas.remove(element));
                    selector.find("#palleon-layers > li:not(.layer-printarea)").remove();
                    $.getJSON(json, function(json) {
                        for (var i = 0; i < json.objects.length; i++) {
                            if (json.objects[i].objectType == 'textbox') {
                                json.objects[i].fontFamily = json.objects[i].fontFamily + '-palleon';
                                json.objects[i].realFontSize = json.objects[i].fontSize;
                                json.objects[i].fontSize = 1;
                            }
                        }
                        fabric.util.enlivenObjects(json.objects, function(objects) {
                            var origRenderOnAddRemove = canvas.renderOnAddRemove;
                            canvas.renderOnAddRemove = false;
                            var textboxes = objects.filter(element => element.objectType == 'textbox');
                            $(document).trigger( "loadTemplateFonts", [textboxes] );
                            objects.forEach(function(o) {
                                canvas.add(o);
                            });
                            var selected = canvas.getObjects().filter(element => element.objectType != 'printarea');
                            var printarea = canvas.getObjects().filter(element => element.objectType == 'printarea')[0];
                            var sel = new fabric.ActiveSelection(selected, {
                            canvas: canvas
                            });
                            canvas.setActiveObject(sel);
                            var group = canvas.getActiveObject();
                            group.scaleToWidth((printarea.width * 0.9) * canvas.getZoom());
                            if (group.getScaledHeight() >= (printarea.height * canvas.getZoom())) {
                                group.scaleToHeight((printarea.height * 0.5) * canvas.getZoom());
                            }
                            group.set("originX", "left");
                            group.set("originY", "top");
                            group.set("top", printarea.top - (printarea.height / 2) + ((printarea.getScaledHeight() - group.getScaledHeight()) / 2));
                            group.set("left", printarea.left - (printarea.width / 2) + ((printarea.getScaledWidth() - group.getScaledWidth()) / 2));
                            canvas.discardActiveObject();
                            canvas.renderOnAddRemove = origRenderOnAddRemove;
                            canvas.requestRenderAll(); 
                          });
                    }).fail(function(jqxhr, textStatus, error) {
                        toastr.error("Request Failed: " + error, palleonParams.error);
                    }).always(function() {
                        selector.find('#palleon-canvas-loader').hide();
                    }); 
                });

                /* Upload Template */
                selector.find('#agama-upload-template').on('change', function (e) {
                    selector.find('#palleon-canvas-loader').css('display', 'flex');
                    selector.find('#agama-bg-delete, #agama-bg-image-settings').hide();
                    var reader = new FileReader();
                    var json = '';
                    reader.onload = function(ev) {
                        json = JSON.parse(reader.result);
                        var bgColor = json.backgroundColor;
                        canvas.getObjects().filter(element => element.objectType != 'printarea').forEach(element => canvas.remove(element));
                        selector.find("#palleon-layers > li:not(.layer-printarea)").remove();
                        for (var i = 0; i < json.objects.length; i++) {
                            if (json.objects[i].objectType == 'textbox') {
                                json.objects[i].fontFamily = json.objects[i].fontFamily + '-palleon';
                                json.objects[i].realFontSize = json.objects[i].fontSize;
                                json.objects[i].fontSize = 1;
                            }
                        }
                        fabric.util.enlivenObjects(json.objects, function(objects) {
                            var origRenderOnAddRemove = canvas.renderOnAddRemove;
                            canvas.renderOnAddRemove = false;
                            var textboxes = objects.filter(element => element.objectType == 'textbox');
                            $(document).trigger( "loadTemplateFonts", [textboxes] );
                            objects.forEach(function(o) {
                                canvas.add(o);
                            });
                            var selected = canvas.getObjects().filter(element => element.objectType != 'printarea');
                            var printarea = canvas.getObjects().filter(element => element.objectType == 'printarea')[0];
                            printarea.set('fill', bgColor);
                            selector.find('#agama-printarea-color').spectrum("set", bgColor);
                            var sel = new fabric.ActiveSelection(selected, {
                            canvas: canvas
                            });
                            canvas.setActiveObject(sel);
                            var group = canvas.getActiveObject();
                            group.scaleToWidth((printarea.width * 0.9) * canvas.getZoom());
                            if (group.getScaledHeight() >= (printarea.height * canvas.getZoom())) {
                                group.scaleToHeight((printarea.height * 0.5) * canvas.getZoom());
                            }
                            group.set("originX", "left");
                            group.set("originY", "top");
                            group.set("top", printarea.top - (printarea.height / 2) + ((printarea.getScaledHeight() - group.getScaledHeight()) / 2));
                            group.set("left", printarea.left - (printarea.width / 2) + ((printarea.getScaledWidth() - group.getScaledWidth()) / 2));
                            canvas.discardActiveObject();
                            canvas.renderOnAddRemove = origRenderOnAddRemove;
                            canvas.requestRenderAll(); 
                            selector.find('#palleon-canvas-loader').hide();
                        });
                    };
                    reader.readAsText(e.target.files[0]);
                });

                /* Download Template */
                selector.find('#agama-download-template').on('click', function() {
                    var name = selector.find('.agama-product-title').attr('data-slug');
                    canvas.clone(function(canvas) {
                        var print_objects = canvas.getObjects().filter(element => element.objectType != 'printarea');
                        if (print_objects.length !== 0) {
                            var designData = '';
                            var print_a = canvas.getObjects().filter(element => element.objectType == 'printarea')[0];
                            if (print_a) {
                                var bgColor = print_a.fill;
                                if (isEverythingInside()) {
                                    canvas.discardActiveObject();
                                    print_a.set('strokeWidth', 0);
                                    var sel = new fabric.ActiveSelection(canvas.getObjects().filter(element => element.objectType != 'clipPath'), {
                                    canvas: canvas
                                    });
                                    canvas.setActiveObject(sel);
                                    var group = canvas.getActiveObject();
        
                                    var newCanvas = document.createElement("canvas");
                                    var tempCanvas = new fabric.Canvas(newCanvas);
                                    tempCanvas.setWidth(group.width);
                                    tempCanvas.setHeight(group.height);
                                    var tempImg = tempCanvas.toDataURL({ format: 'png', enableRetinaScaling: false});
                                    var tempImgBlob = agamaDataURLtoBlob(tempImg);
                                    var tempURL = URL.createObjectURL(tempImgBlob);
        
                                    fabric.Image.fromURL(tempURL, function(img) {
                                        tempCanvas.setBackgroundImage(img, tempCanvas.renderAll.bind(tempCanvas), {
                                            objectType: 'BG',
                                            mode: 'canvas',
                                            top: 0, 
                                            left: 0,
                                            scaleX: 1,
                                            scaleY: 1,
                                            selectable: false,
                                            angle: 0, 
                                            originX: 'left', 
                                            originY: 'top',
                                            lockMovementX: true,
                                            lockMovementY: true,
                                            lockRotation: true,
                                            erasable: false
                                        }, { crossOrigin: 'anonymous' });
                                        
                                        tempCanvas.add(group);
                                        
                                        designData = tempCanvas.toJSON(['objectType','gradientFill','roundedCorders','mode','selectable','lockMovementX','lockMovementY','lockRotation','crossOrigin','layerName']);
                                        designData.backgroundImage.src = tempImg;
                                        designData.backgroundColor = bgColor;
                                        designData.objects = designData.objects[0].objects;
        
                                        $.each(designData.objects, function( index, val ) {
                                            if (this.objectType == 'printarea') {
                                                designData.objects.splice(index,1);
                                                return false; 
                                            }
                                        });
                                        $.each(designData.objects, function( index, val ) {
                                            this.top = this.top - (group.top);
                                            this.left = this.left - (group.left);
                                        });

                                        designData = JSON.stringify(designData);
                                        tempCanvas.dispose();

                                        var a = document.createElement("a");
                                        var file = new Blob([designData], { type: "text/plain" });
                                        a.href = URL.createObjectURL(file);
                                        a.download = name + '.json';
                                        a.click();

                                        canvas.discardActiveObject();
                                        canvas.requestRenderAll();
                                    });
                                } else {
                                    toastr.error(agamaParams.outside, palleonParams.error);
                                    return false;
                                } 
                            }
                        } else {
                            toastr.error(agamaParams.nothingToPrint, palleonParams.error);
                        }
                     },['objectType','gradientFill','roundedCorders','mode','selectable','lockMovementX','lockMovementY','lockRotation','crossOrigin','layerName']);

                });

                /* Variants */
                selector.find('#agama-variants').change(function(){
                    var url = $(this).val();
                    var price = $(this).find('option:selected').attr('data-price');
                    var area = $(this).find('option:selected').text();

                    canvas.getObjects().filter(element => element.objectType != 'printarea').forEach(element => canvas.remove(element));
                    selector.find("#palleon-layers > li:not(.layer-printarea)").remove();

                    $('#area-btn-1').attr('data-price', price);
                    $('#area-btn-1').attr('data-val', price);
                    $('#area-btn-1').attr('data-json', url);
                    $('#area-btn-1').attr('data-area', area);

                    $('#area-btn-1').trigger('click');
                });

                /* Set price */
                function setPrice() {
                    if (typeof agamaVariations !== 'undefined') {
                        var status = false;
                        var selectedVariations = {};
                        $('#palleon-adjust').find('.palleon-control').each(function() {
                            var control = $(this).find(":first");
                            var slug = control.attr('id');
                            var selected = '';
                            if (control.hasClass('agama-swatches')) {
                                selected = control.find('.selected').attr('data-value');
                                selectedVariations['attribute_' + slug] = selected;
                            } else if(control.hasClass('agama-attribute')) {
                                selected = control.val();
                                selectedVariations['attribute_' + slug] = selected;
                            }
                        });
                        $.each(agamaVariations, function( index, val ) {
                            if (JSON.stringify(val.attributes) === JSON.stringify(selectedVariations)) {
                                if (val.is_in_stock) {
                                    var notice = '';
                                    if (val.availability_html && val.availability_html != '') {
                                        notice = val.availability_html;
                                    }
                                    selector.find('.product_meta .sku').html(val.sku);
                                    status = true;
                                    if (notice != '') {
                                        selector.find('#agama-product-price-notice').html(notice);
                                    }
                                    selector.find('#agama-product-price').html(val.price_html);
                                    selector.find('#agama-add-to-cart').attr('data-variation', val.variation_id);
                                    selector.find('#agama-add-to-cart').attr('data-price', val.display_price);
                                    if (val.max_qty != '') {
                                        selector.find('#agama-quantity').data('max', val.max_qty);
                                        if (selector.find('#agama-quantity').val() > val.max_qty) {
                                            selector.find('#agama-quantity').val(val.max_qty);
                                        }
                                    }
                                    return false;
                                }
                            }
                        });
                        if (status === false) {
                            selector.find('.product_meta .sku').html('N/A');
                            selector.find('#agama-product-price').html('<div class="notice notice-danger">' + agamaParams.outofstock + '</div>');
                            selector.find('#agama-additional-fee').hide();
                            selector.find('#agama-product-price-notice').hide();
                            selector.find('#agama-product-price-total').hide();
                            selector.find('#agama-product-price').addClass('outofstock');
                            selector.find('#agama-quantity-control').hide();
                        } else {
                            selector.find('#agama-additional-fee').show();
                            selector.find('#agama-product-price-notice').show();
                            selector.find('#agama-product-price-total').show();
                            selector.find('#agama-product-price').removeClass('outofstock');
                            selector.find('#agama-quantity-control').show();
                            setAdditionalFee();
                        }
                    }
                }
                setPrice();

                // Add thousand seperator
                function addThousandSeparator(nStr) {
                    nStr += '';
                    var x = nStr.split('.');
                    var x1 = x[0];
                    var x2 = x.length > 1 ? '.' + x[1] : '';
                    var rgx = /(\d+)(\d{3})/;
                    while (rgx.test(x1)) {
                        x1 = x1.replace(rgx, '$1' + agamaParams.thousandSeparator + '$2');
                    }
                    return x1 + x2;
                }
            
                // Set Additional Fee
                function setAdditionalFee() {
                    var fee = 0;
                    var currentPrice = parseInt(selector.find('#agama-add-to-cart').attr('data-price'));
                    selector.find('#agama-print-areas .palleon-btn').each(function() {
                        if ($(this).attr('data-val') !== '0') {
                            fee = fee + parseFloat($(this).attr('data-val'));
                        }
                    });
                    if (fee !== 0) {
                        var feeString = fee.toFixed(parseInt(agamaParams.numberOfDecimals)).replace(".", agamaParams.decimalSeparator);
                        if (agamaParams.currencyPosition == 'left') {
                            selector.find('#agama-additional-fee').html(' + ' + agamaParams.currencySymbol + addThousandSeparator(feeString));
                        } else if (agamaParams.currencyPosition == 'right') {
                            selector.find('#agama-additional-fee').html(' + ' + addThousandSeparator(feeString) + agamaParams.currencySymbol);
                        } else if (agamaParams.currencyPosition == 'left_space') {
                            selector.find('#agama-additional-fee').html(' + ' + agamaParams.currencySymbol + ' ' + addThousandSeparator(feeString));
                        } else if (agamaParams.currencyPosition == 'right_space') {
                            selector.find('#agama-additional-fee').html(' + ' + addThousandSeparator(feeString) + ' ' + agamaParams.currencySymbol);
                        }
                        var total = parseFloat(currentPrice + fee);
                        var totalString = total.toFixed(parseInt(agamaParams.numberOfDecimals)).replace(".", agamaParams.decimalSeparator);
                        if (agamaParams.currencyPosition == 'left') {
                            selector.find('#agama-product-price-total').html('Total: ' + agamaParams.currencySymbol + addThousandSeparator(totalString));
                        } else if (agamaParams.currencyPosition == 'right') {
                            selector.find('#agama-product-price-total').html('Total: ' + addThousandSeparator(totalString) + agamaParams.currencySymbol);
                        } else if (agamaParams.currencyPosition == 'left_space') {
                            selector.find('#agama-product-price-total').html('Total: ' + agamaParams.currencySymbol + ' ' + addThousandSeparator(totalString));
                        } else if (agamaParams.currencyPosition == 'right_space') {
                            selector.find('#agama-product-price-total').html('Total: ' + addThousandSeparator(totalString) + ' ' + agamaParams.currencySymbol);
                        }
                    } else {
                        selector.find('#agama-additional-fee').html('');
                        selector.find('#agama-product-price-total').html('');
                    }
                    selector.find('#agama-additional-fee').attr('data-fee', fee);
                }
            
                /* Print Areas */
                selector.find('#agama-print-areas > .palleon-btn').each(function() {
                    var id = $(this).attr('id');
                    $('body').append('<script id="' + id + '-json" type="text/json"></script>');
                    $('body').append('<script id="' + id + '-design" type="text/json"></script>');
                });

                selector.find('#agama-print-areas .palleon-btn').on("click", function (event) {
                    selector.find('#palleon-canvas-loader').css('display', 'flex');
                    selector.find('#palleon-history-list li').remove();
                    selector.find('#palleon-history').prop('disabled', true);
                    selector.find('#palleon-undo').prop('disabled', true);
                    selector.find('#palleon-redo').prop('disabled', true);
                    var selectedID = selector.find('#agama-print-areas .palleon-btn.selected').attr('id');
                    var selected = $('#' + selectedID);
                    var print_objects = canvas.getObjects().filter(element => element.objectType != 'printarea');
                    if (print_objects.length !== 0) {
                        var imgData = '';
                        var designData = '';
                        var print_a = canvas.getObjects().filter(element => element.objectType == 'printarea')[0];
                        if (print_a) {
                            var bgColor = print_a.fill;
                            if (isEverythingInside()) {
                                if (selected.attr('data-price')) {
                                    var price = selected.attr('data-price');
                                    selected.attr('data-val', price);
                                    if (agamaParams.currencyPosition == 'left') {
                                        selected.find('span').html(agamaParams.currencySymbol + addThousandSeparator(price.replace(".", agamaParams.decimalSeparator)));
                                    } else if (agamaParams.currencyPosition == 'right') {
                                        selected.find('span').html(addThousandSeparator(price.replace(".", agamaParams.decimalSeparator)) + agamaParams.currencySymbol);
                                    } else if (agamaParams.currencyPosition == 'left_space') {
                                        selected.find('span').html(agamaParams.currencySymbol + ' ' + addThousandSeparator(price.replace(".", agamaParams.decimalSeparator)));
                                    } else if (agamaParams.currencyPosition == 'right_space') {
                                        selected.find('span').html(addThousandSeparator(price.replace(".", agamaParams.decimalSeparator)) + ' ' + agamaParams.currencySymbol);
                                    }
                                    setAdditionalFee();
                                }
                                var json = canvas.toJSON(['objectType','gradientFill','roundedCorders','mode','selectable','lockMovementX','lockMovementY','lockRotation','crossOrigin','layerName']);
                                $('body').find('#' + selectedID + '-json').html(JSON.stringify(json));
                                var zoom = canvas.getZoom();
                                var width = selector.find('#palleon-img-width').html();
                                var height = selector.find('#palleon-img-height').html();
                                canvas.setZoom(1);
                                selector.find('#palleon-img-zoom').val(100);
                                canvas.setWidth(width);
                                canvas.setHeight(height);
                                canvas.discardActiveObject();
                                print_a.set('strokeWidth', 0);
                                var sel = new fabric.ActiveSelection(canvas.getObjects(), {
                                canvas: canvas
                                });
                                canvas.setActiveObject(sel);
                                var group = canvas.getActiveObject();
                                
                                imgData = group.toDataURL({ format: 'png', quality: '1.0', multiplier: parseFloat(agamaParams.multiplier)});
                                imgData = changeDpiDataUrl(imgData, agamaParams.dpi);
    
                                var newCanvas = document.createElement("canvas");
                                var tempCanvas = new fabric.Canvas(newCanvas);
                                tempCanvas.setWidth(group.width);
                                tempCanvas.setHeight(group.height);
                                var tempImg = tempCanvas.toDataURL({ format: 'png', enableRetinaScaling: false});
                                var tempImgBlob = agamaDataURLtoBlob(tempImg);
                                var tempURL = URL.createObjectURL(tempImgBlob);
    
                                fabric.Image.fromURL(tempURL, function(img) {
                                    tempCanvas.setBackgroundImage(img, tempCanvas.renderAll.bind(tempCanvas), {
                                        objectType: 'BG',
                                        mode: 'canvas',
                                        top: 0, 
                                        left: 0,
                                        scaleX: 1,
                                        scaleY: 1,
                                        selectable: false,
                                        angle: 0, 
                                        originX: 'left', 
                                        originY: 'top',
                                        lockMovementX: true,
                                        lockMovementY: true,
                                        lockRotation: true,
                                        erasable: false
                                    }, { crossOrigin: 'anonymous' });
                                    
                                    tempCanvas.add(group);
                                    
                                    designData = tempCanvas.toJSON(['objectType','gradientFill','roundedCorders','mode','selectable','lockMovementX','lockMovementY','lockRotation','crossOrigin','layerName']);
                                    designData.backgroundImage.src = tempImg;
                                    designData.backgroundColor = bgColor;
                                    designData.objects = designData.objects[0].objects;
    
                                    $.each(designData.objects, function( index, val ) {
                                        if (this.objectType == 'printarea') {
                                            designData.objects.splice(index,1);
                                            return false; 
                                        }
                                    });
                                    $.each(designData.objects, function( index, val ) {
                                        this.top = this.top - (group.top);
                                        this.left = this.left - (group.left);
                                    });
    
                                    $('body').find('#' + selectedID + '-design').html(JSON.stringify(designData));
                                    tempCanvas.dispose();
                                });
    
                                canvas.discardActiveObject();
                                canvas.setZoom(zoom);
                                selector.find('#palleon-img-zoom').val(zoom * 100);
                                canvas.setWidth(width * zoom);
                                canvas.setHeight(height * zoom);
                                canvas.requestRenderAll();
                                selected.attr('data-img', imgData);

                            } else {
                                toastr.error(agamaParams.outside, palleonParams.error);
                                selector.find('#palleon-canvas-loader').hide();
                                return false;
                            } 
                        }
                    } else {
                        $('body').find('#' + selectedID + '-json').html('');
                        $('body').find('#' + selectedID + '-design').html('');
                        selected.attr('data-img', '');
                        selected.find('span').html('');
                        if (!selector.find('#agama-variants').length) {
                            selected.attr('data-val', '0');
                        }
                        setAdditionalFee();
                    }
                    selector.find('#agama-print-areas .palleon-btn').removeClass('selected');
                    selector.find('#agama-print-areas .palleon-btn').removeClass('selectedbefore');
                    selected.addClass('selectedbefore');
                    $(this).addClass('selected');
                    var getjson = $('body').find('#' + $(this).attr('id') + '-json').html();
                    var templateBG = '';
                    if (selector.find('.agama-swatch.selected').length) {
                        templateBG = selector.find('.agama-swatch.selected').attr('data-color');
                    }
                    $( document ).trigger( "loadTemplate", [ getjson,  $(this).attr('data-json'), templateBG] );
                });

                canvas.on('palleon:templateLoaded', function(e) {
                    var print_a = canvas.getObjects().filter(element => element.objectType == 'printarea')[0];
                    if (print_a) {
                        selector.find('#agama-printarea-color').spectrum("set", print_a.fill);
                    }
                    var clipPath = canvas.getObjects().filter(element => element.objectType == 'clipPath')[0];
                    if (clipPath) {
                        selector.find('#agama-bg-delete').show();
                    } else {
                        selector.find('#agama-bg-delete').hide();
                    }
                    selector.find('#agama-bg-image-settings').hide();
                    selector.find('#agama-main-loader').hide();
                });

                selector.find('#agama-print-areas .palleon-btn:first').trigger('click');

                /* Add To Cart */
                selector.find('#agama-add-to-cart').on("click", function () {
                    var answer = window.confirm(agamaParams.areYouSure);
                    if (answer) {
                        if (isEverythingInside()) {
                            selector.find('#agama-main-loader').show();
                            if (selector.find('#agama-product-price').hasClass('outofstock')) {
                                selector.find('#agama-main-loader').fadeOut(200);
                                toastr.error(agamaParams.outofstock, palleonParams.error);
                                return;
                            }
                            var title = selector.find('#palleon-adjust .agama-product-title').html();
                            var slug = selector.find('#palleon-adjust .agama-product-title').attr('data-slug');
                            var product = selector.find('#agama-add-to-cart').attr('data-product');
                            var variation = selector.find('#agama-add-to-cart').attr('data-variation');
                            var quantity = '1';
                            if (selector.find('#agama-quantity').length && selector.find('#agama-quantity').val() != '') {
                                quantity = selector.find('#agama-quantity').val();
                            }

                            $.when(selector.find('#agama-print-areas > .palleon-btn.selectedbefore').trigger('click')).then(function(){
                                var checkimg = false;
                                selector.find('#agama-print-areas > .palleon-btn').each(function() {
                                    if ($(this).attr('data-img') != '') {
                                        checkimg = true;
                                    }
                                });
                                if (checkimg === true) {
                                    setTimeout(function(){
                                        var imgArray = [];
                                        var designArray = [];
                                        var form_data = new FormData();
        
                                        selector.find('#agama-print-areas > .palleon-btn').each(function() {
                                            var imgData = $(this).attr('data-img');
                                            if (imgData != '') {
                                                var id = $(this).attr('id');
                                                var area = $(this).attr('data-area');
                                                var designData = $('body').find('#' + id + '-design').html();
                                                designData = designData.replace(/\/+$/, '');
                                                var blob = agamaDataURLtoBlob(imgData);
                                                imgArray.push({"area": area, "img": imgData, "type": blob.type});
                                                designArray.push({"area": area, "json": designData});
                                            }
                                        });
        
                                        form_data.append('title', title);
                                        form_data.append('slug', slug);
                                        form_data.append('images', JSON.stringify(imgArray));
                                        form_data.append('designs', JSON.stringify(designArray));
                                        form_data.append('product', product);
                                        form_data.append('variation', variation);
                                        form_data.append('fee', $('#agama-additional-fee').attr('data-fee'));
                                        form_data.append('quantity', quantity);
                                        form_data.append('userid', palleonParams.userid);
                                        form_data.append('action', 'agamaMakeOrder');
                                        form_data.append('nonce', palleonParams.nonce);
        
                                        $.ajax({
                                            url: palleonParams.ajaxurl,
                                            type: 'POST',
                                            contentType: false,
                                            processData: false,
                                            data: form_data,
                                            success: function (response) {
                                                selector.find('#agama-print-areas > .palleon-btn:first').trigger('click');
                                                selector.find('#agama-main-loader').fadeOut(200);
                                                toastr.success(agamaParams.addedToCart + '</br><a href="' + agamaParams.cartUrl + '" target="_blank">' + agamaParams.viewCart + '</a>', palleonParams.success);
                                                selector.find('#palleon-cart-count').html(response);
                                            },
                                            error: function(jqXHR,error, errorThrown) {
                                                selector.find('#agama-print-areas > .palleon-btn:first').trigger('click');
                                                selector.find('#agama-main-loader').fadeOut(200);
                                                if(jqXHR.status&&jqXHR.status==400){
                                                    toastr.error(jqXHR.responseText, palleonParams.error);
                                                }else{
                                                    toastr.error(palleonParams.wrong, palleonParams.error);
                                                }
                                            }
                                        });
                                    }, 1000);
                                } else {
                                    selector.find('#agama-main-loader').fadeOut(200);
                                    toastr.error(agamaParams.nothingToPrint, palleonParams.error);
                                }
                            });   
                        } else {
                            toastr.error(agamaParams.outside, palleonParams.error);
                            return false;
                        }
                    }
                });
            }
        });
    });
})(jQuery);