<?php
if (!defined('ABSPATH')) {
    exit;
}

class WFX_PDF_Generator {
    
    private $tcpdf_loaded = false;
    
    public function __construct() {
        $this->load_tcpdf();
    }
    
    /**
     * Cargar TCPDF desde múltiples ubicaciones posibles
     */
    private function load_tcpdf() {
        // Intentar ubicaciones en orden de prioridad
        $possible_paths = array(
            WFX_WHOLESALE_PATH . 'lib/tcpdf/tcpdf.php',
            WFX_WHOLESALE_PATH . 'vendor/tecnickcom/tcpdf/tcpdf.php',
            ABSPATH . 'vendor/tecnickcom/tcpdf/tcpdf.php',
        );
        
        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                $this->tcpdf_loaded = true;
                error_log('WFX Wholesale: TCPDF loaded from: ' . $path);
                break;
            }
        }
        
        if (!$this->tcpdf_loaded) {
            error_log('WFX Wholesale: TCPDF not found in any location');
        }
    }
    
    /**
     * Verificar si TCPDF está disponible
     */
    private function is_tcpdf_available() {
        return $this->tcpdf_loaded && class_exists('TCPDF');
    }
    
    /**
     * Generar PDF del catálogo
     */
    public function generate($product_ids, $options = array()) {
        // Validar que TCPDF esté disponible
        if (!$this->is_tcpdf_available()) {
            error_log('WFX Wholesale: Cannot generate PDF - TCPDF not available');
            return false;
        }
        
        // Validar productos
        if (empty($product_ids) || !is_array($product_ids)) {
            error_log('WFX Wholesale: No products provided');
            return false;
        }
        
        try {
            $settings = get_option('wfx_wholesale_settings', array());
            
            // Crear instancia de TCPDF
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            
            // Configurar documento
            $pdf->SetCreator('WFX Wholesale Catalog Generator');
            $pdf->SetAuthor($settings['company_name'] ?? get_bloginfo('name'));
            $pdf->SetTitle($settings['catalog_title'] ?? 'Catálogo Mayorista');
            $pdf->SetSubject('Catálogo de Productos Mayoristas');
            $pdf->SetKeywords('catálogo, mayorista, productos, precios');
            
            // Configurar márgenes
            $pdf->SetMargins(15, 20, 15);
            $pdf->SetHeaderMargin(10);
            $pdf->SetFooterMargin(15);
            $pdf->SetAutoPageBreak(true, 25);
            
            // Desactivar header y footer automáticos
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            // Configurar fuente
            $pdf->SetFont('helvetica', '', 10);
            
            // Primera página
            $pdf->AddPage();
            
            // Agregar contenido
            $this->add_header($pdf, $settings);
            $this->add_products($pdf, $product_ids, $options);
            $this->add_footer($pdf, $settings);
            
            // Crear directorio de salida
            $upload_dir = wp_upload_dir();
            $pdf_dir = $upload_dir['basedir'] . '/wfx-catalogs/';
            
            if (!file_exists($pdf_dir)) {
                if (!wp_mkdir_p($pdf_dir)) {
                    error_log('WFX Wholesale: Failed to create directory: ' . $pdf_dir);
                    return false;
                }
            }
            
            // Verificar permisos de escritura
            if (!is_writable($pdf_dir)) {
                error_log('WFX Wholesale: Directory not writable: ' . $pdf_dir);
                return false;
            }
            
            // Nombre del archivo
            $filename = 'catalogo-' . date('Y-m-d-His') . '-' . wp_generate_password(6, false, false) . '.pdf';
            $filepath = $pdf_dir . $filename;
            
            // Guardar PDF
            $pdf->Output($filepath, 'F');
            
            // Verificar que se creó
            if (!file_exists($filepath)) {
                error_log('WFX Wholesale: Failed to create PDF file: ' . $filepath);
                return false;
            }
            
            error_log('WFX Wholesale: PDF generated successfully: ' . $filename);
            
            return $upload_dir['baseurl'] . '/wfx-catalogs/' . $filename;
            
        } catch (Exception $e) {
            error_log('WFX Wholesale PDF Error: ' . $e->getMessage());
            error_log('WFX Wholesale PDF Stack: ' . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Agregar header al PDF
     */
    private function add_header($pdf, $settings) {
        // Logo si existe
        if (!empty($settings['company_logo'])) {
            $logo_path = $this->get_image_path($settings['company_logo']);
            if ($logo_path && file_exists($logo_path)) {
                try {
                    $pdf->Image($logo_path, 15, 15, 50, 0, '', '', '', false, 300, '', false, false, 0);
                } catch (Exception $e) {
                    error_log('WFX Wholesale: Logo error: ' . $e->getMessage());
                }
            }
        }
        
        // Título
        $pdf->SetY(25);
        $pdf->SetFont('helvetica', 'B', 22);
        $pdf->SetTextColor(33, 37, 41);
        $pdf->Cell(0, 10, $settings['catalog_title'] ?? 'Catálogo Mayorista', 0, 1, 'C');
        
        // Fecha
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(108, 117, 125);
        $pdf->Cell(0, 6, date('d/m/Y'), 0, 1, 'C');
        
        // Línea separadora
        $pdf->SetY($pdf->GetY() + 5);
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->SetY($pdf->GetY() + 8);
    }
    
    /**
     * Agregar productos al PDF
     */
    private function add_products($pdf, $product_ids, $options) {
        $products = $this->get_sorted_products($product_ids, $options['sort_by'] ?? 'name');
        
        if (empty($products)) {
            $pdf->SetFont('helvetica', 'I', 12);
            $pdf->SetTextColor(108, 117, 125);
            $pdf->Cell(0, 10, 'No hay productos disponibles', 0, 1, 'C');
            return;
        }
        
        $pdf->SetTextColor(0, 0, 0);
        
        foreach ($products as $index => $product) {
            // Verificar si hay espacio para el producto
            if ($pdf->GetY() > 250) {
                $pdf->AddPage();
            }
            
            $this->add_product_row($pdf, $product, $options);
        }
    }
    
    /**
     * Agregar fila de producto
     */
    private function add_product_row($pdf, $product, $options) {
        $product_id = $product->get_id();
        $y_start = $pdf->GetY();
        $x_margin = 15;
        
        // Imagen del producto
        $image_x = $x_margin;
        $image_width = 35;
        
        if (!empty($options['include_images'])) {
            $image_id = $product->get_image_id();
            if ($image_id) {
                $image_path = $this->get_image_path(wp_get_attachment_url($image_id));
                if ($image_path && file_exists($image_path)) {
                    try {
                        $pdf->Image($image_path, $image_x, $y_start, $image_width, 0, '', '', '', false, 300, '', false, false, 0);
                    } catch (Exception $e) {
                        error_log('WFX Wholesale: Product image error: ' . $e->getMessage());
                    }
                }
            }
        }
        
        // Contenido del producto
        $content_x = $image_x + $image_width + 5;
        $content_width = 140;
        $pdf->SetX($content_x);
        
        // Nombre del producto
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor(33, 37, 41);
        $pdf->MultiCell($content_width, 6, $this->clean_text($product->get_name()), 0, 'L', false, 1, $content_x, $y_start);
        
        $current_y = $pdf->GetY();
        
        // SKU
        if (!empty($options['include_sku']) && $product->get_sku()) {
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(108, 117, 125);
            $pdf->Cell($content_width, 5, 'SKU: ' . $product->get_sku(), 0, 1, 'L');
            $current_y = $pdf->GetY();
        }
        
        // Descripción
        if (!empty($options['include_descriptions'])) {
            $description = $product->get_short_description();
            if (empty($description)) {
                $description = $product->get_description();
            }
            
            if (!empty($description)) {
                $pdf->SetFont('helvetica', '', 8);
                $pdf->SetTextColor(73, 80, 87);
                $clean_desc = $this->clean_text(wp_strip_all_tags($description));
                $clean_desc = substr($clean_desc, 0, 200);
                if (strlen($description) > 200) {
                    $clean_desc .= '...';
                }
                $pdf->MultiCell($content_width, 4, $clean_desc, 0, 'L', false, 1);
                $current_y = $pdf->GetY();
            }
        }
        
        // Stock
        if (!empty($options['include_stock'])) {
            $pdf->SetFont('helvetica', '', 8);
            if ($product->is_in_stock()) {
                $stock_qty = $product->get_stock_quantity();
                $stock_text = 'En stock';
                if ($stock_qty !== null) {
                    $stock_text .= ': ' . $stock_qty . ' unidades';
                }
                $pdf->SetTextColor(40, 167, 69);
            } else {
                $stock_text = 'Agotado';
                $pdf->SetTextColor(220, 53, 69);
            }
            $pdf->Cell($content_width, 5, $stock_text, 0, 1, 'L');
            $current_y = $pdf->GetY();
        }
        
        // Precio mayorista (destacado a la derecha)
        $wholesale_price = get_post_meta($product_id, '_wfx_wholesale_price', true);
        if (empty($wholesale_price)) {
            $wholesale_price = $product->get_regular_price();
        }
        
        if (!empty($wholesale_price) && is_numeric($wholesale_price)) {
            $price_x = $content_x + $content_width + 5;
            $pdf->SetXY($price_x, $y_start + 5);
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->SetTextColor(13, 110, 253);
            
            $currency = get_woocommerce_currency_symbol();
            $price_formatted = $currency . ' ' . number_format((float)$wholesale_price, 0, ',', '.');
            
            $pdf->Cell(30, 10, $price_formatted, 0, 0, 'R');
        }
        
        // Calcular altura máxima usada
        $row_height = max($current_y - $y_start, $image_width + 5);
        $pdf->SetY($y_start + $row_height + 3);
        
        // Línea separadora
        $pdf->SetDrawColor(233, 236, 239);
        $pdf->Line($x_margin, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->SetY($pdf->GetY() + 5);
    }
    
    /**
     * Agregar footer al PDF
     */
    private function add_footer($pdf, $settings) {
        $pdf->SetY(-20);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(108, 117, 125);
        
        $contact_info = array();
        if (!empty($settings['contact_email'])) {
            $contact_info[] = 'Email: ' . $settings['contact_email'];
        }
        if (!empty($settings['contact_phone'])) {
            $contact_info[] = 'Tel: ' . $settings['contact_phone'];
        }
        
        if (!empty($contact_info)) {
            $pdf->Cell(0, 4, implode(' | ', $contact_info), 0, 1, 'C');
        }
        
        $pdf->Cell(0, 4, site_url(), 0, 1, 'C');
        $pdf->Cell(0, 4, 'Página ' . $pdf->getAliasNumPage() . ' de ' . $pdf->getAliasNbPages(), 0, 0, 'C');
    }
    
    /**
     * Obtener productos ordenados
     */
    private function get_sorted_products($product_ids, $sort_by) {
        $products = array();
        
        foreach ($product_ids as $id) {
            $product = wc_get_product($id);
            if ($product && $product->is_type('simple')) {
                $products[] = $product;
            }
        }
        
        if (empty($products)) {
            return array();
        }
        
        switch ($sort_by) {
            case 'price':
                usort($products, function($a, $b) {
                    $price_a = (float)($a->get_regular_price() ?? 0);
                    $price_b = (float)($b->get_regular_price() ?? 0);
                    return $price_a <=> $price_b;
                });
                break;
                
            case 'sku':
                usort($products, function($a, $b) {
                    return strcmp($a->get_sku() ?? '', $b->get_sku() ?? '');
                });
                break;
                
            case 'category':
                usort($products, function($a, $b) {
                    $cat_a = $this->get_product_category($a);
                    $cat_b = $this->get_product_category($b);
                    return strcmp($cat_a, $cat_b);
                });
                break;
                
            case 'name':
            default:
                usort($products, function($a, $b) {
                    return strcmp($a->get_name(), $b->get_name());
                });
                break;
        }
        
        return $products;
    }
    
    /**
     * Obtener categoría principal del producto
     */
    private function get_product_category($product) {
        $categories = wp_get_post_terms($product->get_id(), 'product_cat');
        return !empty($categories) ? $categories[0]->name : '';
    }
    
    /**
     * Convertir URL de imagen a ruta del sistema
     */
    private function get_image_path($url) {
        if (empty($url)) {
            return false;
        }
        
        $upload_dir = wp_upload_dir();
        $path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $url);
        
        return $path;
    }
    
    /**
     * Limpiar texto para PDF
     */
    private function clean_text($text) {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }
}
