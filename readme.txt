=== WFX Wholesale Catalog Generator ===
Contributors: wfxtelematics
Tags: woocommerce, pdf, catalog, wholesale, products
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.1.3
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

1. El plugin incluye TCPDF, no requiere instalación adicional
2. Ve a 'Catálogo PDF' en el menú de administración
3. Selecciona los productos que deseas incluir
4. Define precios mayoristas si es necesario
5. Haz clic en 'Generar Catálogo PDF'

= Requisitos =

* WordPress 5.8 o superior
* WooCommerce 5.0 o superior
* PHP 7.4 o superior

== Frequently Asked Questions ==

= ¿Requiere WooCommerce? =

Sí, este plugin está diseñado específicamente para trabajar con WooCommerce y requiere que esté instalado y activado.

= ¿Cómo instalo TCPDF? =

TCPDF está incluido en el plugin desde la versión 1.1.0, no requiere instalación adicional.

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

= 1.1.3 - 2026-01-02 =
* Changed: Stock reemplazado por Compra Mínima en catálogos PDF
* Added: Campo de Compra Mínima en editor de productos
* Added: Configuración de compra mínima por defecto
* Improved: Corte inteligente de descripciones en puntos completos
* Fixed: Descripciones cortadas en mitad de palabra
* Improved: Soporte UTF-8 para emojis en PDF
* Added: Emoji 🛒 para indicador de compra mínima

= 1.1.2 - 2026-01-02 =
* Improved: Tamaño de imágenes de productos aumentado (60mm)
* Fixed: Símbolo de moneda corregido en precios
* Improved: Descripciones más largas (350 caracteres)
* Improved: Diseño profesional con cajas y colores
* Improved: Precio mayorista destacado en caja azul
* Improved: Precio regular tachado cuando difiere
* Improved: SKU en formato badge
* Improved: Stock con iconos visuales
* Improved: Header y footer rediseñados
* Improved: Mejor manejo de HTML entities

= 1.1.1 - 2026-01-02 =
* Fixed: Corregida detección y carga de TCPDF
* Fixed: Error "TCPDF no está disponible" resuelto
* Fixed: Validación de permisos de carpeta
* Improved: Mejor manejo de errores con mensajes descriptivos
* Improved: Logs de debug para troubleshooting
* Improved: Validación de imágenes y productos
* Improved: Formateo de precios mejorado

= 1.1.0 - 2025-12-29 =
* Added: Compatibilidad con WooCommerce HPOS (High-Performance Order Storage)
* Added: TCPDF incluido en el plugin
* Improved: Mejor manejo de errores en generación de PDF
* Improved: Auto-guardado de precios mayoristas
* Fixed: Problemas de compatibilidad con WooCommerce 8.0+

= 1.0.0 - 2025-12-29 =
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
