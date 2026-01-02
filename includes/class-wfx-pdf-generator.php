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
        $pdf->SetY(15);
        
        // Logo si existe (más grande)
        if (!empty($settings['company_logo'])) {
            $logo_path = $this->get_image_path($settings['company_logo']);
            if ($logo_path && file_exists($logo_path)) {
                try {
                    $pdf->Image($logo_path, 15, 15, 60, 0, '', '', '', false, 300, '', false, false, 0);
                } catch (Exception $e) {
                    error_log('WFX Wholesale: Logo error: ' . $e->getMessage());
                }
            }
        }
        
        // Título (lado derecho)
        $pdf->SetXY(100, 20);
        $pdf->SetFont('helvetica', 'B', 24);
        $pdf->SetTextColor(13, 110, 253);
        $pdf->Cell(0, 10, $settings['catalog_title'] ?? 'Catálogo Mayorista', 0, 1, 'R');
        
        // Fecha
        $pdf->SetXY(100, 30);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor(108, 117, 125);
        $pdf->Cell(0, 6, date('d/m/Y'), 0, 1, 'R');
        
        // Línea separadora más gruesa
        $pdf->SetY(45);
        $pdf->SetDrawColor(13, 110, 253);
        $pdf->SetLineWidth(0.8);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->SetLineWidth(0.2);
        
        $pdf->SetY(52);
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
        $page_width = 180;
        
        // Verificar si hay espacio suficiente
        if ($y_start > 210) {
            $pdf->AddPage();
            $y_start = $pdf->GetY();
        }
        
        $padding = 5;
        
        // Calcular altura mínima necesaria (será ajustada después)
        $min_box_height = 70;
        
        // Dibujar caja de fondo inicial (será redibujada con altura correcta)
        $pdf->SetFillColor(248, 249, 250);
        $pdf->SetDrawColor(222, 226, 230);
        $pdf->Rect($x_margin, $y_start, $page_width, $min_box_height, 'FD');
        
        $content_y = $y_start + $padding;
        
        // ============ IMAGEN DEL PRODUCTO ============
        $image_x = $x_margin + $padding;
        $image_width = 60;
        $image_height = 60;
        
        if (!empty($options['include_images'])) {
            $image_id = $product->get_image_id();
            if ($image_id) {
                $image_path = $this->get_image_path(wp_get_attachment_url($image_id));
                if ($image_path && file_exists($image_path)) {
                    try {
                        $image_info = @getimagesize($image_path);
                        
                        if ($image_info !== false) {
                            list($original_width, $original_height) = $image_info;
                            
                            if ($original_width > 0 && $original_height > 0) {
                                // Calcular dimensiones proporcionales
                                $ratio = $original_height / $original_width;
                                
                                if ($ratio > 1) {
                                    // Imagen vertical
                                    $calculated_height = min($image_height, $image_width * $ratio);
                                    $calculated_width = $calculated_height / $ratio;
                                } else {
                                    // Imagen horizontal o cuadrada
                                    $calculated_width = $image_width;
                                    $calculated_height = $image_width * $ratio;
                                    
                                    if ($calculated_height > $image_height) {
                                        $calculated_height = $image_height;
                                        $calculated_width = $image_height / $ratio;
                                    }
                                }
                                
                                // Centrar imagen en el contenedor
                                $image_offset_x = $image_x + (($image_width - $calculated_width) / 2);
                                $image_offset_y = $content_y + (($image_height - $calculated_height) / 2);
                                
                                // Dibujar contenedor
                                $pdf->SetDrawColor(200, 200, 200);
                                $pdf->Rect($image_x, $content_y, $image_width, $image_height, 'D');
                                
                                // Insertar imagen
                                $pdf->Image($image_path, $image_offset_x, $image_offset_y, $calculated_width, $calculated_height, '', '', '', false, 300, '', false, false, 0);
                            }
                        } else {
                            $this->draw_image_placeholder($pdf, $image_x, $content_y, $image_width, $image_height, 'Error de imagen');
                        }
                    } catch (Exception $e) {
                        error_log('WFX Wholesale: Product image error: ' . $e->getMessage());
                        $this->draw_image_placeholder($pdf, $image_x, $content_y, $image_width, $image_height, 'Error de imagen');
                    }
                } else {
                    $this->draw_image_placeholder($pdf, $image_x, $content_y, $image_width, $image_height, 'Sin imagen');
                }
            } else {
                $this->draw_image_placeholder($pdf, $image_x, $content_y, $image_width, $image_height, 'Sin imagen');
            }
        }
        
        // ============ CONTENIDO DEL PRODUCTO ============
        $content_x = $image_x + $image_width + 8;
        $content_width = 80;
        $current_y = $content_y;
        
        // Nombre del producto
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->SetTextColor(33, 37, 41);
        $pdf->SetXY($content_x, $current_y);
        $pdf->MultiCell($content_width, 6, $this->clean_text($product->get_name()), 0, 'L', false, 1);
        $current_y = $pdf->GetY() + 2;
        
        // SKU
        if (!empty($options['include_sku']) && $product->get_sku()) {
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFillColor(108, 117, 125);
            $pdf->SetXY($content_x, $current_y);
            $sku_text = ' SKU: ' . $product->get_sku() . ' ';
            $pdf->Cell($pdf->GetStringWidth($sku_text) + 2, 5, $sku_text, 0, 0, 'L', true);
            $current_y += 7;
        }
        
        // Descripción
        if (!empty($options['include_descriptions'])) {
            $description = $product->get_short_description();
            if (empty($description)) {
                $description = $product->get_description();
            }
            
            if (!empty($description)) {
                $pdf->SetFont('helvetica', '', 9);
                $pdf->SetTextColor(73, 80, 87);
                $clean_desc = $this->clean_text(wp_strip_all_tags($description));
                $clean_desc = $this->smart_truncate($clean_desc, 400);
                
                $pdf->SetXY($content_x, $current_y);
                $pdf->MultiCell($content_width, 4, $clean_desc, 0, 'L', false, 1);
                $current_y = $pdf->GetY() + 2;
            }
        }
        
        // Compra mínima
        $minimum_order = get_post_meta($product_id, '_wfx_minimum_order', true);
        if (empty($minimum_order) || !is_numeric($minimum_order)) {
            $settings = get_option('wfx_wholesale_settings', array());
            $minimum_order = isset($settings['default_minimum_order']) ? $settings['default_minimum_order'] : 5;
        }
        
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetXY($content_x, $current_y);
        $pdf->SetTextColor(13, 110, 253);
        $pdf->Cell($content_width, 5, $this->decode_utf8('🛒 Compra mínima: ' . $minimum_order . ' unidades'), 0, 1, 'L');
        $current_y = $pdf->GetY() + 2;
        
        // ============ PRECIO MAYORISTA ============
        $wholesale_price = get_post_meta($product_id, '_wfx_wholesale_price', true);
        $regular_price = $product->get_regular_price();
        
        if (empty($wholesale_price)) {
            $wholesale_price = $regular_price;
        }
        
        if (!empty($wholesale_price) && is_numeric($wholesale_price)) {
            $price_x = $content_x + $content_width + 5;
            $price_width = 30;
            
            // Caja para el precio
            $pdf->SetFillColor(13, 110, 253);
            $pdf->Rect($price_x, $content_y, $price_width, 20, 'F');
            
            // Etiqueta
            $pdf->SetFont('helvetica', 'B', 6);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetXY($price_x, $content_y + 2);
            $pdf->Cell($price_width, 3, 'PRECIO MAYORISTA', 0, 1, 'C');
            
            // Precio
            $pdf->SetFont('helvetica', 'B', 16);
            $currency = $this->get_currency_symbol();
            $price_formatted = $currency . ' ' . number_format((float)$wholesale_price, 0, ',', '.');
            
            $pdf->SetXY($price_x, $content_y + 7);
            $pdf->Cell($price_width, 10, $price_formatted, 0, 0, 'C');
            
            // Precio regular tachado
            if (!empty($regular_price) && $regular_price != $wholesale_price && is_numeric($regular_price)) {
                $pdf->SetFont('helvetica', '', 8);
                $pdf->SetTextColor(100, 100, 100);
                $regular_formatted = $currency . ' ' . number_format((float)$regular_price, 0, ',', '.');
                $pdf->SetXY($price_x, $content_y + 22);
                
                $text_width = $pdf->GetStringWidth($regular_formatted);
                $text_x = $price_x + ($price_width - $text_width) / 2;
                $pdf->Cell($price_width, 4, $regular_formatted, 0, 0, 'C');
                $pdf->Line($text_x, $content_y + 24, $text_x + $text_width, $content_y + 24);
            }
        }
        
        // ============ CALCULAR ALTURA FINAL Y AJUSTAR CAJA SI ES NECESARIO ============
        $content_height = max($current_y - $content_y, $image_height);
        $actual_box_height = $content_height + ($padding * 2);
        
        // Si la altura calculada es mayor que la mínima, redibujar la caja con la altura correcta
        if ($actual_box_height > $min_box_height) {
            $pdf->SetFillColor(248, 249, 250);
            $pdf->SetDrawColor(222, 226, 230);
            $pdf->Rect($x_margin, $y_start, $page_width, $actual_box_height, 'FD');
            
            // Nota: En TCPDF cuando redibujamos el fondo, no cubre los elementos ya dibujados
            // porque TCPDF mantiene las capas en el orden correcto
        }
        
        // Ajustar posición para siguiente producto
        $pdf->SetY($y_start + max($actual_box_height, $min_box_height) + 5);
    }
    
    /**
     * Agregar footer al PDF
     */
    private function add_footer($pdf, $settings) {
        $pdf->SetY(-25);
        
        // Línea superior
        $pdf->SetDrawColor(13, 110, 253);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->SetY($pdf->GetY() + 3);
        
        // Información de contacto
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(33, 37, 41);
        
        $contact_info = array();
        if (!empty($settings['contact_email'])) {
            $contact_info[] = '📧 ' . $settings['contact_email'];
        }
        if (!empty($settings['contact_phone'])) {
            $contact_info[] = '📱 ' . $settings['contact_phone'];
        }
        
        if (!empty($contact_info)) {
            $pdf->Cell(0, 5, implode('  |  ', $contact_info), 0, 1, 'C');
        }
        
        // Website
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(13, 110, 253);
        $pdf->Cell(0, 4, site_url(), 0, 1, 'C');
        
        // Número de página
        $pdf->SetTextColor(108, 117, 125);
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
        
        // Limpiar URL
        $url = esc_url_raw($url);
        
        $upload_dir = wp_upload_dir();
        $path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $url);
        
        // Verificar que el archivo existe y es una imagen válida
        if (file_exists($path)) {
            $allowed_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif');
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $path);
            finfo_close($finfo);
            
            if (in_array($mime_type, $allowed_types)) {
                return $path;
            }
        }
        
        return false;
    }
    
    /**
     * Limpiar texto para PDF
     */
    private function clean_text($text) {
        // Primero decodificar todas las HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Remover tags HTML
        $text = wp_strip_all_tags($text);
        // Limpiar espacios múltiples
        $text = preg_replace('/\s+/', ' ', $text);
        // Remover caracteres especiales problemáticos
        $text = preg_replace('/[^\p{L}\p{N}\s\.\,\-\(\)\:\;\¿\?\¡\!]/u', '', $text);
        return trim($text);
    }
    
    /**
     * Cortar texto inteligentemente en punto, coma o espacio
     */
    private function smart_truncate($text, $max_length = 400) {
        // Si el texto es más corto que el límite, retornarlo completo
        if (strlen($text) <= $max_length) {
            return $text;
        }
        
        // Obtener substring hasta el límite
        $truncated = substr($text, 0, $max_length);
        
        // Buscar último punto dentro del límite
        $last_period = strrpos($truncated, '.');
        if ($last_period !== false && $last_period > ($max_length * 0.5)) {
            // Si encontramos un punto y está al menos a mitad del texto
            return substr($text, 0, $last_period + 1);
        }
        
        // Si no hay punto, buscar última coma
        $last_comma = strrpos($truncated, ',');
        if ($last_comma !== false && $last_comma > ($max_length * 0.5)) {
            return substr($text, 0, $last_comma + 1) . '...';
        }
        
        // Si no hay coma, buscar último espacio
        $last_space = strrpos($truncated, ' ');
        if ($last_space !== false) {
            return substr($text, 0, $last_space) . '...';
        }
        
        // Si todo falla, cortar en el límite
        return $truncated . '...';
    }
    
    /**
     * Decodificar UTF-8 para mostrar emojis correctamente en PDF
     */
    private function decode_utf8($text) {
        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Dibujar placeholder cuando no hay imagen
     */
    private function draw_image_placeholder($pdf, $x, $y, $width, $height, $text = 'Sin imagen') {
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Rect($x, $y, $width, $height, 'D');
        
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->SetXY($x, $y + ($height / 2) - 2);
        $pdf->Cell($width, 4, $text, 0, 0, 'C');
    }
    
    /**
     * Obtener símbolo de moneda sin HTML entities
     */
    private function get_currency_symbol() {
        $currency = get_woocommerce_currency();
        
        $symbols = array(
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'COP' => '$',
            'MXN' => '$',
            'ARS' => '$',
            'CLP' => '$',
            'PEN' => 'S/',
            'BRL' => 'R$',
            'CAD' => 'CA$',
            'AUD' => 'A$',
            'CHF' => 'CHF',
            'CNY' => '¥',
            'INR' => '₹',
        );
        
        return isset($symbols[$currency]) ? $symbols[$currency] : $currency;
    }
}
