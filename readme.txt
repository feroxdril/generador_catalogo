=== WFX Wholesale Catalog Generator ===
Contributors: wfxtelematics
Tags: woocommerce, pdf, catalog, wholesale, products
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Genera catálogos PDF de productos mayoristas seleccionados desde WooCommerce.

== Description ==

WFX Wholesale Catalog Generator es un plugin completo para WordPress y WooCommerce que te permite crear catálogos PDF profesionales de tus productos mayoristas de forma sencilla e intuitiva.

= Características Principales =

* **Selección Visual de Productos**: Interfaz moderna con checkboxes para seleccionar productos
* **Precios Mayoristas**: Define precios especiales para cada producto en el catálogo
* **Búsqueda en Tiempo Real**: Encuentra productos rápidamente mientras seleccionas
* **Generación Rápida de PDF**: Crea catálogos profesionales en segundos
* **Personalización Completa**: Logo, título, información de contacto y más
* **Diseño Profesional**: PDFs con imágenes, descripciones, precios y stock
* **Fácil de Usar**: Interfaz intuitiva sin configuraciones complicadas

= Características del PDF =

* Logo de la empresa
* Título personalizable
* Imágenes de productos
* Nombres y descripciones
* SKU (opcional)
* Precios mayoristas destacados
* Stock disponible (opcional)
* Información de contacto en el footer
* Numeración de páginas automática

= Casos de Uso =

* Distribuidores que necesitan enviar catálogos a clientes
* Mayoristas que actualizan precios frecuentemente
* Tiendas B2B que quieren compartir productos offline
* Empresas que participan en ferias y eventos

== Installation ==

= Instalación Automática =

1. Ve a 'Plugins > Añadir nuevo' en tu panel de WordPress
2. Busca 'WFX Wholesale Catalog'
3. Haz clic en 'Instalar ahora' y luego en 'Activar'

= Instalación Manual =

1. Descarga el archivo ZIP del plugin
2. Ve a 'Plugins > Añadir nuevo > Subir plugin'
3. Selecciona el archivo ZIP y haz clic en 'Instalar ahora'
4. Activa el plugin

= Después de la Instalación =

1. Ejecuta `composer install` en el directorio del plugin, o instala TCPDF manualmente (ver lib/README.md)
2. Ve a 'Catálogo PDF' en el menú de administración
3. Selecciona los productos que deseas incluir
4. Define precios mayoristas si es necesario
5. Haz clic en 'Generar Catálogo PDF'

= Requisitos =

* WordPress 5.8 o superior
* WooCommerce 5.0 o superior
* PHP 7.4 o superior
* Librería TCPDF (se instala con Composer o manualmente)

== Frequently Asked Questions ==

= ¿Requiere WooCommerce? =

Sí, este plugin está diseñado específicamente para trabajar con WooCommerce y requiere que esté instalado y activado.

= ¿Cómo instalo TCPDF? =

Puedes instalar TCPDF de dos formas:
1. Con Composer: ejecuta `composer install` en el directorio del plugin
2. Manualmente: descarga TCPDF y colócalo en `lib/tcpdf/` (ver lib/README.md para detalles)

= ¿Puedo personalizar el diseño del PDF? =

Actualmente el diseño es profesional y predefinido. Puedes personalizar el logo, título, información de contacto y qué información mostrar (SKU, stock).

= ¿Los precios mayoristas afectan los precios de WooCommerce? =

No, los precios mayoristas que defines son solo para el catálogo PDF y no afectan los precios en tu tienda online.

= ¿Puedo generar varios catálogos diferentes? =

Sí, puedes cambiar la selección de productos y generar diferentes catálogos según necesites. Cada PDF se guarda con fecha y hora.

= ¿Dónde se guardan los PDFs generados? =

Los catálogos se guardan en `wp-content/uploads/wfx-catalogs/` con un nombre único basado en fecha y hora.

= ¿Funciona con productos variables? =

Actualmente el plugin está optimizado para productos simples. El soporte para productos variables se añadirá en futuras versiones.

= ¿Puedo compartir el PDF directamente con clientes? =

Sí, el PDF se genera y se abre automáticamente. Puedes descargarlo y compartirlo por email, WhatsApp, o cualquier otro medio.

== Screenshots ==

1. Página principal con selección de productos
2. Configuración del plugin
3. Ejemplo de catálogo PDF generado
4. Meta box en productos individuales

== Changelog ==

= 1.0.0 =
* Lanzamiento inicial
* Selección visual de productos
* Generación de PDF con TCPDF
* Precios mayoristas personalizados
* Búsqueda en tiempo real
* Configuración personalizable
* Meta boxes en productos
* Interfaz moderna y responsive

== Upgrade Notice ==

= 1.0.0 =
Versión inicial del plugin.

== Additional Info ==

= Soporte =

Para soporte, visita: https://www.wifextelematics.com

= Desarrollo =

Este plugin ha sido desarrollado por WFX Telematics.

= Licencia =

Este plugin es software libre bajo la licencia GPL v2 o posterior.
