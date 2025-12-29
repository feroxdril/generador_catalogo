<?php
/**
 * Generador de PDF para WFX Wholesale Catalog
 *
 * @package WFX_Wholesale_Catalog
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase que genera los catálogos PDF usando TCPDF
 */
class WFX_Wholesale_PDF_Generator {
    
    private $pdf;
    private $settings;
    private $prices;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->load_tcpdf();
    }
    
    /**
     * Carga la librería TCPDF
     */
    private function load_tcpdf() {
        // Intentar cargar TCPDF desde diferentes ubicaciones
        if (file_exists(WFX_WHOLESALE_PATH . 'vendor/tecnickcom/tcpdf/tcpdf.php')) {
            require_once WFX_WHOLESALE_PATH . 'vendor/tecnickcom/tcpdf/tcpdf.php';
        } elseif (file_exists(WFX_WHOLESALE_PATH . 'lib/tcpdf/tcpdf.php')) {
            require_once WFX_WHOLESALE_PATH . 'lib/tcpdf/tcpdf.php';
        } else {
            add_action('admin_notices', function() {
                echo '<div class="error"><p><strong>WFX Wholesale Catalog:</strong> TCPDF no está instalado. El plugin no funcionará correctamente.</p></div>';
            });
            return;
        }
    }
    
    /**
     * Genera el catálogo PDF
     * 
     * @param array $product_ids IDs de productos a incluir
     * @param array $options Opciones de configuración
     * @return string|false URL del PDF generado o false en caso de error
     */
    public function generate($product_ids, $options = array()) {
        // Validar que TCPDF esté disponible
        if (!class_exists('TCPDF')) {
            error_log('WFX Wholesale: TCPDF class not found');
            return false;
        }
        
        try {
            $this->settings = isset($options['settings']) ? $options['settings'] : get_option('wfx_wholesale_settings', array());
            $this->prices = isset($options['prices']) ? $options['prices'] : get_option('wfx_wholesale_prices', array());
            
            // Configurar PDF
            $this->setup_pdf();
            
            // Añadir header
            $this->add_header();
            
            // Añadir productos
            $products = $this->get_sorted_products($product_ids);
            $this->add_products($products);
            
            // Añadir footer
            $this->add_footer();
            
            // Guardar PDF
            return $this->save_pdf();
            
        } catch (Exception $e) {
            error_log('WFX Wholesale PDF Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Configura el PDF
     */
    private function setup_pdf() {
        $catalog_title = isset($this->settings['catalog_title']) ? $this->settings['catalog_title'] : 'Catálogo Mayorista';
        $company_name = isset($this->settings['company_name']) ? $this->settings['company_name'] : get_bloginfo('name');
        
        $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Información del documento
        $this->pdf->SetCreator('WFX Wholesale Catalog');
        $this->pdf->SetAuthor($company_name);
        $this->pdf->SetTitle($catalog_title);
        $this->pdf->SetSubject('Catálogo de Productos Mayoristas');
        
        // Márgenes
        $this->pdf->SetMargins(15, 30, 15);
        $this->pdf->SetAutoPageBreak(true, 25);
        
        // Fuente por defecto
        $this->pdf->SetFont('helvetica', '', 10);
        
        // Quitar header/footer por defecto
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
    }
    
    /**
     * Añade el header al PDF
     */
    private function add_header() {
        $this->pdf->AddPage();
        
        $company_name = isset($this->settings['company_name']) ? $this->settings['company_name'] : get_bloginfo('name');
        $catalog_title = isset($this->settings['catalog_title']) ? $this->settings['catalog_title'] : 'Catálogo Mayorista';
        $company_logo = isset($this->settings['company_logo']) ? $this->settings['company_logo'] : '';
        
        // Logo
        if (!empty($company_logo)) {
            $logo_path = str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $company_logo);
            if (file_exists($logo_path)) {
                $this->pdf->Image($logo_path, 15, 15, 40, 0, '', '', '', false, 300, '', false, false, 0);
                $this->pdf->Ln(25);
            }
        }
        
        // Título
        $this->pdf->SetFont('helvetica', 'B', 20);
        $this->pdf->Cell(0, 10, $catalog_title, 0, 1, 'C');
        
        $this->pdf->SetFont('helvetica', '', 12);
        $this->pdf->Cell(0, 5, $company_name, 0, 1, 'C');
        
        $this->pdf->Ln(5);
        
        // Fecha
        $this->pdf->SetFont('helvetica', 'I', 9);
        $this->pdf->Cell(0, 5, 'Generado: ' . date('d/m/Y H:i'), 0, 1, 'R');
        
        $this->pdf->Ln(10);
    }
    
    /**
     * Añade los productos al PDF
     * 
     * @param array $products Array de productos WC_Product
     */
    private function add_products($products) {
        if (empty($products)) {
            return;
        }
        
        // Título de sección
        $this->pdf->SetFont('helvetica', 'B', 14);
        $this->pdf->Cell(0, 8, 'Listado de Productos', 0, 1, 'L');
        $this->pdf->Ln(3);
        
        foreach ($products as $product) {
            $this->add_product_row($product);
        }
    }
    
    /**
     * Añade una fila de producto
     * 
     * @param WC_Product $product Producto de WooCommerce
     */
    private function add_product_row($product) {
        $product_id = $product->get_id();
        
        // Verificar espacio disponible
        if ($this->pdf->GetY() > 250) {
            $this->pdf->AddPage();
        }
        
        $y_start = $this->pdf->GetY();
        
        // Imagen del producto
        $image_id = $product->get_image_id();
        if ($image_id) {
            $image_path = get_attached_file($image_id);
            if ($image_path && file_exists($image_path)) {
                try {
                    $this->pdf->Image($image_path, 15, $y_start, 30, 0, '', '', '', false, 300, '', false, false, 0);
                } catch (Exception $e) {
                    // Imagen no válida, continuar sin ella
                }
            }
        }
        
        // Información del producto
        $x_text = 50;
        $this->pdf->SetXY($x_text, $y_start);
        
        // Nombre del producto
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->MultiCell(145, 6, $product->get_name(), 0, 'L', false, 1, $x_text, $y_start);
        
        $y_current = $this->pdf->GetY();
        
        // SKU
        if (isset($this->settings['show_sku']) && $this->settings['show_sku'] === 'yes') {
            $sku = $product->get_sku();
            if ($sku) {
                $this->pdf->SetFont('helvetica', '', 9);
                $this->pdf->SetXY($x_text, $y_current);
                $this->pdf->Cell(0, 5, 'SKU: ' . $sku, 0, 1, 'L');
                $y_current = $this->pdf->GetY();
            }
        }
        
        // Descripción corta
        $description = $product->get_short_description();
        if (empty($description)) {
            $description = $product->get_description();
        }
        if (!empty($description)) {
            $description = wp_strip_all_tags($description);
            $description = substr($description, 0, 200);
            if (strlen($description) > 200) {
                $description .= '...';
            }
            
            $this->pdf->SetFont('helvetica', '', 9);
            $this->pdf->SetXY($x_text, $y_current);
            $this->pdf->MultiCell(145, 4, $description, 0, 'L', false, 1);
            $y_current = $this->pdf->GetY() + 2;
        }
        
        // Precio mayorista
        $wholesale_price = isset($this->prices[$product_id]) ? $this->prices[$product_id] : '';
        $currency_symbol = isset($this->settings['currency_symbol']) ? $this->settings['currency_symbol'] : get_woocommerce_currency_symbol();
        
        if ($wholesale_price && $wholesale_price > 0) {
            $this->pdf->SetFont('helvetica', 'B', 11);
            $this->pdf->SetTextColor(0, 128, 0);
            $this->pdf->SetXY($x_text, $y_current);
            $this->pdf->Cell(0, 5, 'Precio Mayorista: ' . $currency_symbol . number_format($wholesale_price, 2), 0, 1, 'L');
            $this->pdf->SetTextColor(0, 0, 0);
            $y_current = $this->pdf->GetY();
        } else {
            // Precio regular
            $regular_price = $product->get_regular_price();
            if ($regular_price) {
                $this->pdf->SetFont('helvetica', '', 10);
                $this->pdf->SetXY($x_text, $y_current);
                $this->pdf->Cell(0, 5, 'Precio: ' . $currency_symbol . number_format($regular_price, 2), 0, 1, 'L');
                $y_current = $this->pdf->GetY();
            }
        }
        
        // Stock
        if (isset($this->settings['show_stock']) && $this->settings['show_stock'] === 'yes') {
            $stock = $product->get_stock_quantity();
            if ($stock !== null && $stock !== '') {
                $this->pdf->SetFont('helvetica', '', 9);
                $this->pdf->SetXY($x_text, $y_current);
                $stock_status = $stock > 0 ? 'En stock: ' . $stock . ' unidades' : 'Sin stock';
                $this->pdf->Cell(0, 5, $stock_status, 0, 1, 'L');
                $y_current = $this->pdf->GetY();
            }
        }
        
        // Calcular altura de la fila
        $row_height = max(30, $y_current - $y_start + 5);
        
        // Línea separadora
        $this->pdf->SetY($y_start + $row_height);
        $this->pdf->Line(15, $y_start + $row_height, 195, $y_start + $row_height);
        
        $this->pdf->Ln(5);
    }
    
    /**
     * Añade el footer al PDF
     */
    private function add_footer() {
        // El footer se añadirá en cada página si es necesario
        $total_pages = $this->pdf->getNumPages();
        
        for ($page = 1; $page <= $total_pages; $page++) {
            $this->pdf->setPage($page);
            
            // Posición del footer
            $this->pdf->SetY(-15);
            
            $this->pdf->SetFont('helvetica', 'I', 8);
            
            // Información de contacto
            $contact_info = array();
            if (!empty($this->settings['contact_email'])) {
                $contact_info[] = 'Email: ' . $this->settings['contact_email'];
            }
            if (!empty($this->settings['contact_phone'])) {
                $contact_info[] = 'Tel: ' . $this->settings['contact_phone'];
            }
            
            if (!empty($contact_info)) {
                $this->pdf->Cell(0, 5, implode(' | ', $contact_info), 0, 1, 'C');
            }
            
            // Número de página
            $this->pdf->Cell(0, 5, 'Página ' . $page . ' de ' . $total_pages, 0, 0, 'C');
        }
    }
    
    /**
     * Obtiene los productos ordenados
     * 
     * @param array $product_ids IDs de productos
     * @return array Array de productos WC_Product
     */
    private function get_sorted_products($product_ids) {
        $products = array();
        
        foreach ($product_ids as $product_id) {
            $product = wc_get_product($product_id);
            if ($product && $product->is_type('simple')) {
                $products[] = $product;
            }
        }
        
        // Ordenar por nombre
        usort($products, function($a, $b) {
            return strcmp($a->get_name(), $b->get_name());
        });
        
        return $products;
    }
    
    /**
     * Guarda el PDF y retorna la URL
     * 
     * @return string|false URL del PDF o false en caso de error
     */
    private function save_pdf() {
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/wfx-catalogs/';
        
        // Crear directorio si no existe
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }
        
        // Nombre único para el archivo
        $filename = 'catalogo-mayorista-' . date('Y-m-d-His') . '-' . wp_generate_password(8, false) . '.pdf';
        $filepath = $pdf_dir . $filename;
        
        // Guardar PDF
        $this->pdf->Output($filepath, 'F');
        
        // Verificar que se creó correctamente
        if (file_exists($filepath)) {
            return $upload_dir['baseurl'] . '/wfx-catalogs/' . $filename;
        }
        
        return false;
    }
}
