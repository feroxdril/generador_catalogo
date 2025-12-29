<?php
/**
 * Clase Admin para WFX Wholesale Catalog
 *
 * @package WFX_Wholesale_Catalog
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase que maneja la interfaz de administración del plugin
 */
class WFX_Wholesale_Admin {
    
    /**
     * Inicializa la clase
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
        add_action('wp_ajax_wfx_generate_catalog', array(__CLASS__, 'ajax_generate_catalog'));
        add_action('wp_ajax_wfx_save_settings', array(__CLASS__, 'ajax_save_settings'));
        add_action('wp_ajax_wfx_save_selection', array(__CLASS__, 'ajax_save_selection'));
        add_action('wp_ajax_wfx_save_wholesale_price', array(__CLASS__, 'ajax_save_wholesale_price'));
    }
    
    /**
     * Añade el menú de administración
     */
    public static function add_admin_menu() {
        add_menu_page(
            'Catálogo Mayorista',
            'Catálogo PDF',
            'manage_woocommerce',
            'wfx-wholesale-catalog',
            array(__CLASS__, 'render_main_page'),
            'dashicons-media-document',
            56
        );
        
        add_submenu_page(
            'wfx-wholesale-catalog',
            'Configuración',
            'Configuración',
            'manage_woocommerce',
            'wfx-wholesale-settings',
            array(__CLASS__, 'render_settings_page')
        );
    }
    
    /**
     * Encola los scripts y estilos
     */
    public static function enqueue_scripts($hook) {
        if (strpos($hook, 'wfx-wholesale') === false) {
            return;
        }
        
        wp_enqueue_style('wfx-wholesale-admin', WFX_WHOLESALE_URL . 'assets/css/admin.css', array(), WFX_WHOLESALE_VERSION);
        wp_enqueue_script('wfx-wholesale-admin', WFX_WHOLESALE_URL . 'assets/js/admin.js', array('jquery'), WFX_WHOLESALE_VERSION, true);
        
        wp_localize_script('wfx-wholesale-admin', 'wfxWholesale', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wfx_wholesale_nonce'),
        ));
        
