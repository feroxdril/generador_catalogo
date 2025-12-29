<?php
/**
 * Meta Boxes de producto para WFX Wholesale Catalog
 *
 * @package WFX_Wholesale_Catalog
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase que maneja los meta boxes de productos
 */
class WFX_Wholesale_Product_Meta {
    
    /**
     * Inicializa la clase
     */
    public static function init() {
        add_action('add_meta_boxes', array(__CLASS__, 'add_meta_box'));
        add_action('save_post', array(__CLASS__, 'save_meta_box'), 10, 2);
    }
    
    /**
     * Añade el meta box a los productos
     */
    public static function add_meta_box() {
        add_meta_box(
            'wfx_wholesale_product_meta',
            'Catálogo Mayorista',
            array(__CLASS__, 'render_meta_box'),
            'product',
            'side',
            'default'
        );
    }
    
    /**
     * Renderiza el meta box
     * 
     * @param WP_Post $post Post actual
     */
    public static function render_meta_box($post) {
        // Nonce para seguridad
        wp_nonce_field('wfx_wholesale_meta_box', 'wfx_wholesale_meta_nonce');
        
        // Obtener valores guardados
        $in_catalog = get_post_meta($post->ID, '_wfx_in_wholesale_catalog', true);
        $wholesale_price = get_post_meta($post->ID, '_wfx_wholesale_price', true);
        
        ?>
        <div class="wfx-product-meta">
            <p>
                <label>
                    <input type="checkbox" 
                           name="wfx_in_wholesale_catalog" 
                           value="yes"
                           <?php checked($in_catalog, 'yes'); ?> />
                    Incluir en catálogo mayorista
                </label>
            </p>
            
            <p>
                <label for="wfx_wholesale_price">
                    <strong>Precio Mayorista:</strong>
                </label>
                <input type="number" 
                       id="wfx_wholesale_price"
                       name="wfx_wholesale_price" 
                       value="<?php echo esc_attr($wholesale_price); ?>"
                       step="0.01"
                       min="0"
                       placeholder="0.00"
                       style="width: 100%;" />
                <small>Precio especial para el catálogo mayorista</small>
            </p>
        </div>
        
        <style>
            .wfx-product-meta p {
                margin-bottom: 12px;
            }
            .wfx-product-meta label {
                display: block;
                margin-bottom: 5px;
            }
            .wfx-product-meta input[type="checkbox"] {
                margin-right: 5px;
            }
        </style>
        <?php
    }
    
    /**
     * Guarda los datos del meta box
     * 
     * @param int $post_id ID del post
     * @param WP_Post $post Post actual
     */
    public static function save_meta_box($post_id, $post) {
        // Verificar nonce
        if (!isset($_POST['wfx_wholesale_meta_nonce']) || 
            !wp_verify_nonce($_POST['wfx_wholesale_meta_nonce'], 'wfx_wholesale_meta_box')) {
            return;
        }
        
        // Verificar que no sea autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Verificar permisos
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Verificar que sea un producto
        if ($post->post_type !== 'product') {
            return;
        }
        
        // Guardar checkbox
        $in_catalog = isset($_POST['wfx_in_wholesale_catalog']) ? 'yes' : 'no';
        update_post_meta($post_id, '_wfx_in_wholesale_catalog', $in_catalog);
        
        // Guardar precio mayorista
        if (isset($_POST['wfx_wholesale_price'])) {
            $wholesale_price = sanitize_text_field($_POST['wfx_wholesale_price']);
            $wholesale_price = floatval($wholesale_price);
            update_post_meta($post_id, '_wfx_wholesale_price', $wholesale_price);
        }
    }
}
