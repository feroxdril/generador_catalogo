<?php
if (!defined('ABSPATH')) {
    exit;
}

class WFX_Image_Optimizer {
    
    /**
     * Inicializar hooks
     */
    public static function init() {
        add_filter('wp_handle_upload', array(__CLASS__, 'optimize_uploaded_image'));
    }
    
    /**
     * Optimizar imagen al subirla
     */
    public static function optimize_uploaded_image($upload) {
        $file_path = $upload['file'];
        $file_type = $upload['type'];
        
        // Solo procesar imágenes
        if (strpos($file_type, 'image') === false) {
            return $upload;
        }
        
        // Verificar que el archivo existe
        if (!file_exists($file_path)) {
            return $upload;
        }
        
        try {
            $image_info = @getimagesize($file_path);
            
            if ($image_info === false) {
                return $upload;
            }
            
            list($width, $height, $type) = $image_info;
            
            // Si la imagen es muy grande, redimensionarla
            $max_dimension = 1200; // Máximo 1200px en cualquier dimensión
            
            if ($width > $max_dimension || $height > $max_dimension) {
                error_log('WFX Wholesale: Optimizing large image: ' . basename($file_path));
                
                // Calcular nuevas dimensiones
                if ($width > $height) {
                    $new_width = $max_dimension;
                    $new_height = intval(($height / $width) * $max_dimension);
                } else {
                    $new_height = $max_dimension;
                    $new_width = intval(($width / $height) * $max_dimension);
                }
                
                // Crear imagen según el tipo
                switch ($type) {
                    case IMAGETYPE_JPEG:
                        $source = imagecreatefromjpeg($file_path);
                        break;
                    case IMAGETYPE_PNG:
                        $source = imagecreatefrompng($file_path);
                        break;
                    case IMAGETYPE_GIF:
                        $source = imagecreatefromgif($file_path);
                        break;
                    default:
                        return $upload;
                }
                
                if ($source === false) {
                    return $upload;
                }
                
                // Crear nueva imagen redimensionada
                $destination = imagecreatetruecolor($new_width, $new_height);
                
                // Preservar transparencia para PNG
                if ($type == IMAGETYPE_PNG) {
                    imagealphablending($destination, false);
                    imagesavealpha($destination, true);
                    $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
                    imagefilledrectangle($destination, 0, 0, $new_width, $new_height, $transparent);
                }
                
                // Redimensionar
                imagecopyresampled($destination, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                
                // Guardar según el tipo
                switch ($type) {
                    case IMAGETYPE_JPEG:
                        imagejpeg($destination, $file_path, 85); // 85% calidad
                        break;
                    case IMAGETYPE_PNG:
                        imagepng($destination, $file_path, 6); // Compresión 6
                        break;
                    case IMAGETYPE_GIF:
                        imagegif($destination, $file_path);
                        break;
                }
                
                // Liberar memoria
                imagedestroy($source);
                imagedestroy($destination);
                
                error_log('WFX Wholesale: Image optimized: ' . $new_width . 'x' . $new_height);
            }
            
        } catch (Exception $e) {
            error_log('WFX Wholesale: Image optimization error: ' . $e->getMessage());
        }
        
        return $upload;
    }
    
    /**
     * Optimizar imagen existente para PDF
     */
    public static function optimize_for_pdf($image_path) {
        if (!file_exists($image_path)) {
            return false;
        }
        
        $image_info = @getimagesize($image_path);
        
        if ($image_info === false) {
            return false;
        }
        
        list($width, $height, $type) = $image_info;
        
        // Si la imagen ya es pequeña, retornarla como está
        if ($width <= 800 && $height <= 800) {
            return $image_path;
        }
        
        // Crear versión optimizada temporal
        $upload_dir = wp_upload_dir();
        // Usar solo extensión del archivo original, no el nombre completo
        $extension = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
        if (!in_array($extension, $allowed_extensions)) {
            $extension = 'jpg'; // Fallback
        }
        $temp_filename = 'wfx-temp-' . wp_generate_password(12, false, false) . '.' . $extension;
        $temp_path = $upload_dir['basedir'] . '/' . $temp_filename;
        
        try {
            // Cargar imagen según tipo
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $source = imagecreatefromjpeg($image_path);
                    break;
                case IMAGETYPE_PNG:
                    $source = imagecreatefrompng($image_path);
                    break;
                case IMAGETYPE_GIF:
                    $source = imagecreatefromgif($image_path);
                    break;
                default:
                    return $image_path;
            }
            
            if ($source === false) {
                return $image_path;
            }
            
            // Calcular nuevas dimensiones (máx 800x800)
            $max_size = 800;
            if ($width > $height) {
                $new_width = $max_size;
                $new_height = intval(($height / $width) * $max_size);
            } else {
                $new_height = $max_size;
                $new_width = intval(($width / $height) * $max_size);
            }
            
            // Crear imagen redimensionada
            $destination = imagecreatetruecolor($new_width, $new_height);
            
            // Preservar transparencia
            if ($type == IMAGETYPE_PNG) {
                imagealphablending($destination, false);
                imagesavealpha($destination, true);
                $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
                imagefilledrectangle($destination, 0, 0, $new_width, $new_height, $transparent);
            }
            
            imagecopyresampled($destination, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            
            // Guardar según el tipo original para preservar transparencia
            switch ($type) {
                case IMAGETYPE_PNG:
                    imagepng($destination, $temp_path, 6);
                    break;
                case IMAGETYPE_JPEG:
                default:
                    imagejpeg($destination, $temp_path, 85);
                    break;
            }
            
            // Liberar memoria
            imagedestroy($source);
            imagedestroy($destination);
            
            return $temp_path;
            
        } catch (Exception $e) {
            error_log('WFX Wholesale: PDF image optimization error: ' . $e->getMessage());
            return $image_path;
        }
    }
}