        // Para el media uploader
        wp_enqueue_media();
    }
    
    /**
     * Renderiza la página principal
     */
    public static function render_main_page() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('No tienes permisos para acceder a esta página.'));
        }
        
        $products = self::get_products();
        $saved_selection = get_option('wfx_wholesale_selection', array());
        $saved_prices = get_option('wfx_wholesale_prices', array());
        
        ?>
        <div class="wrap wfx-wholesale-wrap">
            <h1>Generador de Catálogo Mayorista</h1>
            
            <div class="wfx-catalog-container">
                <div class="wfx-products-section">
                    <div class="wfx-search-box">
                        <input type="text" id="wfx-product-search" placeholder="Buscar productos..." />
                    </div>
                    
                    <div class="wfx-select-all">
                        <label>
                            <input type="checkbox" id="wfx-select-all" />
                            Seleccionar todos
                        </label>
                    </div>
                    
                    <div class="wfx-products-list">
                        <?php foreach ($products as $product) : ?>
                            <?php
                            $product_id = $product->get_id();
                            $is_checked = in_array($product_id, $saved_selection);
                            $wholesale_price = isset($saved_prices[$product_id]) ? $saved_prices[$product_id] : '';
                            $image_url = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
                            if (!$image_url) {
                                $image_url = wc_placeholder_img_src();
                            }
                            ?>
                            <div class="wfx-product-item" data-product-id="<?php echo esc_attr($product_id); ?>">
                                <div class="wfx-product-checkbox">
                                    <input type="checkbox" 
                                           name="wfx_products[]" 
                                           value="<?php echo esc_attr($product_id); ?>"
                                           <?php checked($is_checked); ?>
                                           class="wfx-product-select" />
                                </div>
                                
                                <div class="wfx-product-image">
                                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" />
                                </div>
                                
                                <div class="wfx-product-details">
                                    <h3><?php echo esc_html($product->get_name()); ?></h3>
                                    <p class="wfx-product-sku">SKU: <?php echo esc_html($product->get_sku() ? $product->get_sku() : 'N/A'); ?></p>
                                    <p class="wfx-product-price">Precio regular: <?php echo $product->get_price_html(); ?></p>
                                    <p class="wfx-product-stock">Stock: <?php echo esc_html($product->get_stock_quantity() ? $product->get_stock_quantity() : 'N/A'); ?></p>
                                </div>
                                
                                <div class="wfx-product-wholesale">
                                    <label>Precio Mayorista:</label>
                                    <input type="number" 
                                           name="wfx_wholesale_price[<?php echo esc_attr($product_id); ?>]"
                                           value="<?php echo esc_attr($wholesale_price); ?>"
                                           step="0.01"
                                           min="0"
                                           placeholder="0.00"
                                           class="wfx-wholesale-price wfx-wholesale-price-input"
                                           data-product-id="<?php echo esc_attr($product_id); ?>" />
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="wfx-sidebar">
                    <div class="wfx-sidebar-card">
                        <h3>Productos Seleccionados</h3>
                        <p class="wfx-selected-count">
                            <span id="wfx-selected-count">0</span> productos
                        </p>
                        
                        <button type="button" id="wfx-save-selection" class="button">
                            Guardar Selección
                        </button>
                        
                        <button type="button" id="wfx-generate-catalog" class="button button-primary">
                            Generar Catálogo PDF
                        </button>
                    </div>
                    
                    <div class="wfx-sidebar-card">
                        <h3>Opciones</h3>
                        <p><small>Configure el catálogo en <a href="<?php echo admin_url('admin.php?page=wfx-wholesale-settings'); ?>">Configuración</a></small></p>
                    </div>
                </div>
            </div>
            
            <div id="wfx-loading-overlay" style="display: none;">
                <div class="wfx-loading-content">
                    <div class="wfx-spinner"></div>
                    <p>Generando catálogo PDF...</p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Renderiza la página de configuración
     */
    public static function render_settings_page() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('No tienes permisos para acceder a esta página.'));
        }
        
        $settings = get_option('wfx_wholesale_settings', array());
        $defaults = array(
            'company_name' => get_bloginfo('name'),
            'company_logo' => '',
            'contact_email' => get_option('admin_email'),
            'contact_phone' => '',
            'catalog_title' => 'Catálogo Mayorista',
            'show_sku' => 'yes',
            'show_stock' => 'yes',
            'currency_symbol' => get_woocommerce_currency_symbol(),
        );
        $settings = wp_parse_args($settings, $defaults);
        
        ?>
        <div class="wrap wfx-wholesale-wrap">
            <h1>Configuración del Catálogo Mayorista</h1>
            
            <form id="wfx-settings-form" class="wfx-settings-form">
                <?php wp_nonce_field('wfx_wholesale_settings', 'wfx_settings_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="company_name">Nombre de la Empresa</label></th>
                        <td>
                            <input type="text" 
                                   id="company_name" 
                                   name="company_name" 
                                   value="<?php echo esc_attr($settings['company_name']); ?>" 
                                   class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="company_logo">Logo de la Empresa</label></th>
                        <td>
                            <input type="hidden" 
                                   id="company_logo" 
                                   name="company_logo" 
                                   value="<?php echo esc_attr($settings['company_logo']); ?>" />
                            <button type="button" id="wfx-upload-logo" class="button">
                                Seleccionar Logo
                            </button>
                            <div id="wfx-logo-preview">
                                <?php if (!empty($settings['company_logo'])) : ?>
                                    <img src="<?php echo esc_url($settings['company_logo']); ?>" style="max-width: 200px; margin-top: 10px;" />
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="catalog_title">Título del Catálogo</label></th>
                        <td>
                            <input type="text" 
                                   id="catalog_title" 
                                   name="catalog_title" 
                                   value="<?php echo esc_attr($settings['catalog_title']); ?>" 
                                   class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="contact_email">Email de Contacto</label></th>
                        <td>
                            <input type="email" 
                                   id="contact_email" 
                                   name="contact_email" 
                                   value="<?php echo esc_attr($settings['contact_email']); ?>" 
                                   class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="contact_phone">Teléfono de Contacto</label></th>
                        <td>
                            <input type="text" 
                                   id="contact_phone" 
                                   name="contact_phone" 
                                   value="<?php echo esc_attr($settings['contact_phone']); ?>" 
                                   class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="show_sku">Mostrar SKU</label></th>
                        <td>
                            <input type="checkbox" 
                                   id="show_sku" 
                                   name="show_sku" 
                                   value="yes"
                                   <?php checked($settings['show_sku'], 'yes'); ?> />
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="show_stock">Mostrar Stock</label></th>
                        <td>
                            <input type="checkbox" 
                                   id="show_stock" 
                                   name="show_stock" 
                                   value="yes"
                                   <?php checked($settings['show_stock'], 'yes'); ?> />
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="currency_symbol">Símbolo de Moneda</label></th>
                        <td>
                            <input type="text" 
                                   id="currency_symbol" 
                                   name="currency_symbol" 
                                   value="<?php echo esc_attr($settings['currency_symbol']); ?>" 
                                   class="small-text" />
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary">
                        Guardar Configuración
                    </button>
                </p>
            </form>
        </div>
        <?php
    }
    
    /**
     * Obtiene los productos de WooCommerce
     * 
     * @return array
     */
    private static function get_products() {
        $args = array(
            'status' => 'publish',
            'limit' => -1,
            'orderby' => 'name',
            'order' => 'ASC',
        );
        
        return wc_get_products($args);
    }
    
    /**
     * AJAX: Genera el catálogo PDF
     */
    public static function ajax_generate_catalog() {
        check_ajax_referer('wfx_wholesale_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        $product_ids = isset($_POST['product_ids']) ? array_map('intval', $_POST['product_ids']) : array();
        $options = isset($_POST['options']) ? $_POST['options'] : array();
        
        if (empty($product_ids)) {
            wp_send_json_error('No se seleccionaron productos');
        }
        
        // Verificar que TCPDF esté disponible
        if (!class_exists('TCPDF')) {
            wp_send_json_error('TCPDF no está disponible. Por favor contacte al administrador.');
        }
        
        try {
            $generator = new WFX_Wholesale_PDF_Generator();
            $settings = get_option('wfx_wholesale_settings', array());
            $prices = get_option('wfx_wholesale_prices', array());
            
            $options = array(
                'settings' => $settings,
                'prices' => $prices,
            );
            
            $pdf_url = $generator->generate($product_ids, $options);
            
            if ($pdf_url) {
                wp_send_json_success(array(
                    'pdf_url' => $pdf_url,
                    'message' => 'PDF generado correctamente'
                ));
            } else {
                wp_send_json_error('Error al generar el PDF. Verifique los permisos de escritura.');
            }
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Guarda la configuración
     */
    public static function ajax_save_settings() {
        check_ajax_referer('wfx_wholesale_settings', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => 'Permisos insuficientes'));
        }
        
        $settings = array(
            'company_name' => isset($_POST['company_name']) ? sanitize_text_field($_POST['company_name']) : '',
            'company_logo' => isset($_POST['company_logo']) ? esc_url_raw($_POST['company_logo']) : '',
            'catalog_title' => isset($_POST['catalog_title']) ? sanitize_text_field($_POST['catalog_title']) : '',
            'contact_email' => isset($_POST['contact_email']) ? sanitize_email($_POST['contact_email']) : '',
            'contact_phone' => isset($_POST['contact_phone']) ? sanitize_text_field($_POST['contact_phone']) : '',
            'show_sku' => isset($_POST['show_sku']) ? 'yes' : 'no',
            'show_stock' => isset($_POST['show_stock']) ? 'yes' : 'no',
            'currency_symbol' => isset($_POST['currency_symbol']) ? sanitize_text_field($_POST['currency_symbol']) : '',
        );
        
        update_option('wfx_wholesale_settings', $settings);
        
        wp_send_json_success(array('message' => 'Configuración guardada exitosamente'));
    }
    
    /**
     * AJAX: Guarda la selección de productos
     */
    public static function ajax_save_selection() {
        check_ajax_referer('wfx_wholesale_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => 'Permisos insuficientes'));
        }
        
        $product_ids = isset($_POST['product_ids']) ? array_map('intval', $_POST['product_ids']) : array();
        $prices = isset($_POST['prices']) ? array_map('floatval', $_POST['prices']) : array();
        
        update_option('wfx_wholesale_selection', $product_ids);
        update_option('wfx_wholesale_prices', $prices);
        
        wp_send_json_success(array('message' => 'Selección guardada exitosamente'));
    }
    
    /**
     * AJAX: Guarda el precio mayorista de un producto
     */
    public static function ajax_save_wholesale_price() {
        check_ajax_referer('wfx_wholesale_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $price = isset($_POST['price']) ? sanitize_text_field($_POST['price']) : '';
        
        if ($product_id && is_numeric($price)) {
            update_post_meta($product_id, '_wfx_wholesale_price', $price);
            wp_send_json_success('Precio actualizado');
        } else {
            wp_send_json_error('Datos inválidos');
        }
    }
}
