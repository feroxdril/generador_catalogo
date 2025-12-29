# Instalación de TCPDF

Este plugin requiere la librería TCPDF para generar archivos PDF.

## Opción 1: Instalación con Composer (Recomendado)

Si tienes Composer instalado, ejecuta el siguiente comando en el directorio raíz del plugin:

```bash
composer install
```

Esto instalará TCPDF automáticamente en el directorio `vendor/`.

## Opción 2: Instalación Manual

Si no puedes usar Composer, puedes instalar TCPDF manualmente:

1. Descarga TCPDF desde: https://github.com/tecnickcom/TCPDF/releases
2. Extrae el archivo ZIP
3. Copia la carpeta `tcpdf` completa en este directorio (`lib/tcpdf/`)
4. Asegúrate de que la ruta final sea: `lib/tcpdf/tcpdf.php`

La estructura debe quedar así:
```
wfx-wholesale-catalog/
  lib/
    tcpdf/
      tcpdf.php
      (otros archivos de TCPDF)
```

## Verificación

El plugin detectará automáticamente TCPDF en cualquiera de estas ubicaciones:
- `vendor/autoload.php` (instalación con Composer)
- `lib/tcpdf/tcpdf.php` (instalación manual)

Si TCPDF no está instalado, el plugin mostrará un error al intentar generar un catálogo.

## Requisitos

- PHP >= 7.4
- WordPress >= 5.8
- WooCommerce >= 5.0

## Soporte

Para más información, visita: https://www.wifextelematics.com
