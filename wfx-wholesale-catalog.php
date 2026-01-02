<?php
/**
 * Plugin Name: WFX Wholesale Catalog Generator
 * Plugin URI: https://www.wifextelematics.com
 * Description: Genera catálogos PDF de productos mayoristas seleccionados desde WooCommerce
 * Version: 1.1.1
 * Author: WFX Telematics
 * Author URI: https://www.wifextelematics.com
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Text Domain: wfx-wholesale
 * Domain Path: /languages
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WFX_WHOLESALE_VERSION', '1.1.1');
define('WFX_WHOLESALE_PATH', plugin_dir_path(__FILE__));
define('WFX_WHOLESALE_URL', plugin_dir_url(__FILE__));

/**
 * Declarar compatibilidad con características de WooCommerce
 */
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('orders_cache', __FILE__, true);
    }
});

/**
 * Verifica si WooCommerce está activo
 * 
 * @return bool
 */
function wfx_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'wfx_woocommerce_missing_notice');
        return false;
    }
    return true;
}

/**
 * Muestra aviso de que WooCommerce no está instalado
 */
function wfx_woocommerce_missing_notice() {
    echo '<div class="error"><p><strong>WFX Wholesale Catalog</strong> requiere que WooCommerce esté instalado y activado.</p></div>';
}

add_action('plugins_loaded', 'wfx_wholesale_init');

/**
 * Inicializa el plugin
 */
function wfx_wholesale_init() {
    if (!wfx_check_woocommerce()) {
        return;
    }
    
    require_once WFX_WHOLESALE_PATH . 'includes/class-wfx-admin.php';
    require_once WFX_WHOLESALE_PATH . 'includes/class-wfx-pdf-generator.php';
    require_once WFX_WHOLESALE_PATH . 'includes/class-wfx-product-meta.php';
    
    WFX_Wholesale_Admin::init();
    WFX_Wholesale_Product_Meta::init();
}

register_activation_hook(__FILE__, 'wfx_wholesale_activate');

/**
 * Hook de activación del plugin
 */
function wfx_wholesale_activate() {
    $default_settings = array(
        'company_name' => get_bloginfo('name'),
        'company_logo' => '',
        'contact_email' => get_option('admin_email'),
        'contact_phone' => '',
        'catalog_title' => 'Catálogo Mayorista',
        'show_sku' => 'yes',
        'show_stock' => 'yes',
        'currency_symbol' => get_woocommerce_currency_symbol(),
    );
    
    if (!get_option('wfx_wholesale_settings')) {
        add_option('wfx_wholesale_settings', $default_settings);
    }
    
    $upload_dir = wp_upload_dir();
    $catalog_dir = $upload_dir['basedir'] . '/wfx-catalogs/';
    
    if (!file_exists($catalog_dir)) {
        wp_mkdir_p($catalog_dir);
        
        // Crear archivo .htaccess para proteger archivos
        $htaccess_content = "Options -Indexes\n<FilesMatch \"\\.(pdf)$\">\n    Order Allow,Deny\n    Allow from all\n</FilesMatch>";
        file_put_contents($catalog_dir . '.htaccess', $htaccess_content);
    }
}

register_deactivation_hook(__FILE__, 'wfx_wholesale_deactivate');

/**
 * Hook de desactivación del plugin
 */
function wfx_wholesale_deactivate() {
    // Limpieza si es necesaria
}
