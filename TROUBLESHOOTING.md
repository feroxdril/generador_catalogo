# 🔧 Guía de Solución de Problemas

## Error: "TCPDF no está disponible"

### Causa
La librería TCPDF no se encuentra en las ubicaciones esperadas.

### Solución
1. Verifica que existe: `/wp-content/plugins/generador_catalogo/lib/tcpdf/tcpdf.php`
2. Si no existe, descarga TCPDF desde: https://github.com/tecnickcom/TCPDF
3. Extrae en la carpeta `lib/tcpdf/`

### Verificación
Revisa el archivo de logs de WordPress en `/wp-content/debug.log` buscando líneas que digan "WFX Wholesale: TCPDF loaded from:"

## Error: "No hay permisos de escritura"

### Causa
La carpeta de catálogos no tiene permisos adecuados.

### Solución
Por FTP o SSH:
```bash
chmod 755 /wp-content/uploads/wfx-catalogs/
```

## Error: Imágenes no aparecen en PDF

### Causa
Las imágenes no existen en la ruta esperada o no tienen formato compatible.

### Solución
1. Verifica que las imágenes de productos estén en formato JPG o PNG
2. Regenera las miniaturas desde Herramientas > Regenerate Thumbnails

## Activar Modo Debug

Edita `wp-config.php` y agrega:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Luego revisa `/wp-content/debug.log`

## Logs del Plugin

El plugin genera logs detallados en el archivo `debug.log` de WordPress. Busca entradas que comiencen con "WFX Wholesale:"

### Mensajes de éxito:
- `WFX Wholesale: TCPDF loaded from: [ruta]` - TCPDF se cargó correctamente
- `WFX Wholesale: PDF generated successfully: [archivo]` - PDF creado correctamente

### Mensajes de error:
- `WFX Wholesale: TCPDF not found in any location` - TCPDF no está instalado
- `WFX Wholesale: Cannot generate PDF - TCPDF not available` - TCPDF no está disponible
- `WFX Wholesale: Failed to create directory` - No se pudo crear la carpeta
- `WFX Wholesale: Directory not writable` - La carpeta no tiene permisos de escritura
- `WFX Wholesale: Failed to create PDF file` - Error al guardar el archivo PDF

## Verificar Instalación de TCPDF

Crea un archivo PHP temporal con este contenido:
```php
<?php
define('WFX_WHOLESALE_PATH', plugin_dir_path(__FILE__));
$paths = array(
    WFX_WHOLESALE_PATH . 'lib/tcpdf/tcpdf.php',
    WFX_WHOLESALE_PATH . 'vendor/tecnickcom/tcpdf/tcpdf.php',
);

foreach ($paths as $path) {
    echo $path . ': ' . (file_exists($path) ? 'EXISTS' : 'NOT FOUND') . '<br>';
}
?>
```

## Verificar Permisos de Carpeta

En SSH o terminal:
```bash
ls -la wp-content/uploads/
ls -la wp-content/uploads/wfx-catalogs/
```

La carpeta debe tener permisos `755` y ser propiedad del usuario del servidor web (generalmente `www-data` o `apache`).

## Soporte Adicional

Si los problemas persisten:
1. Revisa el archivo `debug.log` completo
2. Verifica la versión de PHP (debe ser 7.4 o superior)
3. Verifica que WooCommerce esté activo y actualizado
4. Contacta con soporte técnico en: https://www.wifextelematics.com
