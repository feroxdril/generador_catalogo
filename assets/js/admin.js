/**
 * WFX Wholesale Catalog - Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Actualizar contador de productos seleccionados
        function updateSelectedCount() {
            var count = $('.wfx-product-select:checked').length;
            $('#wfx-selected-count').text(count);
        }
        
        // Inicializar contador
        updateSelectedCount();
        
        // Cambio en checkboxes de productos
        $(document).on('change', '.wfx-product-select', function() {
            updateSelectedCount();
        });
        
        // Seleccionar/Deseleccionar todos
        $('#wfx-select-all').on('change', function() {
            var isChecked = $(this).is(':checked');
            $('.wfx-product-select:visible').each(function() {
                $(this).prop('checked', isChecked);
            });
            updateSelectedCount();
        });
        
        // Búsqueda en tiempo real
        $('#wfx-product-search').on('keyup', function() {
            var searchTerm = $(this).val().toLowerCase();
            
            $('.wfx-product-item').each(function() {
                var productName = $(this).find('h3').text().toLowerCase();
                var productSku = $(this).find('.wfx-product-sku').text().toLowerCase();
                var productText = productName + ' ' + productSku;
                
                if (productText.indexOf(searchTerm) > -1) {
                    $(this).removeClass('hidden');
                } else {
                    $(this).addClass('hidden');
                }
            });
            
            // Actualizar el estado del checkbox "Seleccionar todos"
            var visibleChecked = $('.wfx-product-select:visible:checked').length;
            var visibleTotal = $('.wfx-product-select:visible').length;
            $('#wfx-select-all').prop('checked', visibleTotal > 0 && visibleChecked === visibleTotal);
        });
        
        // Auto-guardar precio mayorista al cambiar
        $('.wfx-wholesale-price').on('change', function() {
            const productId = $(this).data('product-id');
            const price = $(this).val();
            
            $.ajax({
                url: wfxWholesale.ajax_url,
                type: 'POST',
                data: {
                    action: 'wfx_save_wholesale_price',
                    nonce: wfxWholesale.nonce,
                    product_id: productId,
                    price: price
                }
            });
        });
        
        // Guardar selección
        $('#wfx-save-selection').on('click', function() {
            var button = $(this);
            var originalText = button.text();
            
            button.prop('disabled', true).text('Guardando...');
            
            // Recopilar productos seleccionados
            var productIds = [];
            $('.wfx-product-select:checked').each(function() {
                productIds.push($(this).val());
            });
            
            // Recopilar precios mayoristas
            var prices = {};
            $('.wfx-wholesale-price-input').each(function() {
                var input = $(this);
                var productId = input.attr('name').match(/\[(\d+)\]/)[1];
                var price = input.val();
                if (price && price > 0) {
                    prices[productId] = price;
                }
            });
            
            // Enviar por AJAX
            $.ajax({
                url: wfxWholesale.ajax_url,
                type: 'POST',
                data: {
                    action: 'wfx_save_selection',
                    nonce: wfxWholesale.nonce,
                    product_ids: productIds,
                    prices: prices
                },
                success: function(response) {
                    if (response.success) {
                        showMessage('Selección guardada exitosamente', 'success');
                    } else {
                        showMessage('Error: ' + response.data.message, 'error');
                    }
                },
                error: function() {
                    showMessage('Error de conexión al guardar la selección', 'error');
                },
                complete: function() {
                    button.prop('disabled', false).text(originalText);
                }
            });
        });
        
        // Generar catálogo PDF
        $('#wfx-generate-catalog').on('click', function() {
            var button = $(this);
            
            // Validar que haya productos seleccionados
            var selectedProducts = [];
            $('.wfx-product-select:checked').each(function() {
                selectedProducts.push($(this).val());
            });
            
            if (selectedProducts.length === 0) {
                alert('Por favor seleccione al menos un producto para generar el catálogo.');
                return;
            }
            
            // Recopilar precios mayoristas
            var prices = {};
            $('.wfx-wholesale-price-input').each(function() {
                var input = $(this);
                var productId = input.attr('name').match(/\[(\d+)\]/)[1];
                var price = input.val();
                if (price && price > 0) {
                    prices[productId] = price;
                }
            });
            
            // Mostrar loading overlay
            $('#wfx-loading-overlay').fadeIn();
            button.prop('disabled', true);
            
            // Enviar por AJAX
            $.ajax({
                url: wfxWholesale.ajax_url,
                type: 'POST',
                data: {
                    action: 'wfx_generate_catalog',
                    nonce: wfxWholesale.nonce,
                    product_ids: selectedProducts,
                    prices: prices
                },
                success: function(response) {
                    $('#wfx-loading-overlay').fadeOut();
                    
                    if (response.success) {
                        showMessage('Catálogo generado exitosamente', 'success');
                        
                        // Abrir PDF en nueva ventana
                        if (response.data.pdf_url) {
                            window.open(response.data.pdf_url, '_blank');
                        }
                    } else {
                        showMessage('Error: ' + response.data.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    $('#wfx-loading-overlay').fadeOut();
                    showMessage('Error de conexión al generar el catálogo: ' + error, 'error');
                },
                complete: function() {
                    button.prop('disabled', false);
                }
            });
        });
        
        // Guardar configuración
        $('#wfx-settings-form').on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var submitButton = form.find('button[type="submit"]');
            var originalText = submitButton.text();
            
            submitButton.prop('disabled', true).text('Guardando...');
            
            // Preparar datos
            var formData = {
                action: 'wfx_save_settings',
                nonce: form.find('#wfx_settings_nonce').val(),
                company_name: form.find('#company_name').val(),
                company_logo: form.find('#company_logo').val(),
                catalog_title: form.find('#catalog_title').val(),
                contact_email: form.find('#contact_email').val(),
                contact_phone: form.find('#contact_phone').val(),
                show_sku: form.find('#show_sku').is(':checked') ? 'yes' : 'no',
                show_stock: form.find('#show_stock').is(':checked') ? 'yes' : 'no',
                currency_symbol: form.find('#currency_symbol').val()
            };
            
            // Enviar por AJAX
            $.ajax({
                url: wfxWholesale.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        showMessage('Configuración guardada exitosamente', 'success');
                    } else {
                        showMessage('Error: ' + response.data.message, 'error');
                    }
                },
                error: function() {
                    showMessage('Error de conexión al guardar la configuración', 'error');
                },
                complete: function() {
                    submitButton.prop('disabled', false).text(originalText);
                }
            });
        });
        
        // Media Uploader para logo
        $('#wfx-upload-logo').on('click', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var mediaUploader;
            
            // Si ya existe el uploader, abrirlo
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            
            // Crear el media uploader
            mediaUploader = wp.media({
                title: 'Seleccionar Logo',
                button: {
                    text: 'Usar este logo'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });
            
            // Cuando se selecciona una imagen
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                
                // Actualizar campo oculto y preview
                $('#company_logo').val(attachment.url);
                $('#wfx-logo-preview').html('<img src="' + attachment.url + '" style="max-width: 200px; margin-top: 10px;" />');
            });
            
            // Abrir el uploader
            mediaUploader.open();
        });
        
        // Función auxiliar para mostrar mensajes
        function showMessage(message, type) {
            // Remover mensajes anteriores
            $('.wfx-message').remove();
            
            // Crear nuevo mensaje
            var messageDiv = $('<div class="wfx-message ' + type + '">' + message + '</div>');
            
            // Insertar después del título
            $('.wfx-wholesale-wrap h1').after(messageDiv);
            
            // Auto-ocultar después de 5 segundos
            setTimeout(function() {
                messageDiv.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
            
            // Scroll al mensaje
            $('html, body').animate({
                scrollTop: messageDiv.offset().top - 100
            }, 500);
        }
        
    });
    
})(jQuery);
